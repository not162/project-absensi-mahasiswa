<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        // Seeder mengikuti desain tabel `users` untuk role dosen.
        // (Agar konsisten dengan DosenController yang mengelola user role = 'dosen')

        $data = [
            [
                'name' => 'Dr. Andi Saputra',
                'kode_dosen' => 'DSN-001',
                'email' => 'andi.saputra@kampus.ac.id',
                'phone' => null,
                'department_name' => 'Teknik Informatika',
                'password' => 'password',
            ],
            [
                'name' => 'Prof. Budi Santoso',
                'kode_dosen' => 'DSN-002',
                'email' => 'budi.santoso@kampus.ac.id',
                'phone' => null,
                'department_name' => 'Sistem Informasi',
                'password' => 'password',
            ],
            [
                'name' => 'Ir. Citra Dewi',
                'kode_dosen' => 'DSN-003',
                'email' => 'citra.dewi@kampus.ac.id',
                'phone' => null,
                'department_name' => 'Teknik Mesin',
                'password' => 'password',
            ],
        ];

        foreach ($data as $row) {
            $departmentId = Department::where('name', $row['department_name'])->value('id');

            // skip jika department belum ada
            if (!$departmentId) {
                continue;
            }

            $exists = User::where('email', $row['email'])
                ->orWhere('kode_dosen', $row['kode_dosen'])
                ->exists();

            if ($exists) {
                continue;
            }

            User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'kode_dosen' => $row['kode_dosen'],
                'phone' => $row['phone'],
                'password' => Hash::make($row['password']),
                'role' => 'dosen',
                'department_id' => $departmentId,
                'nim' => null,
            ]);
        }
    }
}




