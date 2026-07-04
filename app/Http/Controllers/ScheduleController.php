<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /** Semua jadwal (admin) — dikelompokkan per prodi, semester, kelas */
    public function index()
    {
        $schedules = Schedule::with(['course.department', 'lecturer', 'kelas.department'])
            ->join('classes', 'schedules.class_id', '=', 'classes.id')
            ->join('departments', 'classes.department_id', '=', 'departments.id')
            ->orderBy('departments.name')
            ->orderBy('classes.semester')
            ->orderBy('classes.nomor_kelas')
            ->orderByRaw("FIELD(schedules.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('schedules.jam_mulai')
            ->select('schedules.*')
            ->get();

        return view('admin.jadwal.index', compact('schedules'));
    }

    /** Jadwal milik dosen tertentu */
    public function byDosen(User $dosen)
    {
        abort_if($dosen->role !== 'dosen', 404);

        $hariMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];
        $hariIni = $hariMap[now()->format('l')] ?? now()->format('l');

        $schedules = Schedule::with(['course.department', 'kelas.department', 'kelas.mahasiswa'])
            ->where('user_id', $dosen->id)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        // Pertemuan yang sudah diabsen hari ini
        $meetingsDone = \App\Models\ClassMeeting::where('lecturer_id', $dosen->id)
            ->whereDate('tanggal', today())
            ->pluck('schedule_id');

        return view('admin.jadwal.by_dosen', compact('schedules', 'dosen', 'hariIni', 'meetingsDone'));
    }

    /** Jadwal mahasiswa (difilter by departemen & semester) */
    public function mahasiswaJadwal()
    {
        // Halaman jadwal mahasiswa yang lengkap (absen + tugas) ada di SelfAttendanceController.
        // Redirect ke sana supaya tidak ada 2 sumber data berbeda untuk view yang sama.
        return redirect()->route('mahasiswa.jadwal');
    }

    /** Form tambah jadwal */
    public function create()
    {
        $courses  = Course::with('department')->get();
        $dosens   = User::where('role', 'dosen')->get();
        return view('admin.jadwal.create', compact('courses', 'dosens'));
    }

    /** Simpan jadwal baru */
    public function store(Request $request)
    {
        $request->validate([
            'course_id'    => 'required|exists:courses,id',
            'user_id'      => 'required|exists:users,id',
            'hari'         => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'    => 'required',
            'jam_selesai'  => 'required|after:jam_mulai',
            'mode'         => 'required|in:offline,online',
            'ruangan'      => 'required_if:mode,offline|nullable|string|max:50',
            'link_online'  => 'required_if:mode,online|nullable|url',
            'kode_online'  => 'nullable|string|max:100',
            'tahun_ajaran' => 'required|string',
        ]);

        Schedule::create($request->all());

        return redirect()->route('schedules.index')
                         ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /** Form edit jadwal */
    public function edit(Schedule $schedule)
    {
        $courses = Course::with('department')->get();
        $dosens  = User::where('role', 'dosen')->get();
        return view('admin.jadwal.edit', compact('schedule', 'courses', 'dosens'));
    }

    /** Update jadwal */
    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'course_id'    => 'required|exists:courses,id',
            'user_id'      => 'required|exists:users,id',
            'hari'         => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'    => 'required',
            'jam_selesai'  => 'required|after:jam_mulai',
            'mode'         => 'required|in:offline,online',
            'ruangan'      => 'required_if:mode,offline|nullable|string|max:50',
            'link_online'  => 'required_if:mode,online|nullable|url',
            'kode_online'  => 'nullable|string|max:100',
            'tahun_ajaran' => 'required|string',
        ]);

        $schedule->update($request->all());

        return redirect()->route('schedules.index')
                         ->with('success', 'Jadwal berhasil diperbarui.');
    }

    /** Hapus jadwal */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('schedules.index')
                         ->with('success', 'Jadwal berhasil dihapus.');
    }
}