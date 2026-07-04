<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Department;

class AkademikService
{
    /**
     * Get data for a specific semester
     */
    public function getSemesterData($semester)
    {
        $departments = Department::with(['courses' => function($q) use ($semester) {
            $q->where('semester', $semester);
        }])->get();

        $kelasList = Kelas::where('semester', $semester)
            ->with(['department', 'courses.department'])
            ->orderBy('department_id')
            ->orderBy('nomor_kelas')
            ->get()
            ->groupBy('department_id');

        return [
            'departments' => $departments,
            'kelasList' => $kelasList
        ];
    }

    /**
     * Store a new class
     */
    public function storeKelas(array $data, $semester)
    {
        return Kelas::create([
            'nomor_kelas'   => $data['nomor_kelas'],
            'semester'      => $semester,
            'department_id' => $data['department_id'],
            'tahun_ajaran'  => $data['tahun_ajaran'],
        ]);
    }

    /**
     * Assign courses to a class
     */
    public function assignMatkul(Kelas $kelas, array $courseIds)
    {
        return $kelas->courses()->sync($courseIds);
    }
    
    /**
     * Delete a class
     */
    public function deleteKelas(Kelas $kelas)
    {
        return $kelas->delete();
    }
}
