<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $fillable = [
        'meeting_id', 'student_id', 'status', 'keterangan', 'feedback_dosen', 'feedback_sesuai',
    ];

    public function meeting()
    {
        return $this->belongsTo(ClassMeeting::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
