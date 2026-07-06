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

            // 5. Ringkasan kehadiran per kelas yang diampu
            $courseSchedules = Schedule::with(['course', 'kelas'])
                ->where('user_id', $user->id)
                ->get();

            $classAttendanceSummary = [];
            foreach ($courseSchedules as $schedule) {
                $meetingIds = ClassMeeting::where('schedule_id', $schedule->id)->pluck('id');
                $totalRecord = StudentAttendance::whereIn('meeting_id', $meetingIds)->count();
                $hadirRecord = StudentAttendance::whereIn('meeting_id', $meetingIds)
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

            return view('dashboard.dosen', [
                'totalMatkulDiampu'  => $totalMatkulDiampu,
                'totalMahasiswa'     => $totalMahasiswa,
                'pertemuanHariIni'   => $pertemuanHariIni,
                'persenKehadiran'    => $persenKehadiran,
                'hari'               => $hari,
                'todaySchedules'     => $todaySchedules,
                'meetingsDone'       => $meetingsDone,
                'classAttendanceSummary' => $classAttendanceSummary,
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

        // Data grafik per matkul
        $grafikLabels = [];
        $grafikData   = [];
        if ($kelas) {
            foreach ($jadwalKuliah as $jadwal) {
                $meetingIds = ClassMeeting::where('schedule_id', $jadwal->id)->pluck('id');
                $total  = StudentAttendance::whereIn('meeting_id', $meetingIds)->where('student_id', $user->id)->count();
                $hadir  = StudentAttendance::whereIn('meeting_id', $meetingIds)->where('student_id', $user->id)->where('status', 'hadir')->count();
                $grafikLabels[] = $jadwal->course->nama_matkul ?? '-';
                $grafikData[]   = $total > 0 ? round(($hadir / $total) * 100) : 0;
            }
        }

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
        ]);
    }
}
