<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StemExam extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function questions()
    {
        return $this->hasMany(StemQuestion::class, 'exam_id');
    }

    public function attempts()
    {
        return $this->hasMany(StemAttempt::class, 'exam_id');
    }
}
