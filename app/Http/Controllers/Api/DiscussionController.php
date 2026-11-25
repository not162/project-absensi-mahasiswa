<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscussionController extends Controller
{
    private function authorizeAccess(Assignment $assignment): void
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return;
        }
        if ($user->role === 'dosen') {
            abort_if($assignment->meeting?->lecturer_id !== $user->id, 403, 'Unauthorized action.');
            return;
        }
        abort_if($assignment->meeting?->schedule?->class_id !== $user->class_id, 403, 'Unauthorized action.');
    }

    public function index(Request $request, Assignment $assignment)
    {
        $this->authorizeAccess($assignment);
        $lastId = (int) $request->get('last_id', 0);

        $messages = $assignment->discussions()
            ->where('id', '>', $lastId)
            ->with('user:id,name,role')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'user_id' => $msg->user_id,
                    'name' => $msg->user->name ?? 'Pengguna',
                    'role' => $msg->user->role ?? 'user',
                    'message' => $msg->message,
                    'time' => $msg->created_at->format('H:i')
                ];
            });

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, Assignment $assignment)
    {
        $this->authorizeAccess($assignment);

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $assignment->discussions()->create([
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Assignment $assignment, Discussion $discussion)
    {
        abort_if($discussion->assignment_id !== $assignment->id, 404);
        abort_if($discussion->user_id !== Auth::id(), 403, 'Unauthorized action.');

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $discussion->update([
            'message' => $request->message
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Assignment $assignment, Discussion $discussion)
    {
        abort_if($discussion->assignment_id !== $assignment->id, 404);
        abort_if($discussion->user_id !== Auth::id(), 403, 'Unauthorized action.');

        $discussion->delete();

        return response()->json(['success' => true]);
    }
}
