<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Course;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    /** Admin/Dosen: pilih kelas & matkul untuk input nilai */
    public function index(Request $request)
    {
        abort_if(!in_array(Auth::user()->role, ['admin', 'dosen']), 403);

        $classId  = $request->get('class_id');
        $courseId = $request->get('course_id');

        $kelasList = Kelas::with('department')->orderBy('semester')->orderBy('nomor_kelas')->get();
        $courses   = $classId ? Kelas::find($classId)?->courses ?? collect() : collect();

        $grades = collect();
        if ($classId && $courseId) {
            $mahasiswa = User::where('class_id', $classId)->where('role', 'user')->orderBy('name')->get();

            foreach ($mahasiswa as $mhs) {
                $grade = Grade::firstOrNew([
                    'user_id'   => $mhs->id,
                    'course_id' => $courseId,
                ], [
                    'class_id'     => $classId,
                    'tahun_ajaran' => '2024/2025',
                ]);
                $grade->student = $mhs;
                $grades->push($grade);
            }
        }

        return view('admin.grades.index', compact('kelasList', 'courses', 'grades', 'classId', 'courseId'));
    }

    /** Simpan banyak nilai sekaligus (per kelas + matkul) */
    public function store(Request $request)
    {
        abort_if(!in_array(Auth::user()->role, ['admin', 'dosen']), 403);

        $request->validate([
            'class_id'  => 'required|exists:classes,id',
            'course_id' => 'required|exists:courses,id',
            'nilai'     => 'required|array',
        ]);

        foreach ($request->nilai as $userId => $row) {
            $tugas = $row['tugas'] ?? null;
            $uts   = $row['uts'] ?? null;
            $uas   = $row['uas'] ?? null;

            $grade = Grade::updateOrCreate(
                [
                    'user_id'      => $userId,
                    'course_id'    => $request->course_id,
                    'tahun_ajaran' => '2024/2025',
                ],
                [
                    'class_id'    => $request->class_id,
                    'nilai_tugas' => $tugas,
                    'nilai_uts'   => $uts,
                    'nilai_uas'   => $uas,
                ]
            );

            $grade->hitungNilaiAkhir();
            $grade->save();
        }

        return redirect()->route('grades.index', [
            'class_id'  => $request->class_id,
            'course_id' => $request->course_id,
        ])->with('success', 'Nilai berhasil disimpan');
    }

    /** Mahasiswa: lihat nilai sendiri */
    public function myGrades()
    {
        $user = Auth::user();

        $grades = Grade::with(['course'])
            ->where('user_id', $user->id)
            ->get()
            ->sortBy(fn($g) => $g->course->semester ?? 0);

        return view('mahasiswa.grades', compact('grades'));
    }

    /** Mahasiswa: cetak KHS PDF */
    public function exportPdf()
    {
        $user = Auth::user();

        $grades = Grade::with(['course'])
            ->where('user_id', $user->id)
            ->get()
            ->sortBy(fn($g) => $g->course->semester ?? 0);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.khs', compact('grades', 'user'));
        
        return $pdf->download('KHS_' . ($user->nim ?? 'NIM') . '_' . str_replace(' ', '_', $user->name) . '.pdf');
    }
}
