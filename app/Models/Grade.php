<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'class_id', 'tahun_ajaran',
        'nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_akhir', 'grade_huruf',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    /** Hitung nilai akhir & grade huruf otomatis */
    public function hitungNilaiAkhir(): void
    {
        $tugas = $this->nilai_tugas ?? 0;
        $uts   = $this->nilai_uts ?? 0;
        $uas   = $this->nilai_uas ?? 0;

        // Bobot umum: Tugas 30%, UTS 30%, UAS 40%
        $akhir = ($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4);
        $this->nilai_akhir = round($akhir, 2);
        $this->grade_huruf = self::konversiHuruf($this->nilai_akhir);
    }

    public static function konversiHuruf(?float $nilai): string
    {
        if ($nilai === null) return '-';
        if ($nilai >= 85) return 'A';
        if ($nilai >= 80) return 'AB';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 70) return 'BC';
        if ($nilai >= 65) return 'C';
        if ($nilai >= 50) return 'D';
        return 'E';
    }

    /** Helper: apakah nilai ini termasuk lulus (bisa diulang jika tidak) */
    public function isLulus(): bool
    {
        return in_array($this->grade_huruf, ['A', 'AB', 'B', 'BC', 'C']);
    }
}
