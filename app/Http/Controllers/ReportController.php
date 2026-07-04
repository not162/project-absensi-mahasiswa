<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show attendance report index
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Generate monthly attendance report
     */
    public function monthly(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $query = Attendance::query()
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->with('user.department');

        if ($request->has('department_id') && $request->department_id) {
            $query->whereHas('user', function ($q) {
                $q->where('department_id', request('department_id'));
            });
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->paginate(20);

        $summary = $this->generateMonthlySummary($month, $year);
        $departments = Department::all();
        $users = User::where('role', 'user')->get();

        return view('reports.monthly', compact('attendances', 'summary', 'departments', 'users', 'month', 'year'));
    }

    /**
     * Generate daily attendance report
     */
    public function daily(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));

        $attendances = Attendance::whereDate('attendance_date', $date)
            ->with('user.department')
            ->orderBy('time_in')
            ->get();

        $summary = [
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'sick' => $attendances->where('status', 'sick')->count(),
            'permission' => $attendances->where('status', 'permission')->count(),
        ];

        $departments = Department::all();

        return view('reports.daily', compact('attendances', 'summary', 'date', 'departments'));
    }

    /**
     * Generate user attendance report
     */
    public function user(Request $request)
    {
        $userId = $request->get('user_id');
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));

        if (!$userId) {
            return redirect()->back()->with('error', 'Please select a user');
        }

        $user = User::findOrFail($userId);

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date')
            ->get();

        $summary = [
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'sick' => $attendances->where('status', 'sick')->count(),
            'permission' => $attendances->where('status', 'permission')->count(),
            'working_days' => $this->countWorkingDays($startDate, $endDate),
        ];

        $users = User::where('role', 'user')->get();

        return view('reports.user', compact('user', 'attendances', 'summary', 'users', 'userId', 'startDate', 'endDate'));
    }

    /**
     * Export report to CSV
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'monthly');

        if ($type === 'monthly') {
            return $this->exportMonthly($request);
        } elseif ($type === 'daily') {
            return $this->exportDaily($request);
        } elseif ($type === 'user') {
            return $this->exportUser($request);
        }

        return redirect()->back();
    }

    private function exportMonthly(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $attendances = Attendance::whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->with('user.department')
            ->orderBy('attendance_date')
            ->get();

        return $this->downloadCsv('attendance_' . $year . '_' . $month . '.csv', $attendances);
    }

    private function exportDaily(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));

        $attendances = Attendance::whereDate('attendance_date', $date)
            ->with('user.department')
            ->orderBy('time_in')
            ->get();

        return $this->downloadCsv('attendance_' . $date . '.csv', $attendances);
    }

    private function exportUser(Request $request)
    {
        $userId = $request->get('user_id');
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->with('user.department')
            ->orderBy('attendance_date')
            ->get();

        return $this->downloadCsv('attendance_report.csv', $attendances);
    }

    private function downloadCsv($filename, $data)
    {
        $csv = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($csv, ['User', 'Department', 'Date', 'Time In', 'Time Out', 'Status', 'Notes']);

        foreach ($data as $row) {
            fputcsv($csv, [
                $row->user->name,
                $row->user->department?->name ?? 'N/A',
                $row->attendance_date,
                $row->time_in,
                $row->time_out,
                $row->status,
                $row->notes,
            ]);
        }

        fclose($csv);
    }

    private function generateMonthlySummary($month, $year)
    {
        $attendances = Attendance::whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get();

        return [
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'sick' => $attendances->where('status', 'sick')->count(),
            'permission' => $attendances->where('status', 'permission')->count(),
        ];
    }

    private function countWorkingDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $count = 0;

        while ($start <= $end) {
            if ($start->isWeekday()) {
                $count++;
            }
            $start->addDay();
        }

        return $count;
    }
}
