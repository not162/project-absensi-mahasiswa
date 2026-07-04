<?php
namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ClassMeeting;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SelfAttendanceController extends Controller
{
    /** Mahasiswa: halaman jadwal + self check-in + tugas */
    public function index()
    {
        $user  = Auth::user();
        $kelas = $user->kelas;
        $today = Carbon::today();
        $hariMap = [
            'Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
            'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu',
        ];
        $hariIni = $hariMap[$today->format('l')] ?? $today->format('l');

        $schedules = $kelas
            ? Schedule::with(['course', 'lecturer'])
                ->where('class_id', $kelas->id)
                ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
                ->orderBy('jam_mulai')
                ->get()
            : collect();

        // Cek absen hari ini & seluruh tugas (dari semua pertemuan) per jadwal
        $attendanceStatus  = [];
        $meetingData       = [];
        $assignmentsByJadwal = [];

        foreach ($schedules as $schedule) {
            $meeting = ClassMeeting::where('schedule_id', $schedule->id)
                ->whereDate('tanggal', $today)
                ->first();

            $meetingData[$schedule->id] = $meeting;

            if ($meeting) {
                $absen = StudentAttendance::where('meeting_id', $meeting->id)
                    ->where('student_id', $user->id)
                    ->first();
                $attendanceStatus[$schedule->id] = $absen?->status;
            }

            // Semua tugas dari semua pertemuan jadwal ini (bukan cuma hari ini)
            $meetingIdsSchedule = ClassMeeting::where('schedule_id', $schedule->id)->pluck('id');
            $assignmentsByJadwal[$schedule->id] = \App\Models\Assignment::whereIn('meeting_id', $meetingIdsSchedule)
                ->with(['submissions' => function ($q) use ($user) {
                    $q->where('student_id', $user->id);
                }, 'meeting'])
                ->orderByDesc('created_at')
                ->get();
        }

        return view('mahasiswa.jadwal', compact(
            'schedules', 'hariIni', 'today',
            'attendanceStatus', 'meetingData', 'assignmentsByJadwal', 'user'
        ));
    }

    /** Mahasiswa: self check-in (klik Hadir sendiri) */
    public function checkIn(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'status'      => 'required|in:hadir,izin,sakit,tidak_hadir',
        ]);

        $user     = Auth::user();
        $schedule = Schedule::findOrFail($request->schedule_id);
        $today    = Carbon::today();

        // Pastikan mahasiswa ada di kelas jadwal ini
        abort_if($schedule->class_id !== $user->class_id, 403);

        // Cari pertemuan hari ini (harus sudah dibuka oleh dosen)
        $meeting = ClassMeeting::where('schedule_id', $schedule->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$meeting) {
            return redirect()->back()->with('error', 'Pertemuan hari ini belum dibuka oleh dosen.');
        }

        StudentAttendance::updateOrCreate(
            ['meeting_id' => $meeting->id, 'student_id' => $user->id],
            ['status' => $request->status]
        );

        return redirect()->back()->with('success', 'Kehadiran berhasil dicatat');
    }
}