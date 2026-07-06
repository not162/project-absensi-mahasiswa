<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category_stem',
        'file_path',
        'dosen_id',
    ];

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}
