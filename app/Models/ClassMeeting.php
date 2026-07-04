<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassMeeting extends Model
{
    protected $fillable = [
        'schedule_id', 'lecturer_id', 'tanggal', 'pertemuan_ke', 'materi',
    ];

    protected $casts = ['tanggal' => 'date'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function studentAttendances()
    {
        return $this->hasMany(StudentAttendance::class, 'meeting_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'meeting_id');
    }
}