<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyUnivAIService
{
    protected $apiKey;
    protected $model;
    protected $analyticsService;

    public function __construct(StudentAnalyticsService $analyticsService)
    {
        $this->apiKey = env('GROQ_API_KEY');
        $this->model = env('GROQ_MODEL', 'llama-3.3-70b-versatile');
        $this->analyticsService = $analyticsService;
    }

    /**
     * Generate AI response based on student context (RAG) and message history.
     */
    public function getResponse(User $student, array $chatHistory, string $userMessage): string
    {
        // 1. Retrieve RAG Context (Academic status, grades, attendance, prediction analytics)
        $analytics = $this->analyticsService->analyze($student);
        $context = $this->buildRagContext($student, $analytics);

        $currentTimestamp = now()->translatedFormat('l, d F Y H:i:s');
        $rlhfScore = $student->rlhf_score ?? 100.00;

        // 2. Define System Guardrails
        $systemPrompt = <<<PROMPT
Anda adalah "MyUniv", asisten kecerdasan buatan (AI) penasihat akademik resmi untuk mahasiswa Universitas Tangsel Raya.
Tugas utama Anda adalah membantu mahasiswa dalam menganalisis aktivitas belajar, memberikan rekomendasi tips belajar, memberikan strategi mendapat nilai bagus dalam ujian teori, dan memberikan panduan cara membuat proyek best-practice (proyek terstruktur & profesional).

WAKTU & TANGGAL PROYEK SAAT INI (REAL-TIME TIMESTAMP):
{$currentTimestamp}

SKOR PENYELARASAN RLHF ANDA SAAT INI:
{$rlhfScore}%

(Catatan Penyelarasan RLHF: Jika skor Anda di bawah 100%, Anda wajib bersikap ekstra sopan, ekstra hati-hati dalam memberikan saran, membatasi spekulasi, dan fokus memberikan jawaban yang super akurat untuk meningkatkan kepuasan mahasiswa kembali.)

INFORMASI AKADEMIK MAHASISWA SAAT INI (RAG CONTEXT):
{$context}

ATURAN DAN GUARDRAILS YANG WAJIB DIPATUHI:
1. Jawablah pertanyaan mahasiswa secara spesifik, ramah, dan solutif berdasarkan konteks akademik di atas.
2. Jika mahasiswa bertanya tentang kinerja atau masalah akademiknya (misal: kehadiran rendah, tugas belum dikumpul), sebutkan data di atas secara akurat dan tawarkan rekomendasi solusi yang realistis.
3. JANGAN PERNAH BERHALUSINASI (jangan mengarang data nilai, nama dosen, atau tingkat kehadiran yang tidak ada dalam konteks di atas).
4. JANGAN memberikan respon yang manipulatif, menjerumuskan, atau mendorong tindakan curang.
5. JIKA MAHASISWA BERTANYA HAL NON-AKADEMIK (misalnya resep masakan, gosip artis, olahraga luar kampus, atau hal umum lainnya yang tidak berhubungan dengan perkuliahan), Anda WAJIB menolak secara sopan: "Maaf, sebagai asisten akademik MyUniv, saya hanya diizinkan untuk membantu Anda dalam hal akademis, tips belajar, dan perkuliahan."
6. Jika mahasiswa bertanya tentang "tips belajar", "lulus ujian teori", atau "membuat project best practice", berikan penjelasan akademis yang terstruktur (contoh: metode Spaced Repetition, Active Recall untuk ujian teori, dan standard struktur folder/dokumentasi Git untuk project best-practice).
7. Gunakan Bahasa Indonesia yang sopan, profesional, namun tetap mudah dipahami.
8. KETENTUAN TAMPILAN OUTPUT: JANGAN PERNAH menggunakan kata, kalimat, atau awalan '*output responses ai*' atau label serupa. Langsung berikan jawaban bersih berformat markdown.
PROMPT;

        // 3. Prepare messages array for Groq API
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Append chat history (limit to last 10 messages to save context space)
        $recentHistory = array_slice($chatHistory, -10);
        foreach ($recentHistory as $msg) {
            $messages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['content']
            ];
        }

        // Append current message
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        // 4. Request Groq API
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.3, // low temperature to reduce hallucination
                'max_tokens' => 1500,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? 'Maaf, terjadi kesalahan saat memproses respon AI.';
            } else {
                Log::error('Groq API Error Response: ' . $response->body());
                return 'Maaf, terjadi masalah koneksi dengan server AI MyUniv. Silakan coba beberapa saat lagi.';
            }
        } catch (\Exception $e) {
            Log::error('Groq API Exception: ' . $e->getMessage());
            return 'Maaf, sistem AI MyUniv sedang mengalami gangguan teknis. Hubungi admin akademik jika masalah berlanjut.';
        }
    }

    /**
     * Build RAG Context into markdown format for the LLM.
     */
    protected function buildRagContext(User $student, array $analytics): string
    {
        $metrics = $analytics['metrics'];
        $prediction = $analytics['prediction'];

        // Get schedules/courses
        $coursesText = "";
        if ($student->class_id) {
            $schedules = \App\Models\Schedule::with(['course', 'lecturer'])->where('class_id', $student->class_id)->get();
            foreach ($schedules as $s) {
                $coursesText .= "- " . ($s->course->nama_matkul ?? '-') . " (SKS: " . ($s->course->sks ?? '-') . ", Dosen: " . ($s->lecturer->name ?? '-') . ")\n";
            }
        } else {
            $coursesText = "- Tidak terdaftar di mata kuliah mana pun.\n";
        }

        // Get recent grades
        $gradesText = "";
        if ($student->grades->count() > 0) {
            foreach ($student->grades as $g) {
                $gradesText .= "- " . ($g->course->nama_matkul ?? '-') . ": Tugas=" . ($g->nilai_tugas ?? '-') . ", UTS=" . ($g->nilai_uts ?? '-') . ", UAS=" . ($g->nilai_uas ?? '-') . ", Akhir=" . ($g->nilai_akhir ?? '-') . ", Grade=" . ($g->nilai_huruf ?? '-') . "\n";
            }
        } else {
            $gradesText = "- Belum ada nilai terinput.\n";
        }

        $deptName = $student->department->name ?? '-';
        $kelasNomor = $student->kelas->nomor_kelas ?? '-';
        $semester = $student->kelas->semester ?? '-';

        $dosenNoteText = "";
        if ($student->kelas && $student->kelas->context_note) {
            $dosenNoteText = "INSTRUKSI KHUSUS & CATATAN BIMBINGAN DARI DOSEN WALI/PENGAMPU KELAS:\n\"" . $student->kelas->context_note . "\"\n(Catatan Penting: Berikan prioritas atau penekanan bimbingan Anda agar selaras dengan arahan khusus dosen di atas kepada mahasiswa.)\n";
        }

        return <<<CONTEXT
DATA MAHASISWA:
- Nama: {$student->name}
- NIM: {$student->nim}
- Program Studi: {$deptName}
- Kelas: {$kelasNomor} (Semester {$semester})

MATA KULIAH YANG DIIKUTI:
{$coursesText}

STATISTIK AKADEMIK & KINERJA:
- Persentase Kehadiran Kelas: {$metrics['attendance_rate']}% ({$metrics['attended_meetings']}/{$metrics['total_meetings']} pertemuan berlangsung)
- Persentase Pengumpulan Tugas: {$metrics['submission_rate']}% ({$metrics['submitted_assignments']}/{$metrics['total_assignments']} tugas dirilis)
- Rata-rata Nilai Tugas: {$metrics['avg_assignment_grade']}
- Rata-rata Nilai Ujian: {$metrics['avg_exam_grade']}
- Total Mata Kuliah Mengulang: {$metrics['total_repeats']}

HASIL PREDIKSI RISIKO AKADEMIK (PHP MACHINE LEARNING MODEL):
- Skor Risiko: {$prediction['risk_score']} / 100
- Kategori Risiko: {$prediction['risk_level']}
- Rekomendasi Sistem: {$prediction['recommendation']}

{$dosenNoteText}
CONTEXT;
    }
}
