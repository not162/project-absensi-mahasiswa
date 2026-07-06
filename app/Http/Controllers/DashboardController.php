<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Department;
use App\Models\LecturerCourse;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\ClassMeeting;
use App\Models\StudentAttendance;
use App\Models\CourseRepeat;
use App\Models\Kelas;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        if ($user->role === 'admin') {
            // Admin dashboard
            $totalUsers = User::where('role', 'user')->count();
            $totalDepartments = Department::count();
            $totalDosen = User::where('role', 'dosen')->count();
            $totalCourses = Course::count();

            $todayPresent = Attendance::whereDate('attendance_date', $today)
                ->where('status', 'present')
                ->count();

            $todayLate = Attendance::whereDate('attendance_date', $today)
                ->where('status', 'late')
                ->count();

            $todayAbsent = Attendance::whereDate('attendance_date', $today)
                ->where('status', 'absent')
                ->count();

            $todayTotalAttendance = Attendance::whereDate('attendance_date', $today)->count();

            $recentAttendances = Attendance::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Dosen Pengajar
            $lecturers = LecturerCourse::with([
                'lecturer:id,name,kode_dosen,department_id',
                'lecturer.department:id,name',
            ])
                ->get()
                ->pluck('lecturer')
                ->filter()
                ->unique('id')
                ->values();

            // Statistik kehadiran 7 hari terakhir (untuk grafik)
            $weeklyLabels = [];
            $weeklyPresent = [];
            $weeklyAbsent = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $weeklyLabels[] = $date->translatedFormat('d M');

                $weeklyPresent[] = Attendance::whereDate('attendance_date', $date)
                    ->whereIn('status', ['present', 'late'])
                    ->count();

                $weeklyAbsent[] = Attendance::whereDate('attendance_date', $date)
                    ->whereIn('status', ['absent', 'sick', 'permission'])
                    ->count();
            }

            // Pengajuan Pengulangan Mata Kuliah yang masih pending
            $pendingRepeats = CourseRepeat::with(['student', 'course'])
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
            $totalPendingRepeats = CourseRepeat::where('status', 'pending')->count();

            // Mahasiswa baru yang belum mendapatkan kelas
            $pendingStudents = User::where('role', 'user')
                ->whereNull('class_id')
                ->orderBy('created_at', 'desc')
                ->get();
            $allDepartments = Department::orderBy('name')->get();
            $allClasses = Kelas::orderBy('semester')->orderBy('nomor_kelas')->get();

            return view('dashboard.admin', [
                'totalUsers' => $totalUsers,
                'totalDepartments' => $totalDepartments,
                'totalDosen' => $totalDosen,
                'totalCourses' => $totalCourses,
                'todayPresent' => $todayPresent,
                'todayLate' => $todayLate,
                'todayAbsent' => $todayAbsent,
                'todayTotalAttendance' => $todayTotalAttendance,
                'recentAttendances' => $recentAttendances,
                'lecturers' => $lecturers,
                'weeklyLabels' => $weeklyLabels,
                'weeklyPresent' => $weeklyPresent,
                'weeklyAbsent' => $weeklyAbsent,
                'pendingRepeats' => $pendingRepeats,
                'totalPendingRepeats' => $totalPendingRepeats,
                'pendingStudents' => $pendingStudents,
                'allDepartments' => $allDepartments,
                'allClasses' => $allClasses,
            ]);
        }

        if ($user->role === 'dosen') {
            $hariMap = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
            ];
            $hari = $hariMap[$today->format('l')] ?? $today->format('l');

            // Jadwal hari ini milik dosen (beserta info kelas)
            $todaySchedules = Schedule::with(['course.department', 'kelas.mahasiswa'])
                ->where('user_id', $user->id)
                ->where('hari', $hari)
                ->orderBy('jam_mulai')
                ->get();

            // Apakah sudah absen di jadwal hari ini?
            $meetingsDone = ClassMeeting::where('lecturer_id', $user->id)
                ->whereDate('tanggal', $today)
                ->pluck('schedule_id');

            // Kartu statistik
            // 1. Mata kuliah diampu (unique)
            $totalMatkulDiampu = Schedule::where('user_id', $user->id)
                ->distinct('course_id')->count('course_id');

            // 2. Total mahasiswa (dari semua kelas yang diajar)
            $kelasIds = Schedule::where('user_id', $user->id)
                ->whereNotNull('class_id')->pluck('class_id')->unique();
            $totalMahasiswa = User::where('role', 'user')
                ->whereIn('class_id', $kelasIds)->count();

            // 3. Pertemuan hari ini (sudah diabsen)
            $pertemuanHariIni = $meetingsDone->count();

            // 4. Rata-rata persentase kehadiran dari semua pertemuan yang sudah berlangsung
            $allMeetings = ClassMeeting::where('lecturer_id', $user->id)->pluck('id');
            $totalAbsenRecord = StudentAttendance::whereIn('meeting_id', $allMeetings)->count();
            $totalHadir = StudentAttendance::whereIn('meeting_id', $allMeetings)
                ->where('status', 'hadir')->count();
            $persenKehadiran = $totalAbsenRecord > 0 ? round(($totalHadir / $totalAbsenRecord) * 100) : 0;

            // 5. Ringkasan kehadiran per kelas yang diampu (Optimized to avoid N+1 queries)
            $courseSchedules = Schedule::with(['course', 'kelas'])
                ->where('user_id', $user->id)
                ->get();

            $courseScheduleIds = $courseSchedules->pluck('id');
            $allMeetings = ClassMeeting::whereIn('schedule_id', $courseScheduleIds)->get();
            $allMeetingIds = $allMeetings->pluck('id');
            $allAttendances = StudentAttendance::whereIn('meeting_id', $allMeetingIds)->get();

            $classAttendanceSummary = [];
            foreach ($courseSchedules as $schedule) {
                $meetingIds = $allMeetings->where('schedule_id', $schedule->id)->pluck('id');
                $totalRecord = $allAttendances->whereIn('meeting_id', $meetingIds)->count();
                $hadirRecord = $allAttendances->whereIn('meeting_id', $meetingIds)
                    ->where('status', 'hadir')
                    ->count();
                
                $percentage = $totalRecord > 0 ? round(($hadirRecord / $totalRecord) * 100) : 0;
                
                $classAttendanceSummary[] = [
                    'course_name' => $schedule->course->nama_matkul ?? '-',
                    'class_name' => $schedule->kelas ? 'Sem ' . $schedule->kelas->semester . ' - ' . $schedule->kelas->nomor_kelas : '-',
                    'total_meetings' => $meetingIds->count(),
                    'percentage' => $percentage,
                ];
            }

            $lowAttendanceWarnings = [];
            foreach ($classAttendanceSummary as $summary) {
                if ($summary['total_meetings'] > 0 && $summary['percentage'] < 75) {
                    $lowAttendanceWarnings[] = "Rata-rata kehadiran kelas {$summary['course_name']} ({$summary['class_name']}) rendah: {$summary['percentage']}%.";
                }
            }

            $dosenAttendanceStats = [
                'hadir' => $allAttendances->where('status', 'hadir')->count(),
                'izin' => $allAttendances->where('status', 'izin')->count(),
                'sakit' => $allAttendances->where('status', 'sakit')->count(),
                'tidak_hadir' => $allAttendances->where('status', 'tidak_hadir')->count(),
            ];

            // Get all classes taught by this Dosen (distinct)
            $scheduleClasses = Schedule::with('kelas')
                ->where('user_id', $user->id)
                ->get()
                ->pluck('kelas')
                ->filter()
                ->unique('id');

            // Generate Heatmap Data
            $dosenScheduleIds = Schedule::where('user_id', $user->id)->pluck('id');
            $meetings = ClassMeeting::with('schedule.kelas')
                ->whereIn('schedule_id', $dosenScheduleIds)
                ->orderBy('pertemuan_ke')
                ->get();

            $heatmapData = [];
            foreach ($meetings as $meeting) {
                $className = $meeting->schedule->kelas->nomor_kelas ?? 'Kelas -';
                $courseName = $meeting->schedule->course->nama_matkul ?? 'Matkul';
                $meetingNum = $meeting->pertemuan_ke;

                // Total students in this class
                $totalStudents = User::where('role', 'user')->where('class_id', $meeting->schedule->class_id)->count();

                // Attended students in this meeting
                $attendedCount = StudentAttendance::where('meeting_id', $meeting->id)->where('status', 'hadir')->count();

                $percent = $totalStudents > 0 ? round(($attendedCount / $totalStudents) * 100) : 0;

                $heatmapData[$courseName . ' (' . $className . ')'][$meetingNum] = [
                    'percent' => $percent,
                    'meeting_id' => $meeting->id,
                    'date' => $meeting->tanggal ? \Carbon\Carbon::parse($meeting->tanggal)->format('d/m') : '-'
                ];
            }

            return view('dashboard.dosen', [
                'totalMatkulDiampu'  => $totalMatkulDiampu,
                'totalMahasiswa'     => $totalMahasiswa,
                'pertemuanHariIni'   => $pertemuanHariIni,
                'persenKehadiran'    => $persenKehadiran,
                'hari'               => $hari,
                'todaySchedules'     => $todaySchedules,
                'meetingsDone'       => $meetingsDone,
                'classAttendanceSummary' => $classAttendanceSummary,
                'lowAttendanceWarnings' => $lowAttendanceWarnings,
                'dosenAttendanceStats' => $dosenAttendanceStats,
                'scheduleClasses'    => $scheduleClasses,
                'heatmapData'        => $heatmapData,
            ]);
        }

        // Mahasiswa / user dashboard
        $kelas = $user->kelas;

        // Jadwal kuliah mahasiswa (berdasarkan kelas)
        $jadwalKuliah = $kelas
            ? Schedule::with(['course', 'lecturer'])
                ->where('class_id', $kelas->id)
                ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
                ->orderBy('jam_mulai')
                ->get()
            : collect();

        // Total mata kuliah diambil
        $totalMatkul = $jadwalKuliah->count();

        // Mencari pertemuan hari ini yang sedang aktif (sudah dibuka)
        $hariMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];
        $hariIni = $hariMap[$today->format('l')] ?? $today->format('l');

        $activeMeetings = collect();
        if ($kelas) {
            $schedulesToday = Schedule::where('class_id', $kelas->id)->where('hari', $hariIni)->pluck('id');
            $activeMeetings = ClassMeeting::with('schedule.course')
                ->whereIn('schedule_id', $schedulesToday)
                ->whereDate('tanggal', $today)
                ->get();
        }

        // Riwayat absensi mahasiswa (dari student_attendances)
        $riwayatAbsensi = StudentAttendance::with(['meeting.schedule.course'])
            ->where('student_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Statistik kehadiran
        $totalAbsen   = $riwayatAbsensi->count();
        $totalHadir   = $riwayatAbsensi->where('status', 'hadir')->count();
        $totalIzin    = $riwayatAbsensi->where('status', 'izin')->count();
        $totalSakit   = $riwayatAbsensi->where('status', 'sakit')->count();
        $totalTidakHadir = $riwayatAbsensi->where('status', 'tidak_hadir')->count();
        $persenKehadiran = $totalAbsen > 0 ? round(($totalHadir / $totalAbsen) * 100) : 0;

        // Data grafik per matkul (Optimized to avoid N+1 queries)
        $grafikLabels = [];
        $grafikData   = [];
        if ($kelas) {
            $scheduleIds = $jadwalKuliah->pluck('id');
            $allMeetings = ClassMeeting::whereIn('schedule_id', $scheduleIds)->get();
            $allMeetingIds = $allMeetings->pluck('id');
            $allAttendances = StudentAttendance::whereIn('meeting_id', $allMeetingIds)
                ->where('student_id', $user->id)
                ->get();

            foreach ($jadwalKuliah as $jadwal) {
                $meetingIds = $allMeetings->where('schedule_id', $jadwal->id)->pluck('id');
                $total  = $allAttendances->whereIn('meeting_id', $meetingIds)->count();
                $hadir  = $allAttendances->whereIn('meeting_id', $meetingIds)->where('status', 'hadir')->count();
                $grafikLabels[] = $jadwal->course->nama_matkul ?? '-';
                $grafikData[]   = $total > 0 ? round(($hadir / $total) * 100) : 0;
            }
        }

        $lowAttendanceWarnings = [];
        if ($totalAbsen > 0 && $persenKehadiran < 75) {
            $lowAttendanceWarnings[] = "Total rata-rata kehadiran Anda berada di bawah batas aman 75% ({$persenKehadiran}%).";
        }
        foreach ($jadwalKuliah as $i => $jadwal) {
            if (isset($grafikData[$i]) && $grafikData[$i] > 0 && $grafikData[$i] < 75) {
                $lowAttendanceWarnings[] = "Kehadiran mata kuliah " . ($jadwal->course->nama_matkul ?? '-') . " di bawah 75% (" . $grafikData[$i] . "%).";
            }
        }

        $analyticsService = app(\App\Services\StudentAnalyticsService::class);
        $analysis = $analyticsService->analyze($user);

        return view('dashboard.user', [
            'user'            => $user,
            'kelas'           => $kelas,
            'jadwalKuliah'    => $jadwalKuliah,
            'totalMatkul'     => $totalMatkul,
            'riwayatAbsensi'  => $riwayatAbsensi,
            'totalHadir'      => $totalHadir,
            'totalIzin'       => $totalIzin,
            'totalSakit'      => $totalSakit,
            'totalTidakHadir' => $totalTidakHadir,
            'persenKehadiran' => $persenKehadiran,
            'grafikLabels'    => $grafikLabels,
            'grafikData'      => $grafikData,
            'activeMeetings'  => $activeMeetings,
            'lowAttendanceWarnings' => $lowAttendanceWarnings,
            'analysis'        => $analysis,
        ]);
    }

    /** Pencarian Semantik Matakuliah (Vector Database Cosine Similarity) */
    public function semanticSearch(\Illuminate\Http\Request $request, \App\Services\VectorSearchService $vectorSearch)
    {
        $query = $request->get('query', '');
        $results = [];

        $user = Auth::user();
        $recommendedCourses = collect();

        if ($query) {
            $courses = Course::with('department')->get()->map(function ($c) {
                return [
                    'id' => $c->id,
                    'kode' => $c->kode_matkul,
                    'nama' => $c->nama_matkul,
                    'sks' => $c->sks,
                    'semester' => $c->semester,
                    'department' => $c->department->name ?? '-',
                    'text' => $c->kode_matkul . ' ' . $c->nama_matkul . ' ' . ($c->deskripsi ?? '') . ' ' . ($c->department->name ?? '')
                ];
            })->toArray();

            $results = $vectorSearch->search($query, $courses, 5);
        } else {
            // Load recommended courses based on user study program
            if ($user && $user->department_id) {
                $recommendedCourses = Course::with('department')
                    ->where('department_id', $user->department_id)
                    ->orderBy('semester')
                    ->limit(6)
                    ->get();
            } else {
                $recommendedCourses = Course::with('department')
                    ->limit(6)
                    ->get();
            }
        }

        return view('search.semantic', compact('query', 'results', 'recommendedCourses'));
    }

    /** Update Dosen custom AI Context/Note for a Class */
    public function updateClassContextNote(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'context_note' => 'nullable|string|max:1000',
        ]);

        $kelas = \App\Models\Kelas::findOrFail($request->class_id);
        $kelas->update(['context_note' => $request->context_note]);

        return back()->with('success', 'Catatan Latihan/Konteks Akademik AI Kelas berhasil diperbarui!');
    }
}
