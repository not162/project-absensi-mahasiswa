<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   protected $fillable = [
    'name', 'email', 'nim', 'phone', 'role',
    'password', 'department_id', 'kode_dosen', 'class_id', 'photo'
];

public function kelas()
{
    return $this->belongsTo(\App\Models\Kelas::class, 'class_id');
}

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /** Relasi ke jurusan */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /** Riwayat absensi user */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /** Jadwal mengajar (khusus dosen) */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /** Mata kuliah yang diajarkan (tanpa harus ada jadwal fisik) */
    public function lecturerCourses()
    {
        return $this->hasMany(LecturerCourse::class, 'user_id');
    }

    public function coursesTaught()
    {
        return $this->belongsToMany(Course::class, 'lecturer_courses', 'user_id', 'course_id')->withTimestamps();
    }


    /** Helper: apakah user ini dosen? */
    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    /** Helper: apakah user ini admin? */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Nilai mahasiswa (jika role=user) */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'user_id');
    }

    /** Pengajuan pengulangan mata kuliah (jika role=user) */
    public function courseRepeats()
    {
        return $this->hasMany(CourseRepeat::class, 'user_id');
    }

    /** Riwayat absen mengajar (jika role=dosen) */
    public function teachingAttendances()
    {
        return $this->hasMany(TeachingAttendance::class, 'user_id');
    }

    /** Jadwal mengawas ujian (jika role=dosen) */
    public function examSupervisions()
    {
        return $this->hasMany(ExamSchedule::class, 'pengawas_id');
    }

    /** Riwayat simulasi TOEFL/IELTS mahasiswa */
    public function toeflResults()
    {
        return $this->hasMany(ToeflResult::class, 'user_id');
    }
}