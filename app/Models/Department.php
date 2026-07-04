<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the users in the department.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function courses()
{
    return $this->hasMany(\App\Models\Course::class);
}

public function user()
{
    return $this->hasMany(\App\Models\User::class);
}

public function kelas()
{
    return $this->hasMany(\App\Models\Kelas::class);
}
}
