<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StemAnswer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(StemAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(StemQuestion::class, 'question_id');
    }
}
