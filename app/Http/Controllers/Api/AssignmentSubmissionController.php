<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentSubmissionController extends Controller
{
    public function index(Assignment $assignment)
    {
        if ($assignment->meeting->lecturer_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $submissions = $assignment->submissions()
            ->with('student:id,name')
            ->get()
            ->map(function ($sub) {
                return [
                    'student_id' => $sub->student_id,
                    'file_url' => asset('storage/' . $sub->file_tugas),
                    'file_name' => $sub->file_tugas_original,
                    'catatan' => $sub->catatan,
                    'submitted_at' => $sub->submitted_at ? $sub->submitted_at->format('d/m/Y H:i') : '-',
                    'nilai' => $sub->nilai,
                    'feedback' => $sub->feedback
                ];
            });

        return response()->json([
            'total_submitted' => $submissions->count(),
            'submissions' => $submissions->keyBy('student_id')
        ]);
    }
}
