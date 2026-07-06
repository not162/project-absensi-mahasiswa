<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StemExam;
use App\Models\StemQuestion;
use App\Models\StemAttempt;
use App\Models\StemAnswer;
use App\Services\ExamScoringService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class StemExamController extends Controller
{
    public function index()
    {
        $exam = StemExam::first(); // for now, assuming 1 dummy exam
        if (!$exam) {
            return redirect()->route('dashboard')->with('error', 'Belum ada ujian STEM yang tersedia.');
        }

        // Cek jika sudah pernah mengerjakan
        $attempt = StemAttempt::where('user_id', Auth::id())
            ->where('exam_id', $exam->id)
            ->first();

        if ($attempt && $attempt->status == 'completed') {
            return redirect()->route('stem.result', $attempt->id);
        }

        return view('mahasiswa.stem_exam.index', compact('exam', 'attempt'));
    }

    public function start(Request $request, int $id)
    {
        $exam = StemExam::findOrFail($id);
        
        $attempt = StemAttempt::where('user_id', Auth::id())
            ->where('exam_id', $exam->id)
            ->first();

        if (!$attempt) {
            $attempt = StemAttempt::create([
                'user_id' => Auth::id(),
                'exam_id' => $exam->id,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        if ($attempt->status == 'completed') {
            return redirect()->route('stem.result', $attempt->id);
        }

        // Calculate remaining time
        $timePassed = now()->diffInSeconds($attempt->started_at);
        $totalDuration = $exam->duration_minutes * 60;
        $timeRemaining = max(0, $totalDuration - $timePassed);

        if ($timeRemaining <= 0) {
            return $this->autoSubmit($attempt);
        }

        $questions = StemQuestion::where('exam_id', $exam->id)->get();
        return view('mahasiswa.stem_exam.take', compact('exam', 'attempt', 'questions', 'timeRemaining'));
    }

    public function submit(Request $request, int $attemptId)
    {
        $attempt = StemAttempt::findOrFail($attemptId);
        
        if ($attempt->status == 'completed') {
            return redirect()->route('stem.result', $attempt->id);
        }

        $answers = $request->input('answers', []);
        $questions = StemQuestion::where('exam_id', $attempt->exam_id)->get();

        $correctCount = 0;
        $categoryScores = ['S' => 0, 'T' => 0, 'E' => 0, 'M' => 0];
        $categoryCounts = ['S' => 0, 'T' => 0, 'E' => 0, 'M' => 0];

        foreach ($questions as $q) {
            $selectedOpt = $answers[$q->id] ?? null;
            $isCorrect = ($selectedOpt === $q->correct_opt);

            StemAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $q->id],
                ['selected_opt' => $selectedOpt, 'is_correct' => $isCorrect]
            );

            $categoryCounts[$q->category]++;
            if ($isCorrect) {
                $correctCount++;
                $categoryScores[$q->category]++;
            }
        }

        // Hitung raw score (0-100)
        $rawScore = ($correctCount / max(1, count($questions))) * 100;

        // Cari kategori terlemah untuk pathfinding remedial
        $weakestCat = 'M';
        $minScore = 100;
        foreach ($categoryScores as $cat => $score) {
            $catPercent = ($score / max(1, $categoryCounts[$cat])) * 100;
            if ($catPercent < $minScore) {
                $minScore = $catPercent;
                $weakestCat = $cat;
            }
        }

        // Waktu pengerjaan dalam menit
        $timeTakenMinutes = now()->diffInMinutes($attempt->started_at);

        // --- Panggil Service AI (Fuzzy & Dijkstra) ---
        $scoringService = new ExamScoringService();
        $departmentName = Auth::user()->department->name ?? 'Umum';
        
        $fuzzyResult = $scoringService->evaluateFuzzy($rawScore, $timeTakenMinutes, $departmentName);
        $remedialPath = $scoringService->getRemedialPath($weakestCat);

        // Update Attempt
        $attempt->update([
            'status' => 'completed',
            'finished_at' => now(),
            'raw_score' => $rawScore,
            'fuzzy_score' => $fuzzyResult['fuzzy_score'],
            'decision' => $fuzzyResult['decision'],
            'remedial_path' => $remedialPath
        ]);

        return redirect()->route('stem.result', $attempt->id);
    }

    private function autoSubmit(StemAttempt $attempt)
    {
        // Just dummy submit with empty answers if time is up and they haven't submitted
        $request = new Request();
        return $this->submit($request, $attempt->id);
    }

    public function result(int $attemptId)
    {
        $attempt = StemAttempt::with('exam', 'answers.question')->findOrFail($attemptId);
        
        // Ambil semua modul belajar untuk memetakan nama node dengan ketersediaan modul
        $availableModules = \App\Models\LearningModule::all()->keyBy('category_stem');

        return view('mahasiswa.stem_exam.result', compact('attempt', 'availableModules'));
    }

    public function printPdf(int $attemptId)
    {
        $attempt = StemAttempt::with('exam', 'answers.question', 'user.department')->findOrFail($attemptId);
        $pdf = Pdf::loadView('mahasiswa.stem_exam.pdf', compact('attempt'));
        return $pdf->download('Hasil_Ujian_STEM_' . $attempt->user->nim . '.pdf');
    }
}
