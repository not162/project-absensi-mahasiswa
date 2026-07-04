<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Halaman ini menampilkan "Data Dosen" untuk admin (bukan riwayat absensi)
            $dosenList = User::where('role', 'dosen')
                ->with(['department', 'lecturerCourses.course', 'schedules.course'])
                ->orderBy('name')
                ->paginate(15);

            return view('attendance.index', ['dosenList' => $dosenList]);
        }

        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('attendance_date', 'desc')
            ->paginate(15);

        return view('attendance.my', compact('attendances'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')->with('error', 'Unauthorized');
        }
        
        $users = User::where('role', 'user')->get();
        return view('attendance.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,late,absent,sick,permission',
            'notes' => 'nullable|string',
        ]);

        $existingAttendance = Attendance::where('user_id', $validated['user_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->first();

        if ($existingAttendance) {
            return redirect()->back()->with('error', 'Attendance already recorded for this date');
        }

        Attendance::create($validated);

        return redirect()->route('attendance.index')->with('success', 'Attendance recorded successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $this->authorize('view', $attendance);
        return view('attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        $this->authorize('update', $attendance);
        $users = User::where('role', 'user')->get();
        return view('attendance.edit', compact('attendance', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $this->authorize('update', $attendance);
        
        $validated = $request->validate([
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,late,absent,sick,permission',
            'notes' => 'nullable|string',
        ]);

        $attendance->update($validated);

        return redirect()->route('attendance.index')->with('success', 'Attendance updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $this->authorize('delete', $attendance);
        $attendance->delete();

        return redirect()->route('attendance.index')->with('success', 'Attendance deleted successfully');
    }

    /**
     * Record time in for user
     */
    public function timeIn(Request $request)
{
    $user = Auth::user();
    $today = Carbon::today();

    $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('attendance_date', $today)
        ->first();

    if (!$attendance) {
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attendance_date = $today;
    }

    $currentTime = Carbon::now();
    $attendance->time_in = $currentTime->toTimeString();
    
    $workStartTime = Carbon::today()->setHour(8)->setMinute(0);
    if ($currentTime > $workStartTime) {
        $attendance->status = 'late';
    } else {
        $attendance->status = 'present';
    }

    $attendance->save();

    return redirect()->back()->with('success', 'Time in recorded successfully');
}

public function timeOut(Request $request)
{
    $user = Auth::user();
    $today = Carbon::today();

    $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('attendance_date', $today)
        ->first();

    if (!$attendance) {
        return redirect()->back()->with('error', 'Please record time in first');
    }

    $attendance->time_out = Carbon::now()->format('H:i:s');
    $attendance->save();

    return redirect()->back()->with('success', 'Time out recorded successfully');
}
}