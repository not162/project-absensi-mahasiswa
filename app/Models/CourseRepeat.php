<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseRepeat extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'grade_id', 'tahun_ajaran_target',
        'alasan', 'status', 'catatan_admin', 'bukti_foto', 'tanggal_ujian_ulang',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'approved_at'         => 'datetime',
        'tanggal_ujian_ulang' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
