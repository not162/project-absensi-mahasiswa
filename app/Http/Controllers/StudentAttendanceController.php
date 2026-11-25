<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ClassMeeting;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Course;
use App\Models\TeachingAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentAttendanceController extends Controller
{
    /** Dosen: halaman input absensi mahasiswa per jadwal */
    public function startAbsensi(Request $request, Schedule $schedule)
    {
        $user = Auth::user();
        abort_if($schedule->user_id !== $user->id, 403);

        $today = Carbon::today();

        // Buat atau ambil pertemuan hari ini
        $meeting = ClassMeeting::where('schedule_id', $schedule->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$meeting) {
            $meeting = ClassMeeting::create([
                'schedule_id'  => $schedule->id,
                'tanggal'      => $today->toDateString(),
                'lecturer_id'  => $user->id,
                'pertemuan_ke' => (ClassMeeting::where('schedule_id', $schedule->id)->max('pertemuan_ke') ?? 0) + 1,
                'materi'       => $request->materi ?? '',
            ]);
        }

        // Begitu dosen mulai kelas, otomatis tercatat juga sebagai "Absen Mengajar"
        // (hadir) supaya langsung muncul di riwayat absen mengajar milik admin.
        $teaching = TeachingAttendance::where('schedule_id', $schedule->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($teaching) {
            $teaching->update([
                'user_id' => $user->id,
                'status'  => 'hadir',
                'materi'  => $meeting->materi,
            ]);
        } else {
            TeachingAttendance::create([
                'schedule_id' => $schedule->id,
                'tanggal'     => $today->toDateString(),
                'user_id'     => $user->id,
                'status'      => 'hadir',
                'materi'      => $meeting->materi,
            ]);
        }

        // Ambil semua mahasiswa di kelas jadwal ini
        $mahasiswa = $schedule->kelas
            ? User::where('class_id', $schedule->kelas->id)->where('role', 'user')->orderBy('name')->get()
            : collect();

        // Absen yang sudah ada untuk pertemuan ini
        $existing = StudentAttendance::where('meeting_id', $meeting->id)
            ->get()->keyBy('student_id');

        return view('dosen.absensi.input', compact('schedule', 'meeting', 'mahasiswa', 'existing'));
    }

    /** Simpan absen mahasiswa satu pertemuan */
    public function store(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|exists:class_meetings,id',
            'materi'     => 'nullable|string|max:255',
            'absen'      => 'required|array',
            'absen.*'    => 'required|in:hadir,izin,sakit,tidak_hadir',
        ]);

        $meeting = ClassMeeting::findOrFail($request->meeting_id);
        abort_if($meeting->lecturer_id !== Auth::id(), 403);

        // Update materi
        if ($request->materi) {
            $meeting->update(['materi' => $request->materi]);
        }

        // Simpan absen tiap mahasiswa
        foreach ($request->absen as $studentId => $status) {
            StudentAttendance::updateOrCreate(
                ['meeting_id' => $meeting->id, 'student_id' => $studentId],
                ['status' => $status]
            );
        }

        // Broadcast Real-time Data Event
        try {
            event(new \App\Events\AbsensiTercatat([
                'schedule_id' => $meeting->schedule_id,
                'meeting_id'  => $meeting->id,
                'pesan'       => 'Absensi untuk jadwal ini telah diperbarui.'
            ]));
        } catch (\Exception $e) {
            // Abaikan jika Pusher belum di-setup
        }

        return redirect()->route('dosen.jadwal')->with('success', 'Absensi berhasil disimpan!');
    }

    /** Update single student attendance asynchronously (AJAX) */
    public function updateAsync(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|exists:class_meetings,id',
            'student_id' => 'required|exists:users,id',
            'status'     => 'required|in:hadir,izin,sakit,tidak_hadir',
        ]);

        $meeting = ClassMeeting::findOrFail($request->meeting_id);
        abort_if($meeting->lecturer_id !== Auth::id() && Auth::user()->role !== 'admin', 403);

        $attendance = StudentAttendance::updateOrCreate(
            ['meeting_id' => $meeting->id, 'student_id' => $request->student_id],
            ['status' => $request->status]
        );

        // Broadcast event
        try {
            event(new \App\Events\AbsensiTercatat([
                'schedule_id' => $meeting->schedule_id,
                'meeting_id'  => $meeting->id,
                'pesan'       => 'Absensi untuk jadwal ini telah diperbarui.'
            ]));
        } catch (\Exception $e) {
            // Silence
        }

        return response()->json([
            'success' => true,
            'message' => 'Status absensi berhasil diperbarui secara asynchronous!',
            'data' => $attendance
        ]);
    }

    /** Dosen: rekap kehadiran mahasiswa per jadwal yang diajar */
    public function rekap(Request $request)
    {
        $user      = Auth::user();
        $semesterF = $request->get('semester');
        $courseF   = $request->get('course_id');
        $kelasF    = $request->get('class_id');

        // Jadwal milik dosen ini
        $scheduleQuery = Schedule::with(['course', 'kelas.department'])
            ->where('user_id', $user->id);

        if ($semesterF) {
            $scheduleQuery->whereHas('kelas', fn($q) => $q->where('semester', $semesterF));
        }
        if ($courseF)   $scheduleQuery->where('course_id', $courseF);
        if ($kelasF)    $scheduleQuery->where('class_id', $kelasF);

        $schedules = $scheduleQuery->get();
        $rekap = $this->buildRekapData($schedules);

        // Data untuk filter dropdown
        $myCourses = Schedule::where('user_id', $user->id)
            ->with('course')->get()->pluck('course')->unique('id');
        $myKelas   = Schedule::where('user_id', $user->id)
            ->with('kelas.department')->get()->pluck('kelas')->unique('id');

        return view('dosen.absensi.rekap', compact(
            'rekap', 'myCourses', 'myKelas',
            'semesterF', 'courseF', 'kelasF'
        ));
    }

    /** Admin: rekap semua dosen */
    public function rekapAdmin(Request $request)
    {
        $semesterF    = $request->get('semester');
        $courseF      = $request->get('course_id');
        $kelasF       = $request->get('class_id');

        $scheduleQuery = Schedule::with(['course', 'kelas.department', 'lecturer'])
            ->join('classes', 'schedules.class_id', '=', 'classes.id')
            ->join('departments', 'classes.department_id', '=', 'departments.id')
            ->orderBy('departments.name')
            ->orderBy('classes.semester')
            ->select('schedules.*');

        if ($semesterF) {
            $scheduleQuery->where('classes.semester', $semesterF);
        }
        if ($courseF) $scheduleQuery->where('schedules.course_id', $courseF);
        if ($kelasF)  $scheduleQuery->where('schedules.class_id', $kelasF);

        $schedules = $scheduleQuery->get();
        $rekap = $this->buildRekapData($schedules);

        if ($request->get('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.absensi.rekap_pdf', compact(
                'rekap', 'semesterF', 'courseF', 'kelasF'
            ));
            return $pdf->stream('Rekap_Kehadiran_Mahasiswa.pdf');
        }

        $allCourses = Course::orderBy('nama_matkul')->get();
        $allKelas   = Kelas::with('department')->orderBy('semester')->get();

        return view('admin.absensi.rekap', compact(
            'rekap', 'allCourses', 'allKelas',
            'semesterF', 'courseF', 'kelasF'
        ));
    }

    /** Bangun data rekap kehadiran per jadwal tanpa N+1 per mahasiswa */
    private function buildRekapData($schedules): array
    {
        $rekap = [];
        foreach ($schedules as $schedule) {
            $meetings = ClassMeeting::where('schedule_id', $schedule->id)->pluck('id');
            $mahasiswa = $schedule->kelas
                ? User::where('class_id', $schedule->kelas->id)->where('role', 'user')->orderBy('name')->get()
                : collect();

            $statsByStudent = $meetings->isNotEmpty()
                ? StudentAttendance::whereIn('meeting_id', $meetings)
                    ->groupBy('student_id')
                    ->selectRaw("
                        student_id,
                        SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN status = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir,
                        COUNT(*) as total
                    ")
                    ->get()
                    ->keyBy('student_id')
                : collect();

            $rows = [];
            foreach ($mahasiswa as $mhs) {
                $att = $statsByStudent->get($mhs->id);
                $total = (int) ($att->total ?? 0);
                $hadir = (int) ($att->hadir ?? 0);

                $rows[] = [
                    'mahasiswa'   => $mhs,
                    'hadir'       => $hadir,
                    'izin'        => (int) ($att->izin ?? 0),
                    'sakit'       => (int) ($att->sakit ?? 0),
                    'tidak_hadir' => (int) ($att->tidak_hadir ?? 0),
                    'total'       => $total,
                    'persen'      => $total > 0 ? round(($hadir / $total) * 100) : 0,
                ];
            }

            $rekap[] = [
                'schedule'  => $schedule,
                'rows'      => $rows,
                'pertemuan' => $meetings->count(),
            ];
        }

        return $rekap;
    }

}