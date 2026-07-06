<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DosenPengajarController;
use App\Http\Controllers\AkademikController;
use App\Http\Controllers\ExamScheduleController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\CourseRepeatController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SelfAttendanceController;
use App\Http\Controllers\TeachingAttendanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\ExamReplacementController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('auth.login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.submit');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('auth.register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================================
// AUTHENTICATED ROUTES
// ==========================================================
Route::middleware('auth')->group(function () {
    // Dashboard (All Authenticated Users)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (All Authenticated Users)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::get('/semantic-search', [DashboardController::class, 'semanticSearch'])->name('semantic.search');

    // ──────────────────────────────────────────────────────────
    // ADMIN ONLY ROUTES
    // ──────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Data Akademik
        Route::prefix('akademik')->group(function () {
            Route::get('/', [AkademikController::class, 'index'])->name('akademik.index');
            Route::get('/semester/{semester}', [AkademikController::class, 'semester'])->name('akademik.semester');
            Route::get('/semester/{semester}/kelas/create', [AkademikController::class, 'createKelas'])->name('akademik.kelas.create');
            Route::post('/semester/{semester}/kelas', [AkademikController::class, 'storeKelas'])->name('akademik.kelas.store');
            Route::post('/kelas/{kelas}/assign', [AkademikController::class, 'assignMatkul'])->name('akademik.assign');
            Route::delete('/kelas/{kelas}', [AkademikController::class, 'destroyKelas'])->name('akademik.kelas.destroy');
        });

        // Users & Departments
        Route::get('/users-export-pdf', [UserController::class, 'exportPdf'])->name('users.exportPdf');
        Route::post('/users-import', [UserController::class, 'importExcel'])->name('users.import');
        Route::resource('users', UserController::class);
        Route::resource('departments', DepartmentController::class);

        // Dosen Management
        Route::resource('dosen', DosenController::class);
        Route::post('/dosen', [DosenController::class, 'store'])->name('dosen.store'); // was outside auth
        Route::resource('dosen-pengajar', DosenPengajarController::class)->except(['create', 'store']);
        Route::get('/dosen-pengajar', [DosenPengajarController::class, 'index'])->name('dosenpengajar.index');
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
            Route::get('/daily', [ReportController::class, 'daily'])->name('reports.daily');
            Route::get('/user', [ReportController::class, 'user'])->name('reports.user');
            Route::post('/export', [ReportController::class, 'export'])->name('reports.export');
        });

        // Exams (Admin CRUD)
        Route::resource('exam', ExamScheduleController::class)->except(['index', 'show']);
        
        Route::prefix('exam')->group(function () {
            Route::get('/create', [ExamScheduleController::class, 'create'])->name('exam.create');
            Route::post('/', [ExamScheduleController::class, 'store'])->name('exam.store');
            Route::get('/{exam}/edit', [ExamScheduleController::class, 'edit'])->name('exam.edit');
            Route::put('/{exam}', [ExamScheduleController::class, 'update'])->name('exam.update');
            Route::delete('/{exam}', [ExamScheduleController::class, 'destroy'])->name('exam.destroy');
        });


        Route::get('/teaching-attendance/history', [TeachingAttendanceController::class, 'history'])->name('teaching-attendance.history');
        Route::post('/teaching-attendance/admin-store', [TeachingAttendanceController::class, 'adminStore'])->name('teaching-attendance.adminStore');
        
        // Course Repeats Admin
        Route::get('/admin/repeats', [CourseRepeatController::class, 'index'])->name('repeats.index');
        Route::post('/admin/repeats/{repeat}/approve', [CourseRepeatController::class, 'approve'])->name('repeats.approve');
        Route::post('/admin/repeats/{repeat}/reject', [CourseRepeatController::class, 'reject'])->name('repeats.reject');
        Route::delete('/admin/repeats/{repeat}', [CourseRepeatController::class, 'destroy'])->name('repeats.destroy');
        
        Route::get('/absensi/rekap-admin', [StudentAttendanceController::class, 'rekapAdmin'])->name('absensi.rekapAdmin');
        Route::post('/admin/users/{student}/assign-class', [App\Http\Controllers\Admin\StudentVerificationController::class, 'assignClass'])->name('users.assignClass');
    });

    // ──────────────────────────────────────────────────────────
    // ADMIN & DOSEN ROUTES
    // ──────────────────────────────────────────────────────────
    Route::middleware('role:admin,dosen')->group(function () {
        Route::get('/dosen/courses', [DosenController::class, 'coursesByDepartmentAndSemester'])->name('dosen.coursesByDepartmentAndSemester');
        Route::get('/schedules/dosen/{dosen}', [ScheduleController::class, 'byDosen'])->name('schedules.byDosen');
        
        // Tugas Management
        Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::post('/assignments/{assignment}/destroy', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::post('/assignments/submission/{submission}/nilai', [AssignmentController::class, 'nilaiSubmission'])->name('assignments.nilai');
        
        // Nilai
        Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
        Route::post('/grades', [GradeController::class, 'store'])->name('grades.store');
        
        // Teaching Attendance
        Route::get('/teaching-attendance', [TeachingAttendanceController::class, 'index'])->name('teaching-attendance.index');
        Route::post('/teaching-attendance', [TeachingAttendanceController::class, 'store'])->name('teaching-attendance.store');
        
        // Absensi Mahasiswa per Pertemuan
        Route::get('/absensi/{schedule}/start', [StudentAttendanceController::class, 'startAbsensi'])->name('absensi.start');
        Route::post('/absensi/store', [StudentAttendanceController::class, 'store'])->name('absensi.store');
        Route::post('/absensi/update-async', [StudentAttendanceController::class, 'updateAsync'])->name('absensi.updateAsync');
        Route::get('/absensi/rekap', [StudentAttendanceController::class, 'rekap'])->name('absensi.rekap');

        // Schedules CRUD (Admin & Dosen)
        Route::resource('schedules', ScheduleController::class)->except(['index', 'show']);
        Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');

        // Ujian Pengganti (Admin & Dosen)
        Route::get('/admin/exam-replacements', [ExamReplacementController::class, 'adminIndex'])->name('exam.replacement.admin.index');
        Route::post('/admin/exam-replacements/{replacement}/approve', [ExamReplacementController::class, 'approve'])->name('exam.replacement.approve');
        Route::post('/admin/exam-replacements/{replacement}/reject', [ExamReplacementController::class, 'reject'])->name('exam.replacement.reject');
    });

    // ──────────────────────────────────────────────────────────
    // DOSEN ONLY ROUTES
    // ──────────────────────────────────────────────────────────
    Route::middleware('role:dosen')->group(function () {
        Route::get('/dosen-jadwal', function() {
            return redirect()->route('schedules.byDosen', Auth::user());
        })->name('dosen.jadwal');
        Route::get('/my-supervisions', [ExamScheduleController::class, 'mySupervisions'])->name('exam.mySupervisions');
        // Exam / Supervisions
        Route::get('/exam-supervisions', [App\Http\Controllers\ExamScheduleController::class, 'dosenSupervisions'])->name('dosen.supervisions.index');

        // LMS Modules
        Route::resource('modules', App\Http\Controllers\LearningModuleController::class)->names([
            'index' => 'dosen.modules.index',
            'create' => 'dosen.modules.create',
            'store' => 'dosen.modules.store',
            'edit' => 'dosen.modules.edit',
            'update' => 'dosen.modules.update',
            'destroy' => 'dosen.modules.destroy',
        ]);
    });

    // ──────────────────────────────────────────────────────────
    // MAHASISWA ONLY ROUTES
    // ──────────────────────────────────────────────────────────
    Route::middleware('role:user')->group(function () {
        Route::get('/schedules/mahasiswa', [ScheduleController::class, 'mahasiswaJadwal'])->name('schedules.mahasiswa');
        Route::get('/jadwal-kuliah', [SelfAttendanceController::class, 'index'])->name('mahasiswa.jadwal');
        Route::post('/self-checkin', [SelfAttendanceController::class, 'checkIn'])->name('mahasiswa.checkin');
        Route::get('/self-checkin/qr', [SelfAttendanceController::class, 'qrCheckIn'])->name('mahasiswa.qrCheckin');
        
        Route::get('/my-grades', [GradeController::class, 'myGrades'])->name('grades.my');
        
        // Repeat Course Routes
        Route::get('/repeats', [App\Http\Controllers\CourseRepeatController::class, 'index'])->name('repeats.my');
        Route::post('/repeats', [App\Http\Controllers\CourseRepeatController::class, 'store'])->name('repeats.store');

        // STEM Exam Routes
        Route::get('/stem-exam', [App\Http\Controllers\StemExamController::class, 'index'])->name('stem.index');
        Route::get('/stem-exam/start/{id}', [App\Http\Controllers\StemExamController::class, 'start'])->name('stem.start');
        Route::post('/stem-exam/submit/{attemptId}', [App\Http\Controllers\StemExamController::class, 'submit'])->name('stem.submit');
        Route::get('/stem-exam/result/{attemptId}', [App\Http\Controllers\StemExamController::class, 'result'])->name('stem.result');
        Route::get('/stem-exam/pdf/{attemptId}', [App\Http\Controllers\StemExamController::class, 'printPdf'])->name('stem.pdf');
        
        // LMS Module View
        Route::get('/stem-exam/module/{id}', [App\Http\Controllers\LearningModuleController::class, 'show'])->name('stem.module.show');

        // TOEFL & Jadwal Kelas Routes
        Route::get('/toefl-exam', [SelfAttendanceController::class, 'toeflExam'])->name('mahasiswa.toefl');
        Route::get('/toefl-exam/take', [SelfAttendanceController::class, 'takeToefl'])->name('mahasiswa.toefl.take');
        Route::post('/toefl-exam/submit', [SelfAttendanceController::class, 'submitToefl'])->name('mahasiswa.toefl.submit');
        Route::get('/toefl-exam/result/{id}', [SelfAttendanceController::class, 'toeflResult'])->name('mahasiswa.toefl.result');
        Route::get('/jadwal-kelas', [SelfAttendanceController::class, 'jadwalKelas'])->name('mahasiswa.jadwal_kelas');

        // Ujian Pengganti (Mahasiswa)
        Route::get('/exam-replacements', [ExamReplacementController::class, 'index'])->name('exam.replacement.index');
        Route::post('/exam-replacements', [ExamReplacementController::class, 'store'])->name('exam.replacement.store');
        Route::post('/attendance/feedback', [SelfAttendanceController::class, 'storeFeedback'])->name('mahasiswa.attendance.feedback');
        Route::get('/mahasiswa/rekap-absen', [SelfAttendanceController::class, 'rekapAbsen'])->name('mahasiswa.rekap_absen');
    });

    // ──────────────────────────────────────────────────────────
    // MIXED ACCESS (Check manually in controller or view)
    // ──────────────────────────────────────────────────────────
    Route::get('/exam', [ExamScheduleController::class, 'index'])->name('exam.index');

    // Attendance Routes
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/time-in', [AttendanceController::class, 'timeIn'])->name('attendance.timeIn');
        Route::post('/time-out', [AttendanceController::class, 'timeOut'])->name('attendance.timeOut');

        // Admin-only Attendance Management
        Route::middleware('role:admin')->group(function () {
            Route::get('/create', [AttendanceController::class, 'create'])->name('attendance.create');
            Route::post('/', [AttendanceController::class, 'store'])->name('attendance.store');
            Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
            Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
            Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
        });
    });

    // Assignment Show (Dosen & Mahasiswa) & Submit (Mahasiswa)
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');

});