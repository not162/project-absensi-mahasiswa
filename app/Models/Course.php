<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'kode_matkul',
        'nama_matkul',
        'sks',
        'semester',
        'department_id',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    // (opsional) relasi ke lecturer_courses
    public function lecturerCourses(): HasMany
    {
        return $this->hasMany(LecturerCourse::class);
    }

    public function classes()
    {
    return $this->belongsToMany(Kelas::class, 'class_course', 'course_id', 'class_id');
    }

    public function department()
    {
    return $this->belongsTo(Department::class);
    }
    }
