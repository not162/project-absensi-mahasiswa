<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    protected $fillable = [
        'tipe', 'course_id', 'class_id', 'semester',
        'department_id', 'tanggal', 'jam_mulai', 'jam_selesai',
        'ruangan', 'pengawas_id', 'tipe_soal', 'tahun_ajaran', 'periode', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function course()     { return $this->belongsTo(Course::class); }
    public function kelas()      { return $this->belongsTo(Kelas::class, 'class_id'); }
    public function department() { return $this->belongsTo(Department::class); }

    /** Dosen pengawas ujian (diambil dari users role=dosen) */
    public function pengawas()
    {
        return $this->belongsTo(User::class, 'pengawas_id');
    }
}
