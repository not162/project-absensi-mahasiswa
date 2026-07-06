<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToeflResult extends Model
{
    protected $fillable = ['user_id', 'listening_score', 'structure_score', 'reading_score', 'total_score'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
