<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\ClassMeeting;
use App\Models\Course;
use App\Models\CourseRepeat;
use App\Models\Department;
use App\Models\Discussion;
use App\Models\ExamSchedule;
use App\Models\Grade;
use App\Models\Kelas;
use App\Models\Schedule;
use App\Models\StemAttempt;
use App\Models\StemExam;
use App\Models\StemQuestion;
use App\Models\ToeflResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoreWorkflowsPatchTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;
    private Kelas $kelas;
    private Course $course;
    private User $admin;
    private User $dosen;
    private User $otherDosen;
    private User $student;
    private User $otherStudent;
    private Schedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::create(['name' => 'Teknik Informatika', 'description' => 'TI']);
        $this->kelas = Kelas::create([
            'nomor_kelas' => 1,
            'semester' => 3,
            'department_id' => $this->dept->id,
            'tahun_ajaran' => '2024/2025',
        ]);
        $this->course = Course::create([
            'kode_matkul' => 'TI301',
            'nama_matkul' => 'Pemrograman Web',
            'sks' => 4,
            'semester' => 3,
            'department_id' => $this->dept->id,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $this->dosen = User::create([
            'name' => 'Dosen Satu',
            'email' => 'dosen1@kampus.ac.id',
            'kode_dosen' => 'DSN001',
            'role' => 'dosen',
            'department_id' => $this->dept->id,
            'password' => Hash::make('password'),
        ]);

        $this->otherDosen = User::create([
            'name' => 'Dosen Dua',
            'email' => 'dosen2@kampus.ac.id',
            'kode_dosen' => 'DSN002',
            'role' => 'dosen',
            'department_id' => $this->dept->id,
            'password' => Hash::make('password'),
        ]);

        $this->student = User::create([
            'name' => 'Mahasiswa Satu',
            'email' => 'mhs1@gmail.com',
            'nim' => '20240010001',
            'role' => 'user',
            'department_id' => $this->dept->id,
            'class_id' => $this->kelas->id,
            'password' => Hash::make('password'),
        ]);

        $this->otherStudent = User::create([
            'name' => 'Mahasiswa Dua',
            'email' => 'mhs2@gmail.com',
            'nim' => '20240010002',
            'role' => 'user',
            'department_id' => $this->dept->id,
            'class_id' => $this->kelas->id,
            'password' => Hash::make('password'),
        ]);

        $this->schedule = Schedule::create([
            'course_id' => $this->course->id,
            'user_id' => $this->dosen->id,
            'class_id' => $this->kelas->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'mode' => 'offline',
            'ruangan' => 'R.101',
            'tahun_ajaran' => '2024/2025',
        ]);
    }

    public function test_auth_redirects_and_role_dashboards_work(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        $this->actingAs($this->student)->get('/login')->assertRedirect('/dashboard');
        $this->actingAs($this->admin)->get('/dashboard')->assertOk();
        $this->actingAs($this->dosen)->get('/dashboard')->assertOk();
        $this->actingAs($this->student)->get('/dashboard')->assertOk();
    }

    public function test_dosen_registration_populates_lecturer_courses_semester(): void
    {
        $response = $this->post('/register', [
            'name' => 'Dosen Baru',
            'email' => 'dosenbaru@gmail.com',
            'password' => 'secret123',
            'role' => 'dosen',
            'identifier' => 'DSN999',
            'department_id' => $this->dept->id,
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['kode_dosen' => 'DSN999', 'role' => 'dosen']);
        $this->assertDatabaseHas('lecturer_courses', [
            'course_id' => $this->course->id,
            'semester' => $this->course->semester,
        ]);
    }

    public function test_schedule_replacement_fields_persist_on_create_and_update(): void
    {
        $this->actingAs($this->admin)->post(route('schedules.store'), [
            'course_id' => $this->course->id,
            'user_id' => $this->dosen->id,
            'class_id' => $this->kelas->id,
            'hari' => 'Sabtu',
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'mode' => 'offline',
            'ruangan' => 'R.202',
            'tahun_ajaran' => '2024/2025',
            'is_replacement' => '1',
            'replacement_date' => '2026-11-28',
        ])->assertRedirect(route('schedules.index'));

        $this->assertDatabaseHas('schedules', [
            'hari' => 'Sabtu',
            'is_replacement' => 1,
        ]);

        $created = Schedule::where('hari', 'Sabtu')->first();
        $this->assertSame('2026-11-28', $created->replacement_date?->format('Y-m-d'));

        $this->actingAs($this->admin)->get(route('schedules.edit', $created))
            ->assertOk()
            ->assertSee('value="2026-11-28"', false);
    }

    public function test_course_repeats_student_view_and_cancel_work(): void
    {
        $this->actingAs($this->student)
            ->get(route('repeats.my'))
            ->assertOk()
            ->assertSee('Pengajuan Pengulangan Mata Kuliah');

        $this->actingAs($this->student)->post(route('repeats.store'), [
            'course_id' => $this->course->id,
            'alasan' => 'Ingin memperbaiki nilai',
        ])->assertRedirect(route('repeats.my'));

        $repeat = CourseRepeat::firstOrFail();

        $this->actingAs($this->student)
            ->delete(route('repeats.destroy', $repeat))
            ->assertRedirect(route('repeats.my'));

        $this->assertDatabaseMissing('course_repeats', ['id' => $repeat->id]);
    }

    public function test_dosen_supervisions_route_and_student_exam_course_name_render(): void
    {
        $this->actingAs($this->dosen)
            ->get(route('dosen.supervisions.index'))
            ->assertOk();

        ExamSchedule::create([
            'tipe' => 'uts',
            'course_id' => $this->course->id,
            'class_id' => $this->kelas->id,
            'semester' => 3,
            'department_id' => $this->dept->id,
            'tanggal' => '2026-12-01',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'ruangan' => 'R.101',
            'pengawas_id' => $this->dosen->id,
            'tipe_soal' => 'tulis',
            'tahun_ajaran' => '2024/2025',
            'periode' => 'ganjil',
        ]);

        $this->actingAs($this->student)
            ->get(route('exam.index', ['tipe' => 'uts']))
            ->assertOk()
            ->assertSee('Pemrograman Web')
            ->assertSee('TI301');
    }

    public function test_khs_pdf_renders_grade_huruf_and_ab_bc_weights(): void
    {
        Grade::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'class_id' => $this->kelas->id,
            'tahun_ajaran' => '2024/2025',
            'nilai_tugas' => 82,
            'nilai_uts' => 80,
            'nilai_uas' => 82,
            'nilai_akhir' => 81.4,
            'grade_huruf' => 'AB',
        ]);

        $html = view('pdf.khs', [
            'user' => $this->student,
            'grades' => $this->student->grades()->with('course')->get(),
        ])->render();

        $this->assertStringContainsString('AB', $html);
        $this->assertStringContainsString('3.50', $html);

        $this->actingAs($this->student)
            ->get(route('grades.my.pdf'))
            ->assertOk();
    }

    public function test_admin_rekap_pdf_export_and_reports_work(): void
    {
        $this->actingAs($this->admin)
            ->get(route('absensi.rekapAdmin', ['export' => 'pdf']))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('reports.user'))
            ->assertOk();

        Attendance::create([
            'user_id' => $this->student->id,
            'attendance_date' => '2026-11-10',
            'time_in' => '08:00:00',
            'status' => 'present',
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.monthly', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('Mahasiswa Satu');

        $att = Attendance::firstOrFail();
        $this->actingAs($this->admin)
            ->get(route('attendance.edit', $att))
            ->assertOk();
    }

    public function test_nested_discussion_api_and_submission_polling_id(): void
    {
        $meeting = ClassMeeting::create([
            'schedule_id' => $this->schedule->id,
            'lecturer_id' => $this->dosen->id,
            'tanggal' => today()->toDateString(),
            'pertemuan_ke' => 1,
            'materi' => 'Pengenalan',
        ]);

        $assignment = Assignment::create([
            'meeting_id' => $meeting->id,
            'judul' => 'Tugas 1',
            'deskripsi' => 'Kerjakan',
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'link_tugas' => '',
            'file_tugas' => 'tugas-jawaban/test.pdf',
            'file_tugas_original' => 'test.pdf',
            'submitted_at' => now(),
        ]);

        $this->actingAs($this->dosen)
            ->getJson(route('api.assignments.submissions', $assignment))
            ->assertOk()
            ->assertJsonPath("submissions.{$this->student->id}.id", $submission->id);

        $this->actingAs($this->student)
            ->postJson(route('assignments.discussions.store', $assignment), ['message' => 'Halo Pak'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $disc = Discussion::firstOrFail();

        $this->actingAs($this->student)
            ->putJson(route('assignments.discussions.update', [$assignment, $disc]), ['message' => 'Halo Pak (edit)'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('discussions', ['id' => $disc->id, 'message' => 'Halo Pak (edit)']);

        $this->actingAs($this->student)
            ->deleteJson(route('assignments.discussions.destroy', [$assignment, $disc]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('discussions', ['id' => $disc->id]);
    }

    public function test_stem_and_toefl_ownership_and_ai_guardrails(): void
    {
        $exam = StemExam::create(['title' => 'STEM Test', 'duration_minutes' => 60]);
        StemQuestion::create([
            'exam_id' => $exam->id,
            'category' => 'M',
            'question_text' => '1+1?',
            'opt_a' => '2',
            'opt_b' => '3',
            'opt_c' => '4',
            'opt_d' => '5',
            'correct_opt' => 'A',
        ]);

        $attempt = StemAttempt::create([
            'user_id' => $this->student->id,
            'exam_id' => $exam->id,
            'status' => 'completed',
            'started_at' => now()->subMinutes(10),
            'finished_at' => now(),
            'raw_score' => 100,
            'fuzzy_score' => 100,
            'decision' => 'Lulus Murni',
            'remedial_path' => ['path' => [], 'total_effort_hours' => 0],
        ]);

        $this->actingAs($this->otherStudent)
            ->get(route('stem.result', $attempt->id))
            ->assertForbidden();

        $toefl = ToeflResult::create([
            'user_id' => $this->student->id,
            'listening_score' => 500,
            'structure_score' => 500,
            'reading_score' => 500,
            'total_score' => 500,
        ]);

        $this->actingAs($this->otherStudent)
            ->get(route('mahasiswa.toefl.result', $toefl->id))
            ->assertForbidden();

        // RLHF word boundary: "masalah" should NOT trigger "salah" penalty
        $this->actingAs($this->student)
            ->postJson(route('myuniv.ai.chat'), ['message' => 'Saya punya masalah dengan nilai tugas.'])
            ->assertOk()
            ->assertJson(['rlhf_score' => 100]);

        // Only teaching dosen can update class AI context note
        $this->actingAs($this->otherDosen)
            ->post(route('dosen.class.context.update'), [
                'class_id' => $this->kelas->id,
                'context_note' => 'Unauthorized edit',
            ])
            ->assertForbidden();

        $this->actingAs($this->dosen)
            ->post(route('dosen.class.context.update'), [
                'class_id' => $this->kelas->id,
                'context_note' => 'Fokus latihan algoritma.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('classes', [
            'id' => $this->kelas->id,
            'context_note' => 'Fokus latihan algoritma.',
        ]);
    }
}
