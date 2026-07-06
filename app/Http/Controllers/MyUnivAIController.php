<?php

namespace App\Http\Controllers;

use App\Services\StudentAnalyticsService;
use App\Services\MyUnivAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyUnivAIController extends Controller
{
    protected $analyticsService;
    protected $aiService;

    public function __construct(StudentAnalyticsService $analyticsService, MyUnivAIService $aiService)
    {
        $this->analyticsService = $analyticsService;
        $this->aiService = $aiService;
    }

    /**
     * Display the MyUniv AI Advisor page with real-time academic analysis.
     */
    public function index()
    {
        $user = Auth::user();
        abort_if($user->role !== 'user', 403, 'Hanya mahasiswa yang dapat mengakses penasihat akademik AI MyUniv.');

        // Compute PHP Machine Learning risk assessment for display on dashboard UI
        $analysis = $this->analyticsService->analyze($user);

        // Reset chat history in session if requested
        if (request()->has('reset')) {
            session()->forget('myuniv_chat_history');
            return redirect()->route('myuniv.ai');
        }

        $chatHistory = session()->get('myuniv_chat_history', []);

        return view('mahasiswa.myuniv_chat', compact('analysis', 'chatHistory', 'user'));
    }

    /**
     * Send message to MyUniv AI and get response.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        abort_if($user->role !== 'user', 403);

        $userMessage = $request->message;
        
        // Auto RLHF detection based on text
        $lowerMsg = strtolower($userMessage);
        if (str_contains($lowerMsg, 'terima kasih') || str_contains($lowerMsg, 'terimakasih') || str_contains($lowerMsg, 'thank you') || str_contains($lowerMsg, 'thanks')) {
            $user->rlhf_score = min(200.0, $user->rlhf_score + 1.5);
            $user->save();
        } elseif (str_contains($lowerMsg, 'kurang puas') || str_contains($lowerMsg, 'kecewa') || str_contains($lowerMsg, 'salah') || str_contains($lowerMsg, 'tidak puas') || str_contains($lowerMsg, 'tidak akurat') || str_contains($lowerMsg, 'jelek')) {
            $user->rlhf_score = max(0.0, $user->rlhf_score - 0.18);
            $user->save();
        }

        $chatHistory = session()->get('myuniv_chat_history', []);

        // Call Groq API with RAG Context & Guardrails
        $aiResponse = $this->aiService->getResponse($user, $chatHistory, $userMessage);

        // Save history in session
        $chatHistory[] = ['role' => 'user', 'content' => $userMessage, 'time' => now()->format('H:i')];
        $chatHistory[] = ['role' => 'assistant', 'content' => $aiResponse, 'time' => now()->format('H:i')];
        session()->put('myuniv_chat_history', $chatHistory);

        return response()->json([
            'success' => true,
            'response' => $aiResponse,
            'time' => now()->format('H:i'),
            'rlhf_score' => round($user->rlhf_score, 2),
        ]);
    }

    /** Handle explicit thumbs / feedback button clicks */
    public function rlhfFeedback(Request $request)
    {
        $request->validate([
            'type' => 'required|in:terimakasih,tidak_cukup_puas,kurang_puas'
        ]);

        $user = Auth::user();
        $change = 0.0;

        if ($request->type === 'terimakasih') {
            $change = 1.5;
        } elseif ($request->type === 'tidak_cukup_puas') {
            $change = -0.18;
        } elseif ($request->type === 'kurang_puas') {
            $change = -2.1;
        }

        $user->rlhf_score = max(0.0, min(200.0, $user->rlhf_score + $change));
        $user->save();

        return response()->json([
            'success' => true,
            'rlhf_score' => round($user->rlhf_score, 2),
            'message' => 'Feedback RLHF berhasil dicatat.'
        ]);
    }
}
