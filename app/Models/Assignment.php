<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['meeting_id', 'judul', 'deskripsi', 'deadline', 'file_tugas', 'file_tugas_original'];
    protected $casts = ['deadline' => 'datetime'];

    public function meeting() { return $this->belongsTo(ClassMeeting::class); }
    public function submissions() { return $this->hasMany(AssignmentSubmission::class); }
    public function discussions() { return $this->hasMany(Discussion::class); }

    public function isDeadlinePassed(): bool
    {
        return $this->deadline && now()->isAfter($this->deadline);
    }

    public function hasFile(): bool
    {
        return !empty($this->file_tugas);
    }
}
