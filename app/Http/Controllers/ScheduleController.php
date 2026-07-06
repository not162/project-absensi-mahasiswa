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
        $user = auth()->user();
        
        $query = Schedule::with(['course.department', 'lecturer', 'kelas.department'])
            ->join('classes', 'schedules.class_id', '=', 'classes.id')
            ->join('departments', 'classes.department_id', '=', 'departments.id');

        if ($user->role === 'dosen') {
            $query->where('schedules.user_id', $user->id);
        }

        $schedules = $query->orderBy('departments.name')
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
        abort_if(auth()->user()->role !== 'admin', 403);
        $courses  = Course::with('department')->get();
        $dosens   = User::where('role', 'dosen')->get();
        $classes  = \App\Models\Kelas::orderBy('semester')->orderBy('nomor_kelas')->get();
        return view('admin.jadwal.create', compact('courses', 'dosens', 'classes'));
    }

    /** Simpan jadwal baru */
    public function store(Request $request)
    {
        abort_if(auth()->user()->role !== 'admin', 403);
        $request->validate([
            'course_id'        => 'required|exists:courses,id',
            'user_id'          => 'required|exists:users,id',
            'class_id'         => 'required|exists:classes,id',
            'hari'             => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'        => 'required',
            'jam_selesai'      => 'required|after:jam_mulai',
            'mode'             => 'required|in:offline,online',
            'ruangan'          => 'required_if:mode,offline|nullable|string|max:50',
            'link_online'      => 'required_if:mode,online|nullable|url',
            'kode_online'      => 'nullable|string|max:100',
            'tahun_ajaran'     => 'required|string',
            'is_replacement'   => 'nullable|boolean',
            'replacement_date' => 'required_if:is_replacement,1|nullable|date',
        ]);

        $data = $request->all();
        $data['is_replacement'] = $request->has('is_replacement') ? 1 : 0;

        Schedule::create($data);

        return redirect()->route('schedules.index')
                         ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /** Form edit jadwal */
    public function edit(Schedule $schedule)
    {
        $user = auth()->user();
        
        // Dosen can only edit their own schedules
        if ($user->role === 'dosen') {
            abort_if($schedule->user_id !== $user->id, 403, 'Anda hanya dapat mengedit jadwal milik Anda sendiri.');
        } else {
            abort_if($user->role !== 'admin', 403);
        }

        $courses = Course::with('department')->get();
        $dosens  = User::where('role', 'dosen')->get();
        $classes = \App\Models\Kelas::orderBy('semester')->orderBy('nomor_kelas')->get();
        $isDosen = $user->role === 'dosen';
        return view('admin.jadwal.edit', compact('schedule', 'courses', 'dosens', 'classes', 'isDosen'));
    }

    /** Update jadwal */
    public function update(Request $request, Schedule $schedule)
    {
        $user = auth()->user();

        if ($user->role === 'dosen') {
            abort_if($schedule->user_id !== $user->id, 403, 'Anda hanya dapat mengubah jadwal milik Anda sendiri.');

            // Dosen hanya bisa mengubah: hari, jam, mode, ruangan/link
            $request->validate([
                'hari'             => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
                'jam_mulai'        => 'required',
                'jam_selesai'      => 'required|after:jam_mulai',
                'mode'             => 'required|in:offline,online',
                'ruangan'          => 'required_if:mode,offline|nullable|string|max:50',
                'link_online'      => 'required_if:mode,online|nullable|url',
                'kode_online'      => 'nullable|string|max:100',
            ]);

            $schedule->update($request->only([
                'hari', 'jam_mulai', 'jam_selesai', 'mode', 'ruangan', 'link_online', 'kode_online'
            ]));

            return redirect()->route('schedules.byDosen', $user)
                             ->with('success', 'Jadwal berhasil diperbarui.');
        }

        // Admin: full update
        abort_if($user->role !== 'admin', 403);
        $request->validate([
            'course_id'        => 'required|exists:courses,id',
            'user_id'          => 'required|exists:users,id',
            'class_id'         => 'required|exists:classes,id',
            'hari'             => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'        => 'required',
            'jam_selesai'      => 'required|after:jam_mulai',
            'mode'             => 'required|in:offline,online',
            'ruangan'          => 'required_if:mode,offline|nullable|string|max:50',
            'link_online'      => 'required_if:mode,online|nullable|url',
            'kode_online'      => 'nullable|string|max:100',
            'tahun_ajaran'     => 'required|string',
            'is_replacement'   => 'nullable|boolean',
            'replacement_date' => 'required_if:is_replacement,1|nullable|date',
        ]);

        $data = $request->all();
        $data['is_replacement'] = $request->has('is_replacement') ? 1 : 0;

        $schedule->update($data);

        return redirect()->route('schedules.index')
                         ->with('success', 'Jadwal berhasil diperbarui.');
    }

    /** Hapus jadwal */
    public function destroy(Schedule $schedule)
    {
        abort_if(auth()->user()->role !== 'admin', 403);
        $schedule->delete();
        return redirect()->route('schedules.index')
                         ->with('success', 'Jadwal berhasil dihapus.');
    }
}