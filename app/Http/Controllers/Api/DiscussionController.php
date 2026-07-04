<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscussionController extends Controller
{
    public function index(Request $request, Assignment $assignment)
    {
        $lastId = $request->get('last_id', 0);

        $messages = $assignment->discussions()
            ->where('id', '>', $lastId)
            ->with('user:id,name,role')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'user_id' => $msg->user_id,
                    'name' => $msg->user->name,
                    'role' => $msg->user->role,
                    'message' => $msg->message,
                    'time' => $msg->created_at->format('H:i')
                ];
            });

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, Assignment $assignment)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $assignment->discussions()->create([
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Discussion $discussion)
    {
        abort_if($discussion->user_id !== Auth::id(), 403, 'Unauthorized action.');

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $discussion->update([
            'message' => $request->message
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Discussion $discussion)
    {
        abort_if($discussion->user_id !== Auth::id(), 403, 'Unauthorized action.');

        $discussion->delete();

        return response()->json(['success' => true]);
    }
}
