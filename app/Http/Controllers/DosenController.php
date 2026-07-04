<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Models\LecturerCourse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = User::where('role', 'dosen')->with([
            'department',
            'lecturerCourses.course',
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('kode_dosen', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $dosen = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.dosen.index', compact('dosen', 'search'));
    }

    public function create()
    {
        $departments = Department::all();

        $courses = Course::where('department_id', request()->input('department_id'))
            ->where('semester', request()->input('semester'))
            ->get();

        // fallback: kalau belum pilih semester/departemen, tampilkan kosong agar user pilih dulu
        if (!request()->filled('semester') || !request()->filled('department_id')) {
            $courses = collect();
        }

        return view('admin.dosen.create', compact('departments', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'kode_dosen' => 'required|string|max:255|unique:users,kode_dosen',
            'phone' => 'nullable|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'semester' => 'required|integer|min:1|max:8',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'kode_dosen' => $validated['kode_dosen'],
            'phone' => $validated['phone'] ?? null,
            'role' => 'dosen',
            'department_id' => $validated['department_id'],
            'password' => Hash::make($validated['password']),
        ]);

        $courseIds = $request->input('course_ids', []);

        foreach ($courseIds as $courseId) {
            LecturerCourse::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'course_id' => $courseId,
                    'semester' => $validated['semester'],
                ],
                []
            );
        }

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function show(User $dosen)
    {
        // ambil relasi schedules agar view tidak error
        $dosen->load('schedules.course');
        return view('admin.dosen.show', compact('dosen'));
    }

    public function edit(User $dosen)
    {
        $departments = Department::all();

        return view('admin.dosen.edit', compact('dosen', 'departments'));
    }

    public function update(Request $request, User $dosen)
    {
        $validated = $request->validate([
            'kode_dosen' => 'required|string|max:255|unique:users,kode_dosen,' . $dosen->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $dosen->id,
            'phone' => 'nullable|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'password' => 'nullable|string|min:6|confirmed',
            'password_confirmation' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'kode_dosen' => $validated['kode_dosen'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $dosen->update($updateData);

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(User $dosen)
    {
        // pastikan hanya bisa hapus dosen
        if ($dosen->role !== 'dosen') {
            return redirect()->route('dosen.index')->with('error', 'Data tidak valid.');
        }

        // Relasi schedules punya FK cascade, tetapi kita hapus aman relasi lecturer_courses juga.
        LecturerCourse::where('user_id', $dosen->id)->delete();

        $dosen->delete();

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil dihapus.');
    }

    // Endpoint untuk load mata kuliah berdasarkan departemen + semester
    public function coursesByDepartmentAndSemester(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'semester' => 'required|integer|min:1|max:8',
        ]);

        $courses = Course::where('department_id', $request->department_id)
            ->where('semester', (int) $request->semester)
            ->orderBy('department_id', 'asc')
            ->orderBy('semester', 'asc')
            ->get(['id', 'kode_matkul', 'nama_matkul']);

        return response()->json($courses);
    }
}



