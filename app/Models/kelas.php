<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'classes';
    protected $fillable = ['nomor_kelas', 'semester', 'department_id', 'tahun_ajaran'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function courses()
    {
        return $this->belongsToMany(\App\Models\Course::class, 'class_course', 'class_id', 'course_id');
    }

    /** Mahasiswa yang terdaftar di kelas ini */
    public function mahasiswa()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'user');
    }
}
