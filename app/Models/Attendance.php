<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon $attendance_date
 * @property string|null $time_in
 * @property string|null $time_out
 * @property string $status
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'time_in',
        'time_out',
        'status',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    /**
     * Get the user associated with the attendance.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
