<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Kelas;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\User;
use App\Models\LecturerCourse;

class SaturdayScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $classes = Kelas::all();
        $dosens = User::where('role', 'dosen')->get();

        if ($dosens->isEmpty()) {
            return;
        }

        foreach ($classes as $kelas) {
            // Find a course for this department and semester
            $course = Course::where('department_id', $kelas->department_id)
                ->where('semester', $kelas->semester)
                ->first();

            if (!$course) {
                // Create a fallback course
                $deptCode = strtoupper(substr($kelas->department->name ?? 'GEN', 0, 2));
                $course = Course::create([
                    'kode_matkul' => $deptCode . 'S' . $kelas->semester . '99',
                    'nama_matkul' => 'Kajian Mandiri Sabtu ' . ($kelas->department->name ?? ''),
                    'sks' => 3,
                    'semester' => $kelas->semester,
                    'department_id' => $kelas->department_id,
                ]);
            }

            // Assign a random dosen
            $dosen = $dosens->random();
            $dosenId = $dosen->id;

            Schedule::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'class_id'  => $kelas->id,
                    'hari'      => 'Sabtu',
                ],
                [
                    'user_id'      => $dosenId,
                    'jam_mulai'    => '08:20:00',
                    'jam_selesai'  => '11:50:00',
                    'mode'         => 'offline',
                    'ruangan'      => 'Lab Komputer ' . ($kelas->nomor_kelas ?? 1),
                    'tahun_ajaran' => $kelas->tahun_ajaran ?? '2024/2025',
                ]
            );

            // Relate dosen-pengajar
            LecturerCourse::updateOrCreate(
                [
                    'user_id'   => $dosenId,
                    'course_id' => $course->id,
                    'semester'  => $kelas->semester,
                ],
                []
            );
        }
    }
}
