<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\CourseRepeat;
use App\Models\Department;
use App\Models\Grade;
use App\Models\Kelas;
use App\Models\User;
use App\Services\ExamScoringService;
use App\Services\MyUnivAIService;
use App\Services\StudentAnalyticsService;
use App\Services\VectorSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ServicesPatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_scoring_fuzzy_handles_boundary_and_normal_time_correctly(): void
    {
        $service = new ExamScoringService();

        $perfectFast = $service->evaluateFuzzy(100, 20, 'Teknik Informatika');
        $this->assertSame(100.0, $perfectFast['fuzzy_score']);
        $this->assertSame('Lulus Murni', $perfectFast['decision']);

        $zeroSlow = $service->evaluateFuzzy(0, 130, 'Akuntansi');
        $this->assertSame(30.0, $zeroSlow['fuzzy_score']);
        $this->assertSame('Remedial', $zeroSlow['decision']);

        $highNormal = $service->evaluateFuzzy(100, 70, 'Akuntansi');
        $this->assertSame(90.0, $highNormal['fuzzy_score']);
        $this->assertSame('Lulus Murni', $highNormal['decision']);
    }

    public function test_student_analytics_does_not_penalize_when_no_meetings_or_assignments_exist(): void
    {
        $dept = Department::create(['name' => 'Teknik Informatika', 'description' => 'TI']);
        $kelas = Kelas::create([
            'nomor_kelas' => 1,
            'semester' => 1,
            'department_id' => $dept->id,
            'tahun_ajaran' => '2024/2025',
        ]);
        $student = User::create([
            'name' => 'Mahasiswa Baru',
            'email' => 'mhsbaru@gmail.com',
            'nim' => '20240019999',
            'role' => 'user',
            'department_id' => $dept->id,
            'class_id' => $kelas->id,
            'password' => Hash::make('password'),
        ]);

        $analysis = app(StudentAnalyticsService::class)->analyze($student);

        $this->assertSame(0, $analysis['prediction']['risk_score']);
        $this->assertSame('EXCELLENT', $analysis['prediction']['risk_level']);
        $this->assertSame(5, $analysis['stars_analysis']['stars_count']);
        $this->assertSame('Belum Ada Pertemuan', $analysis['stars_analysis']['decision_input']['attendance']);
        $this->assertSame('Belum Ada Tugas', $analysis['stars_analysis']['decision_input']['submission']);
    }

    public function test_student_analytics_counts_disetujui_course_repeats(): void
    {
        $dept = Department::create(['name' => 'Teknik Informatika', 'description' => 'TI']);
        $course = Course::create([
            'kode_matkul' => 'TI101',
            'nama_matkul' => 'Algoritma',
            'sks' => 3,
            'semester' => 1,
            'department_id' => $dept->id,
        ]);
        $student = User::create([
            'name' => 'Mahasiswa Repeat',
            'email' => 'repeat@gmail.com',
            'nim' => '20240018888',
            'role' => 'user',
            'department_id' => $dept->id,
            'password' => Hash::make('password'),
        ]);

        CourseRepeat::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'alasan' => 'Perbaikan nilai',
            'status' => 'disetujui',
        ]);

        $analysis = app(StudentAnalyticsService::class)->analyze($student);
        $this->assertSame(1, $analysis['metrics']['total_repeats']);
        $this->assertGreaterThanOrEqual(10, $analysis['prediction']['risk_score']);
    }

    public function test_vector_search_supports_llm_json_and_short_token_local_fallback(): void
    {
        config(['services.groq.key' => 'test-key']);

        Http::fake([
            'https://api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'courses' => [
                                ['id' => 2, 'score' => 0.92],
                                ['id' => 1, 'score' => 0.45],
                            ],
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $docs = [
            ['id' => 1, 'text' => 'Basis Data SQL'],
            ['id' => 2, 'text' => 'Desain UI UX Mobile'],
        ];

        $service = new VectorSearchService();
        $llmResults = $service->search('tampilan aplikasi', $docs, 5);
        $this->assertCount(2, $llmResults);
        $this->assertSame(2, $llmResults[0]['id']);

        // Local fallback for 2-char token "UI"
        config(['services.groq.key' => null]);
        $localService = new VectorSearchService();
        $localResults = $localService->search('UI', $docs, 5);
        $this->assertCount(1, $localResults);
        $this->assertSame(2, $localResults[0]['id']);
    }

    public function test_myuniv_ai_service_includes_grade_huruf_in_rag_context(): void
    {
        config(['services.groq.key' => 'test-key']);

        $dept = Department::create(['name' => 'Teknik Informatika', 'description' => 'TI']);
        $course = Course::create([
            'kode_matkul' => 'TI201',
            'nama_matkul' => 'Pemrograman Web',
            'sks' => 4,
            'semester' => 2,
            'department_id' => $dept->id,
        ]);
        $student = User::create([
            'name' => 'Budi',
            'email' => 'budi@gmail.com',
            'nim' => '20240017777',
            'role' => 'user',
            'department_id' => $dept->id,
            'password' => Hash::make('password'),
        ]);
        Grade::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'tahun_ajaran' => '2024/2025',
            'nilai_tugas' => 85,
            'nilai_uts' => 80,
            'nilai_uas' => 90,
            'nilai_akhir' => 85.5,
            'grade_huruf' => 'A',
        ]);

        Http::fake([
            'https://api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => ['content' => 'Halo Budi, nilai Pemrograman Web Anda sangat baik.'],
                ]],
            ], 200),
        ]);

        $reply = app(MyUnivAIService::class)->getResponse($student, [], 'Bagaimana nilai saya?');
        $this->assertStringContainsString('Pemrograman Web', $reply);

        Http::assertSent(function ($request) {
            $systemMsg = $request['messages'][0]['content'] ?? '';
            return str_contains($systemMsg, 'NILAI MATA KULIAH:')
                && str_contains($systemMsg, 'Pemrograman Web')
                && str_contains($systemMsg, 'Grade=A');
        });
    }
}
