<?php

namespace App\Http\Controllers;

use App\Models\ExamSchedule;
use App\Models\Course;
use App\Models\Kelas;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class ExamScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tipe = $request->get('tipe', 'uts');
        $tahunAjaran = $request->get('tahun_ajaran', '2024/2025');

        $query = ExamSchedule::with(['course', 'kelas', 'department', 'pengawas'])
            ->where('tipe', $tipe)
            ->where('tahun_ajaran', $tahunAjaran);

        if ($user->role === 'admin') {
            $semester = $request->get('semester');
            $departmentId = $request->get('department_id');
            $periode = $request->get('periode');

            if ($semester) $query->where('semester', $semester);
            if ($departmentId) $query->where('department_id', $departmentId);
            if ($periode) $query->where('periode', $periode);

            $exams = $query->orderBy('tanggal')->orderBy('jam_mulai')->get();
            $departments = Department::all();
            $examsByDate = $exams->groupBy(fn($e) => $e->tanggal->format('Y-m-d'));

            return view('admin.exam.index', compact(
                'exams', 'examsByDate', 'tipe', 'semester',
                'departments', 'periode', 'tahunAjaran'
            ));
        } elseif ($user->role === 'dosen') {
            // Dosen only sees exams they supervise
            return redirect()->route('exam.mySupervisions', ['tipe' => $tipe]);
        } else {
            // Mahasiswa only sees exams for their class
            $query->where('class_id', $user->class_id);
            $exams = $query->orderBy('tanggal')->orderBy('jam_mulai')->get();
            $examsByDate = $exams->groupBy(fn($e) => $e->tanggal->format('Y-m-d'));

            return view('mahasiswa.exam.index', compact('exams', 'examsByDate', 'tipe', 'tahunAjaran'));
        }
    }

    public function create(Request $request)
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $tipe        = $request->get('tipe', 'uts');
        $semester    = $request->get('semester', 1);
        $departments = Department::all();
        $courses     = Course::when($semester, fn($q) => $q->where('semester', $semester))->get();
        $kelasList   = Kelas::when($semester, fn($q) => $q->where('semester', $semester))
                            ->with('department')->get();
        $dosenList   = User::where('role', 'dosen')->orderBy('name')->get();

        return view('admin.exam.create', compact(
            'tipe', 'semester', 'departments', 'courses', 'kelasList', 'dosenList'
        ));
    }

    public function store(Request $request)
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $request->validate([
            'tipe'          => 'required|in:uts,uas',
            'course_id'     => 'required|exists:courses,id',
            'class_id'      => 'required|exists:classes,id',
            'semester'      => 'required|integer|min:1|max:8',
            'department_id' => 'required|exists:departments,id',
            'tanggal'       => 'required|date',
            'jam_mulai'     => 'required',
            'jam_selesai'   => 'required',
            'ruangan'       => 'required|string|max:100',
            'pengawas_id'   => 'nullable|exists:users,id',
            'tipe_soal'     => 'required|in:tulis,online,take-home',
            'tahun_ajaran'  => 'required|string',
            'periode'       => 'required|in:ganjil,genap',
            'catatan'       => 'nullable|string',
        ]);

        ExamSchedule::create($request->all());

        return redirect()->route('exam.index', ['tipe' => $request->tipe])
            ->with('success', 'Jadwal ' . strtoupper($request->tipe) . ' berhasil ditambahkan');
    }

    public function edit(ExamSchedule $exam)
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $departments = Department::all();
        $courses     = Course::where('semester', $exam->semester)->get();
        $kelasList   = Kelas::where('semester', $exam->semester)->with('department')->get();
        $dosenList   = User::where('role', 'dosen')->orderBy('name')->get();

        return view('admin.exam.edit', compact(
            'exam', 'departments', 'courses', 'kelasList', 'dosenList'
        ));
    }

    public function update(Request $request, ExamSchedule $exam)
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $request->validate([
            'tipe'          => 'required|in:uts,uas',
            'course_id'     => 'required|exists:courses,id',
            'class_id'      => 'required|exists:classes,id',
            'semester'      => 'required|integer|min:1|max:8',
            'department_id' => 'required|exists:departments,id',
            'tanggal'       => 'required|date',
            'jam_mulai'     => 'required',
            'jam_selesai'   => 'required',
            'ruangan'       => 'required|string|max:100',
            'pengawas_id'   => 'nullable|exists:users,id',
            'tipe_soal'     => 'required|in:tulis,online,take-home',
            'tahun_ajaran'  => 'required|string',
            'periode'       => 'required|in:ganjil,genap',
            'catatan'       => 'nullable|string',
        ]);

        $exam->update($request->all());

        return redirect()->route('exam.index', ['tipe' => $exam->tipe])
            ->with('success', 'Jadwal berhasil diupdate');
    }

    public function destroy(ExamSchedule $exam)
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $tipe = $exam->tipe;
        $exam->delete();

        return redirect()->route('exam.index', ['tipe' => $tipe])
            ->with('success', 'Jadwal berhasil dihapus');
    }

    /** Dosen: jadwal mengawas ujian miliknya, lengkap dengan daftar mahasiswa yang diawas */
    public function mySupervisions(Request $request)
    {
        $user = $request->user();

        $exams = ExamSchedule::with(['course', 'kelas.mahasiswa', 'department'])
            ->where('pengawas_id', $user->id)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $examsByDate = $exams->groupBy(fn($e) => $e->tanggal->format('Y-m-d'));

        return view('dosen.supervisions.index', compact('exams', 'examsByDate'));
    }
}
