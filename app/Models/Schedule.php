<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'class_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'mode',
        'ruangan',
        'link_online',
        'kode_online',
        'tahun_ajaran',
    ];

    /** Mata kuliah yang dijadwalkan */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /** Dosen pengampu jadwal ini */
    public function lecturer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Kelas yang diajar pada jadwal ini */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    /** Riwayat absen mengajar untuk jadwal ini */
    public function teachingAttendances()
    {
        return $this->hasMany(TeachingAttendance::class);
    }

    /** Helper: apakah jadwal ini online? */
    public function isOnline(): bool
    {
        return $this->mode === 'online';
    }

    /** Helper: tampilkan lokasi / kode pertemuan */
    public function getLokasiAttribute(): string
    {
        if ($this->mode === 'online') {
            $kode = $this->kode_online ? " (Kode: {$this->kode_online})" : '';
            return ($this->link_online ?? 'Online') . $kode;
        }
        return $this->ruangan ?? '-';
    }
}
