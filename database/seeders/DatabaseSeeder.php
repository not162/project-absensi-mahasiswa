<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use App\Models\Course;
use App\Models\Kelas;
use App\Models\Schedule;
use App\Models\ExamSchedule;
use App\Models\LecturerCourse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==================== 1. DEPARTMENTS ====================
        $programStudi = [
            ['name' => 'Teknik Informatika', 'description' => 'Program Studi Teknik Informatika'],
            ['name' => 'Sistem Informasi', 'description' => 'Program Studi Sistem Informasi'],
            ['name' => 'Teknik Mesin', 'description' => 'Program Studi Teknik Mesin'],
            ['name' => 'Administrasi Bisnis', 'description' => 'Program Studi Administrasi Bisnis'],
            ['name' => 'Akuntansi', 'description' => 'Program Studi Akuntansi'],
        ];

        foreach ($programStudi as $prodi) {
            Department::updateOrCreate(['name' => $prodi['name']], ['description' => $prodi['description']]);
        }

        $ti = Department::where('name', 'Teknik Informatika')->first()->id;
        $si = Department::where('name', 'Sistem Informasi')->first()->id;
        $tm = Department::where('name', 'Teknik Mesin')->first()->id;
        $ab = Department::where('name', 'Administrasi Bisnis')->first()->id;
        $ak = Department::where('name', 'Akuntansi')->first()->id;

        // ==================== 2. CLASSES (semester 1-8 tiap prodi) ====================
        $classDefs = [
            $ti => [1 => 3, 2 => 3, 3 => 2, 4 => 2, 5 => 2, 6 => 2, 7 => 1, 8 => 1],
            $si => [1 => 2, 2 => 2, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1],
            $tm => [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1],
            $ab => [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1],
            $ak => [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1],
        ];

        $tahunAjaran = '2024/2025';

        foreach ($classDefs as $deptId => $semesters) {
            foreach ($semesters as $semester => $jumlahKelas) {
                for ($n = 1; $n <= $jumlahKelas; $n++) {
                    DB::table('classes')->updateOrInsert(
                        [
                            'nomor_kelas'   => $n,
                            'semester'      => $semester,
                            'department_id' => $deptId,
                            'tahun_ajaran'  => $tahunAjaran,
                        ],
                        ['updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        }

        // ==================== 3. COURSES ====================
        $courses = [
            // --- Teknik Informatika ---
            ['kode_matkul' => 'TI101', 'nama_matkul' => 'Pengantar Teknologi Informasi', 'sks' => 3, 'semester' => 1, 'department_id' => $ti],
            ['kode_matkul' => 'TI102', 'nama_matkul' => 'Matematika Diskrit', 'sks' => 3, 'semester' => 1, 'department_id' => $ti],
            ['kode_matkul' => 'TI103', 'nama_matkul' => 'Algoritma dan Pemrograman', 'sks' => 4, 'semester' => 1, 'department_id' => $ti],
            ['kode_matkul' => 'TI104', 'nama_matkul' => 'Bahasa Indonesia', 'sks' => 2, 'semester' => 1, 'department_id' => $ti],
            ['kode_matkul' => 'TI105', 'nama_matkul' => 'Pendidikan Agama', 'sks' => 2, 'semester' => 1, 'department_id' => $ti],
            ['kode_matkul' => 'TI201', 'nama_matkul' => 'Pemrograman Berorientasi Objek', 'sks' => 4, 'semester' => 2, 'department_id' => $ti],
            ['kode_matkul' => 'TI202', 'nama_matkul' => 'Struktur Data', 'sks' => 3, 'semester' => 2, 'department_id' => $ti],
            ['kode_matkul' => 'TI203', 'nama_matkul' => 'Kalkulus', 'sks' => 3, 'semester' => 2, 'department_id' => $ti],
            ['kode_matkul' => 'TI204', 'nama_matkul' => 'Basis Data', 'sks' => 3, 'semester' => 2, 'department_id' => $ti],
            ['kode_matkul' => 'TI301', 'nama_matkul' => 'Pemrograman Web', 'sks' => 4, 'semester' => 3, 'department_id' => $ti],
            ['kode_matkul' => 'TI302', 'nama_matkul' => 'Jaringan Komputer', 'sks' => 3, 'semester' => 3, 'department_id' => $ti],
            ['kode_matkul' => 'TI303', 'nama_matkul' => 'Sistem Operasi', 'sks' => 3, 'semester' => 3, 'department_id' => $ti],
            ['kode_matkul' => 'TI304', 'nama_matkul' => 'Statistika', 'sks' => 3, 'semester' => 3, 'department_id' => $ti],
            ['kode_matkul' => 'TI401', 'nama_matkul' => 'Rekayasa Perangkat Lunak', 'sks' => 3, 'semester' => 4, 'department_id' => $ti],
            ['kode_matkul' => 'TI402', 'nama_matkul' => 'Pemrograman Mobile', 'sks' => 4, 'semester' => 4, 'department_id' => $ti],
            ['kode_matkul' => 'TI403', 'nama_matkul' => 'Kecerdasan Buatan', 'sks' => 3, 'semester' => 4, 'department_id' => $ti],
            ['kode_matkul' => 'TI404', 'nama_matkul' => 'Keamanan Sistem', 'sks' => 3, 'semester' => 4, 'department_id' => $ti],
            ['kode_matkul' => 'TI501', 'nama_matkul' => 'Machine Learning', 'sks' => 3, 'semester' => 5, 'department_id' => $ti],
            ['kode_matkul' => 'TI502', 'nama_matkul' => 'Cloud Computing', 'sks' => 3, 'semester' => 5, 'department_id' => $ti],
            ['kode_matkul' => 'TI503', 'nama_matkul' => 'Pengolahan Citra Digital', 'sks' => 3, 'semester' => 5, 'department_id' => $ti],
            ['kode_matkul' => 'TI601', 'nama_matkul' => 'Big Data', 'sks' => 3, 'semester' => 6, 'department_id' => $ti],
            ['kode_matkul' => 'TI602', 'nama_matkul' => 'Internet of Things', 'sks' => 3, 'semester' => 6, 'department_id' => $ti],
            ['kode_matkul' => 'TI603', 'nama_matkul' => 'Manajemen Proyek TI', 'sks' => 3, 'semester' => 6, 'department_id' => $ti],
            ['kode_matkul' => 'TI701', 'nama_matkul' => 'Kerja Praktik', 'sks' => 2, 'semester' => 7, 'department_id' => $ti],
            ['kode_matkul' => 'TI702', 'nama_matkul' => 'Etika Profesi', 'sks' => 2, 'semester' => 7, 'department_id' => $ti],
            ['kode_matkul' => 'TI703', 'nama_matkul' => 'Seminar Penelitian', 'sks' => 2, 'semester' => 7, 'department_id' => $ti],
            ['kode_matkul' => 'TI801', 'nama_matkul' => 'Skripsi', 'sks' => 6, 'semester' => 8, 'department_id' => $ti],

            // --- Sistem Informasi ---
            ['kode_matkul' => 'SI101', 'nama_matkul' => 'Pengantar Sistem Informasi', 'sks' => 3, 'semester' => 1, 'department_id' => $si],
            ['kode_matkul' => 'SI102', 'nama_matkul' => 'Algoritma dan Pemrograman', 'sks' => 3, 'semester' => 1, 'department_id' => $si],
            ['kode_matkul' => 'SI103', 'nama_matkul' => 'Matematika Bisnis', 'sks' => 3, 'semester' => 1, 'department_id' => $si],
            ['kode_matkul' => 'SI104', 'nama_matkul' => 'Bahasa Indonesia', 'sks' => 2, 'semester' => 1, 'department_id' => $si],
            ['kode_matkul' => 'SI201', 'nama_matkul' => 'Pemrograman Web', 'sks' => 3, 'semester' => 2, 'department_id' => $si],
            ['kode_matkul' => 'SI202', 'nama_matkul' => 'Basis Data', 'sks' => 3, 'semester' => 2, 'department_id' => $si],
            ['kode_matkul' => 'SI203', 'nama_matkul' => 'Analisis Sistem', 'sks' => 3, 'semester' => 2, 'department_id' => $si],
            ['kode_matkul' => 'SI301', 'nama_matkul' => 'Pemrograman Berorientasi Objek', 'sks' => 3, 'semester' => 3, 'department_id' => $si],
            ['kode_matkul' => 'SI302', 'nama_matkul' => 'Desain UI/UX', 'sks' => 3, 'semester' => 3, 'department_id' => $si],
            ['kode_matkul' => 'SI303', 'nama_matkul' => 'Jaringan Komputer', 'sks' => 3, 'semester' => 3, 'department_id' => $si],
            ['kode_matkul' => 'SI401', 'nama_matkul' => 'Sistem Informasi Manajemen', 'sks' => 3, 'semester' => 4, 'department_id' => $si],
            ['kode_matkul' => 'SI402', 'nama_matkul' => 'E-Commerce', 'sks' => 3, 'semester' => 4, 'department_id' => $si],
            ['kode_matkul' => 'SI403', 'nama_matkul' => 'Keamanan Informasi', 'sks' => 3, 'semester' => 4, 'department_id' => $si],
            ['kode_matkul' => 'SI501', 'nama_matkul' => 'Business Intelligence', 'sks' => 3, 'semester' => 5, 'department_id' => $si],
            ['kode_matkul' => 'SI502', 'nama_matkul' => 'Manajemen Proyek', 'sks' => 3, 'semester' => 5, 'department_id' => $si],
            ['kode_matkul' => 'SI601', 'nama_matkul' => 'Audit Sistem Informasi', 'sks' => 3, 'semester' => 6, 'department_id' => $si],
            ['kode_matkul' => 'SI602', 'nama_matkul' => 'Tata Kelola TI', 'sks' => 3, 'semester' => 6, 'department_id' => $si],
            ['kode_matkul' => 'SI701', 'nama_matkul' => 'Kerja Praktik', 'sks' => 2, 'semester' => 7, 'department_id' => $si],
            ['kode_matkul' => 'SI702', 'nama_matkul' => 'Seminar', 'sks' => 2, 'semester' => 7, 'department_id' => $si],
            ['kode_matkul' => 'SI801', 'nama_matkul' => 'Skripsi', 'sks' => 6, 'semester' => 8, 'department_id' => $si],

            // --- Teknik Mesin ---
            ['kode_matkul' => 'TM101', 'nama_matkul' => 'Pengantar Teknik Mesin', 'sks' => 3, 'semester' => 1, 'department_id' => $tm],
            ['kode_matkul' => 'TM102', 'nama_matkul' => 'Fisika Teknik', 'sks' => 3, 'semester' => 1, 'department_id' => $tm],
            ['kode_matkul' => 'TM103', 'nama_matkul' => 'Kalkulus', 'sks' => 3, 'semester' => 1, 'department_id' => $tm],
            ['kode_matkul' => 'TM201', 'nama_matkul' => 'Mekanika Teknik', 'sks' => 3, 'semester' => 2, 'department_id' => $tm],
            ['kode_matkul' => 'TM202', 'nama_matkul' => 'Material Teknik', 'sks' => 3, 'semester' => 2, 'department_id' => $tm],
            ['kode_matkul' => 'TM301', 'nama_matkul' => 'Termodinamika', 'sks' => 3, 'semester' => 3, 'department_id' => $tm],
            ['kode_matkul' => 'TM302', 'nama_matkul' => 'Elemen Mesin', 'sks' => 3, 'semester' => 3, 'department_id' => $tm],
            ['kode_matkul' => 'TM401', 'nama_matkul' => 'Mesin Konversi Energi', 'sks' => 3, 'semester' => 4, 'department_id' => $tm],
            ['kode_matkul' => 'TM402', 'nama_matkul' => 'Proses Manufaktur', 'sks' => 3, 'semester' => 4, 'department_id' => $tm],
            ['kode_matkul' => 'TM501', 'nama_matkul' => 'Perancangan Mesin', 'sks' => 3, 'semester' => 5, 'department_id' => $tm],
            ['kode_matkul' => 'TM601', 'nama_matkul' => 'Otomasi Industri', 'sks' => 3, 'semester' => 6, 'department_id' => $tm],
            ['kode_matkul' => 'TM701', 'nama_matkul' => 'Kerja Praktik', 'sks' => 2, 'semester' => 7, 'department_id' => $tm],
            ['kode_matkul' => 'TM801', 'nama_matkul' => 'Skripsi', 'sks' => 6, 'semester' => 8, 'department_id' => $tm],

            // --- Administrasi Bisnis ---
            ['kode_matkul' => 'AB101', 'nama_matkul' => 'Pengantar Administrasi Bisnis', 'sks' => 3, 'semester' => 1, 'department_id' => $ab],
            ['kode_matkul' => 'AB102', 'nama_matkul' => 'Matematika Bisnis', 'sks' => 3, 'semester' => 1, 'department_id' => $ab],
            ['kode_matkul' => 'AB201', 'nama_matkul' => 'Manajemen Pemasaran', 'sks' => 3, 'semester' => 2, 'department_id' => $ab],
            ['kode_matkul' => 'AB202', 'nama_matkul' => 'Akuntansi Dasar', 'sks' => 3, 'semester' => 2, 'department_id' => $ab],
            ['kode_matkul' => 'AB301', 'nama_matkul' => 'Manajemen SDM', 'sks' => 3, 'semester' => 3, 'department_id' => $ab],
            ['kode_matkul' => 'AB302', 'nama_matkul' => 'Hukum Bisnis', 'sks' => 3, 'semester' => 3, 'department_id' => $ab],
            ['kode_matkul' => 'AB401', 'nama_matkul' => 'Kewirausahaan', 'sks' => 3, 'semester' => 4, 'department_id' => $ab],
            ['kode_matkul' => 'AB402', 'nama_matkul' => 'Manajemen Keuangan', 'sks' => 3, 'semester' => 4, 'department_id' => $ab],
            ['kode_matkul' => 'AB501', 'nama_matkul' => 'Manajemen Strategik', 'sks' => 3, 'semester' => 5, 'department_id' => $ab],
            ['kode_matkul' => 'AB601', 'nama_matkul' => 'Bisnis Internasional', 'sks' => 3, 'semester' => 6, 'department_id' => $ab],
            ['kode_matkul' => 'AB701', 'nama_matkul' => 'Kerja Praktik', 'sks' => 2, 'semester' => 7, 'department_id' => $ab],
            ['kode_matkul' => 'AB801', 'nama_matkul' => 'Skripsi', 'sks' => 6, 'semester' => 8, 'department_id' => $ab],

            // --- Akuntansi ---
            ['kode_matkul' => 'AK101', 'nama_matkul' => 'Pengantar Akuntansi', 'sks' => 3, 'semester' => 1, 'department_id' => $ak],
            ['kode_matkul' => 'AK102', 'nama_matkul' => 'Matematika Keuangan', 'sks' => 3, 'semester' => 1, 'department_id' => $ak],
            ['kode_matkul' => 'AK201', 'nama_matkul' => 'Akuntansi Keuangan', 'sks' => 3, 'semester' => 2, 'department_id' => $ak],
            ['kode_matkul' => 'AK202', 'nama_matkul' => 'Perpajakan', 'sks' => 3, 'semester' => 2, 'department_id' => $ak],
            ['kode_matkul' => 'AK301', 'nama_matkul' => 'Akuntansi Biaya', 'sks' => 3, 'semester' => 3, 'department_id' => $ak],
            ['kode_matkul' => 'AK302', 'nama_matkul' => 'Sistem Informasi Akuntansi', 'sks' => 3, 'semester' => 3, 'department_id' => $ak],
            ['kode_matkul' => 'AK401', 'nama_matkul' => 'Akuntansi Manajemen', 'sks' => 3, 'semester' => 4, 'department_id' => $ak],
            ['kode_matkul' => 'AK402', 'nama_matkul' => 'Auditing', 'sks' => 3, 'semester' => 4, 'department_id' => $ak],
            ['kode_matkul' => 'AK501', 'nama_matkul' => 'Akuntansi Sektor Publik', 'sks' => 3, 'semester' => 5, 'department_id' => $ak],
            ['kode_matkul' => 'AK601', 'nama_matkul' => 'Analisis Laporan Keuangan', 'sks' => 3, 'semester' => 6, 'department_id' => $ak],
            ['kode_matkul' => 'AK701', 'nama_matkul' => 'Kerja Praktik', 'sks' => 2, 'semester' => 7, 'department_id' => $ak],
            ['kode_matkul' => 'AK801', 'nama_matkul' => 'Skripsi', 'sks' => 6, 'semester' => 8, 'department_id' => $ak],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['kode_matkul' => $course['kode_matkul']],
                [
                    'nama_matkul'   => $course['nama_matkul'],
                    'sks'           => $course['sks'],
                    'semester'      => $course['semester'],
                    'department_id' => $course['department_id'],
                ]
            );
        }

        // Hubungkan setiap course ke setiap kelas yang sesuai semester+department (untuk fitur assign matkul)
        $allClasses = Kelas::all();
        $allCourses = Course::all();
        foreach ($allClasses as $kelas) {
            $matchingCourses = $allCourses->where('department_id', $kelas->department_id)
                                           ->where('semester', $kelas->semester);
            foreach ($matchingCourses as $course) {
                DB::table('class_course')->updateOrInsert(
                    ['class_id' => $kelas->id, 'course_id' => $course->id],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        // ==================== 4. ADMIN ====================
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'          => 'Admin Akademik',
                'nim'           => null,
                'phone'         => '08123456789',
                'role'          => 'admin',
                'department_id' => null,
                'password'      => Hash::make('password'),
            ]
        );

        // ==================== 5. DOSEN (acak, 3 per prodi) ====================
        $namaDepan = ['Budi', 'Siti', 'Ahmad', 'Dewi', 'Eko', 'Fitri', 'Gunawan', 'Hani', 'Irfan', 'Joko', 'Kartika', 'Lestari', 'Maya', 'Nanda', 'Oki'];
        $namaBelakang = ['Santoso', 'Rahayu', 'Wijaya', 'Lestari', 'Prasetyo', 'Handayani', 'Hadi', 'Pertiwi', 'Maulana', 'Susilo', 'Permana', 'Utami'];
        $gelar = ['M.Kom', 'M.T', 'M.Si', 'M.Pd', 'M.Ak', 'Ph.D'];

        $deptList = [
            $ti => 'TI', $si => 'SI', $tm => 'TM', $ab => 'AB', $ak => 'AK',
        ];

        $dosenIdsByDept = [];
        $counter = 1;

        foreach ($deptList as $deptId => $prefix) {
            $dosenIdsByDept[$deptId] = [];

            for ($i = 1; $i <= 3; $i++) {
                $nama = $namaDepan[array_rand($namaDepan)] . ' ' . $namaBelakang[array_rand($namaBelakang)] . ', ' . $gelar[array_rand($gelar)];
                $kodeDosen = 'DSN' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $email = 'dosen' . str_pad($counter, 3, '0', STR_PAD_LEFT) . '@kampus.ac.id';

                $dosen = User::updateOrCreate(
                    ['kode_dosen' => $kodeDosen],
                    [
                        'name'          => $nama,
                        'email'         => $email,
                        'phone'         => '0812' . rand(10000000, 99999999),
                        'role'          => 'dosen',
                        'department_id' => $deptId,
                        'password'      => Hash::make('password'),
                    ]
                );

                $dosenIdsByDept[$deptId][] = $dosen->id;
                $counter++;
            }
        }

        // ==================== 6. MAHASISWA (acak, masuk ke kelas & semester) ====================
        $namaMhsDepan = ['Muhammad', 'Siti', 'Ahmad', 'Dewi', 'Eka', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kiki', 'Lina', 'Marco', 'Nina', 'Oscar', 'Putri', 'Qori', 'Rama', 'Sari', 'Toni'];
        $namaMhsBelakang = ['Santoso', 'Ahmad', 'Wijaya', 'Prayogo', 'Kusuma', 'Saputra', 'Lestari', 'Hidayat', 'Permata', 'Nugraha', 'Maharani', 'Pratama'];

        $nimCounter = 1;

        foreach ($allClasses as $kelas) {
            // tiap kelas diisi 5-10 mahasiswa secara acak
            $jumlahMhs = rand(5, 10);

            for ($i = 1; $i <= $jumlahMhs; $i++) {
                $nama = $namaMhsDepan[array_rand($namaMhsDepan)] . ' ' . $namaMhsBelakang[array_rand($namaMhsBelakang)];
                $tahunAngkatan = 2024 - (int) ceil($kelas->semester / 2) + 1;
                $nim = $tahunAngkatan . str_pad($kelas->department_id, 3, '0', STR_PAD_LEFT) . str_pad($nimCounter, 4, '0', STR_PAD_LEFT);
                $email = 'mhs' . str_pad($nimCounter, 4, '0', STR_PAD_LEFT) . '@students.kampus.ac.id';

                User::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'name'          => $nama,
                        'email'         => $email,
                        'phone'         => '0813' . rand(10000000, 99999999),
                        'role'          => 'user',
                        'department_id' => $kelas->department_id,
                        'class_id'      => $kelas->id,
                        'password'      => Hash::make('password'),
                    ]
                );

                $nimCounter++;
            }
        }

        // ==================== 7. JADWAL MENGAJAR (Schedule) ====================
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jamSlots = [
            ['08:00', '09:40'],
            ['10:00', '11:40'],
            ['13:00', '14:40'],
            ['15:00', '16:40'],
        ];
        $ruanganList = ['Gedung A - R.101', 'Gedung A - R.102', 'Gedung B - R.201', 'Gedung B - R.202', 'Gedung C - R.301', 'Lab Komputer 1', 'Lab Komputer 2'];

        $hariIndex = 0;
        $jamIndex = 0;
        $ruanganIndex = 0;

        foreach ($allClasses as $kelas) {
            $matchingCourses = $allCourses->where('department_id', $kelas->department_id)
                                           ->where('semester', $kelas->semester);
            $dosenPool = $dosenIdsByDept[$kelas->department_id] ?? [];

            if (empty($dosenPool) || $matchingCourses->isEmpty()) {
                continue;
            }

            foreach ($matchingCourses as $course) {
                $hari = $hariList[$hariIndex % count($hariList)];
                $jam  = $jamSlots[$jamIndex % count($jamSlots)];
                $ruangan = $ruanganList[$ruanganIndex % count($ruanganList)];
                $dosenId = $dosenPool[array_rand($dosenPool)];

                Schedule::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'class_id'  => $kelas->id,
                    ],
                    [
                        'user_id'      => $dosenId,
                        'hari'         => $hari,
                        'jam_mulai'    => $jam[0],
                        'jam_selesai'  => $jam[1],
                        'mode'         => 'offline',
                        'ruangan'      => $ruangan,
                        'tahun_ajaran' => $tahunAjaran,
                    ]
                );

                // Catat juga relasi dosen-pengajar (untuk halaman "Dosen Pengajar")
                LecturerCourse::updateOrCreate(
                    [
                        'user_id'   => $dosenId,
                        'course_id' => $course->id,
                        'semester'  => $kelas->semester,
                    ],
                    []
                );

                $hariIndex++;
                $jamIndex++;
                $ruanganIndex++;
            }
        }

        // ==================== 8. JADWAL UTS & UAS ====================
        $ruanganUjian = ['Gedung A - R.101', 'Gedung A - R.102', 'Gedung B - R.201', 'Gedung B - R.202', 'Gedung C - R.301'];

        // UTS: minggu ke-8 (misal mulai 14 Okt), UAS: minggu ke-16 (misal mulai 9 Des) - tahun ajaran ganjil 2024
        $tanggalUts = \Carbon\Carbon::parse('2024-10-14');
        $tanggalUas = \Carbon\Carbon::parse('2024-12-09');

        $examJamSlots = [
            ['08:00', '10:00'],
            ['10:30', '12:30'],
            ['13:00', '15:00'],
        ];

        foreach (['uts' => $tanggalUts, 'uas' => $tanggalUas] as $tipe => $tanggalMulai) {
            $hariKe = 0;
            $jamKe = 0;
            $ruanganKe = 0;

            foreach ($allClasses as $kelas) {
                $matchingCourses = $allCourses->where('department_id', $kelas->department_id)
                                               ->where('semester', $kelas->semester);
                $dosenPool = $dosenIdsByDept[$kelas->department_id] ?? [];

                if ($matchingCourses->isEmpty()) {
                    continue;
                }

                foreach ($matchingCourses as $course) {
                    // skip mata kuliah non-teori (kerja praktik, seminar, skripsi) dari ujian tertulis
                    if (str_contains(strtolower($course->nama_matkul), 'kerja praktik')
                        || str_contains(strtolower($course->nama_matkul), 'seminar')
                        || str_contains(strtolower($course->nama_matkul), 'skripsi')) {
                        continue;
                    }

                    $tanggal = $tanggalMulai->copy()->addDays(intdiv($hariKe, 3)); // 3 ujian per hari
                    // lompati weekend
                    while ($tanggal->isWeekend()) {
                        $tanggal->addDay();
                    }

                    $jam = $examJamSlots[$jamKe % count($examJamSlots)];
                    $ruangan = $ruanganUjian[$ruanganKe % count($ruanganUjian)];
                    $pengawasId = !empty($dosenPool) ? $dosenPool[array_rand($dosenPool)] : null;

                    ExamSchedule::updateOrCreate(
                        [
                            'tipe'      => $tipe,
                            'course_id' => $course->id,
                            'class_id'  => $kelas->id,
                            'tahun_ajaran' => $tahunAjaran,
                        ],
                        [
                            'semester'      => $kelas->semester,
                            'department_id' => $kelas->department_id,
                            'tanggal'       => $tanggal->format('Y-m-d'),
                            'jam_mulai'     => $jam[0],
                            'jam_selesai'   => $jam[1],
                            'ruangan'       => $ruangan,
                            'pengawas_id'   => $pengawasId,
                            'tipe_soal'     => 'tulis',
                            'periode'       => 'ganjil',
                            'catatan'       => 'Mahasiswa wajib membawa KTM/KTP dan dilarang membuka catatan kecuali ujian open book.',
                        ]
                    );

                    $hariKe++;
                    $jamKe++;
                    $ruanganKe++;
                }
            }
        }
    }
}
