<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    protected $fillable = ['assignment_id', 'student_id', 'link_tugas', 'file_tugas', 'file_tugas_original', 'catatan', 'nilai', 'feedback', 'submitted_at'];
    protected $casts = ['submitted_at' => 'datetime'];

    public function assignment() { return $this->belongsTo(Assignment::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function sudahDinilai(): bool { return $this->nilai !== null; }
    public function hasFile(): bool { return !empty($this->file_tugas); }
}