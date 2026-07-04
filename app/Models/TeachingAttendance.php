<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingAttendance extends Model
{
    protected $fillable = [
        'schedule_id', 'user_id', 'tanggal', 'jam', 'status', 'materi', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
