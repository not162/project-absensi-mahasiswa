<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StemQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function exam()
    {
        return $this->belongsTo(StemExam::class, 'exam_id');
    }
}
