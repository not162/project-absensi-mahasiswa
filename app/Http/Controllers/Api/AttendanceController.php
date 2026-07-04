<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function pollLatest(Request $request)
    {
        // Get the latest attendance record's updated_at timestamp
        $latest = \App\Models\StudentAttendance::orderBy('updated_at', 'desc')->first();
        
        // Get the last 2 records for offline track record
        $lastTwo = \App\Models\StudentAttendance::with('student')
            ->orderBy('updated_at', 'desc')
            ->take(2)
            ->get()
            ->map(function ($att) {
                return [
                    'nama' => $att->student ? $att->student->name : 'Unknown',
                    'status' => $att->status,
                    'waktu' => $att->updated_at->format('d M Y, H:i')
                ];
            });
            
        $response = [
            'latest_timestamp' => $latest ? $latest->updated_at->timestamp : 0,
            'count' => \App\Models\StudentAttendance::count(),
            'last_two' => $lastTwo
        ];

        // If meeting_id is provided, send real-time detailed student statuses for that meeting
        if ($request->has('meeting_id')) {
            $meetingId = $request->get('meeting_id');
            $statuses = \App\Models\StudentAttendance::where('meeting_id', $meetingId)
                ->pluck('status', 'student_id')
                ->toArray();
            $response['meeting_statuses'] = $statuses;
        }
        
        return response()->json($response);
    }
}
