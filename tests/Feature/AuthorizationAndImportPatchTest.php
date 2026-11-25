<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorizationAndImportPatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_on_sqlite_without_errors(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, Department::count());
        $this->assertGreaterThan(0, User::where('role', 'admin')->count());
    }

    public function test_login_post_for_admin_dosen_and_student_reaches_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin Login',
            'email' => 'adminlogin@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('secret123'),
        ]);
        $dosen = User::create([
            'name' => 'Dosen Login',
            'email' => 'dosenlogin@gmail.com',
            'role' => 'dosen',
            'password' => Hash::make('secret123'),
        ]);
        $student = User::create([
            'name' => 'Student Login',
            'email' => 'mhslogin@gmail.com',
            'nim' => '20249990001',
            'role' => 'user',
            'password' => Hash::make('secret123'),
        ]);

        foreach ([$admin, $dosen, $student] as $account) {
            $this->get('/login')->assertOk();
            $captcha = session('captcha_result');

            $response = $this->post('/login', [
                'username' => $account->email,
                'password' => 'secret123',
                'captcha_answer' => $captcha,
            ]);
            $response->assertRedirect(route('dashboard'));
            $this->get('/dashboard')->assertOk();
            $this->post('/logout')->assertRedirect('/');
        }
    }

    public function test_semantic_search_handles_uppercase_short_query_over_http(): void
    {
        \Illuminate\Support\Facades\Http::fake();

        $admin = User::create([
            'name' => 'Admin Search',
            'email' => 'adminsearch@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);
        $dept = Department::create([
            'name' => 'Teknik Informatika',
            'description' => 'TI',
        ]);
        \App\Models\Course::create([
            'kode_matkul' => 'TI205',
            'nama_matkul' => 'Desain UI Mobile',
            'sks' => 3,
            'semester' => 2,
            'department_id' => $dept->id,
        ]);

        $this->actingAs($admin)
            ->get(route('semantic.search', ['query' => 'UI']))
            ->assertOk()
            ->assertSee('Desain UI Mobile');
    }

    public function test_import_excel_rejects_binary_xlsx_and_dosen_route_returns_404_for_student(): void
    {
        $admin = User::create([
            'name' => 'Admin Guard',
            'email' => 'adminguard@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);
        $student = User::create([
            'name' => 'Bukan Dosen',
            'email' => 'bukandosen@gmail.com',
            'nim' => '20249990002',
            'role' => 'user',
            'password' => Hash::make('password'),
        ]);

        $fakeXlsx = UploadedFile::fake()->create(
            'users.xlsx',
            10,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $this->actingAs($admin)
            ->post(route('users.import'), ['file' => $fakeXlsx])
            ->assertSessionHasErrors('file');

        $this->actingAs($admin)
            ->get(route('dosen.show', $student))
            ->assertNotFound();
    }

    public function test_assign_class_rejects_mismatched_department_and_non_student(): void
    {
        $admin = User::create([
            'name' => 'Admin Assign',
            'email' => 'adminassign@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);
        $deptA = Department::create(['name' => 'Informatika', 'description' => 'IF']);
        $deptB = Department::create(['name' => 'Elektro', 'description' => 'EL']);
        $kelasB = Kelas::create([
            'nomor_kelas' => 1,
            'semester' => 1,
            'department_id' => $deptB->id,
            'tahun_ajaran' => '2024/2025',
        ]);
        $student = User::create([
            'name' => 'Student Assign',
            'email' => 'studentassign@gmail.com',
            'nim' => '20249990003',
            'role' => 'user',
            'department_id' => $deptA->id,
            'password' => Hash::make('password'),
        ]);
        $dosen = User::create([
            'name' => 'Dosen Assign',
            'email' => 'dosenassign@gmail.com',
            'role' => 'dosen',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)
            ->post(route('users.assignClass', $student), ['class_id' => $kelasB->id])
            ->assertSessionHasErrors();

        $student->refresh();
        $this->assertNull($student->class_id);

        $this->actingAs($admin)
            ->post(route('users.assignClass', $dosen), ['class_id' => $kelasB->id])
            ->assertNotFound();
    }
}
