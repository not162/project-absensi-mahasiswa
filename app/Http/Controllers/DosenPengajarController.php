<?php

namespace App\Http\Controllers;

use App\Models\LecturerCourse;
use Illuminate\Support\Facades\Auth;

class DosenPengajarController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_if(!$user || $user->role !== 'admin', 403);

        $lecturerCourses = LecturerCourse::with([
            'lecturer:id,name,kode_dosen,department_id',
            'lecturer.department:id,name',
            'course:id,nama_matkul,kode_matkul,semester,department_id',
        ])
            ->join('users', 'lecturer_courses.user_id', '=', 'users.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->orderBy('departments.name')
            ->orderBy('lecturer_courses.semester')
            ->orderBy('users.name')
            ->select('lecturer_courses.*')
            ->get();

        return view('admin.dosenpengajar.index', [
            'lecturerCourses' => $lecturerCourses,
        ]);
    }

    public function show(LecturerCourse $dosenPengajar)
    {
        $dosenPengajar->load(['lecturer.department', 'course']);
        return view('admin.dosenpengajar.show', ['lecturerCourse' => $dosenPengajar]);
    }

    public function edit(LecturerCourse $dosenPengajar)
    {
        $dosenPengajar->load(['lecturer.department', 'course']);

        // filter course sesuai prodi dosen pengajar
        // Jangan batasi semester, karena tabel lecturer_courses semester adalah semester diajar (bukan selalu semester course)
        $courses = \App\Models\Course::where('department_id', $dosenPengajar->lecturer->department_id)
            ->get(['id', 'nama_matkul', 'kode_matkul', 'semester', 'department_id']);


        return view('admin.dosenpengajar.edit', [
            'lecturerCourse' => $dosenPengajar,
            'courses' => $courses,
        ]);
    }

    public function update(\Illuminate\Http\Request $request, LecturerCourse $dosenPengajar)
    {
        $validated = $request->validate([
            'semester' => 'required|integer|min:1|max:8',
            'course_id' => 'required|exists:courses,id',
        ]);

        $dosenPengajar->update([
            'semester' => $validated['semester'],
            'course_id' => $validated['course_id'],
        ]);

        return redirect()->route('dosen-pengajar.show', $dosenPengajar)
            ->with('success', 'Dosen pengajar berhasil diperbarui.');
    }

    public function destroy(LecturerCourse $dosenPengajar)
    {
        $dosenPengajar->delete();

        return redirect()->route('dosenpengajar.index')
            ->with('success', 'Dosen pengajar berhasil dihapus.');
    }
}