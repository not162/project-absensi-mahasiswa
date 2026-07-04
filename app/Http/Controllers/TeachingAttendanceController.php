<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\TeachingAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeachingAttendanceController extends Controller
{
    private function hariIni(): string
    {
        $hariMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];
        return $hariMap[Carbon::today()->format('l')] ?? Carbon::today()->format('l');
    }

    /** Dosen: data mengajar hari ini (jadwal + kelas + mahasiswa) + absen */
    public function index()
    {
        $user  = Auth::user();
        $hari  = $this->hariIni();
        $today = Carbon::today();

        $schedules = Schedule::with(['course.department', 'kelas.mahasiswa'])
            ->where('user_id', $user->id)
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get();

        // Ambil absen mengajar yang sudah diisi hari ini
        $attendances = TeachingAttendance::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->get()
            ->keyBy('schedule_id');

        return view('dosen.teaching-attendance.index', compact('schedules', 'attendances', 'hari', 'today'));
    }

    /** Simpan absen mengajar untuk 1 jadwal hari ini */
    public function store(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'status'      => 'required|in:hadir,izin,sakit,tidak_hadir',
            'materi'      => 'nullable|string|max:255',
            'catatan'     => 'nullable|string',
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);
        abort_if($schedule->user_id !== Auth::id(), 403);

        TeachingAttendance::updateOrCreate(
            [
                'schedule_id' => $request->schedule_id,
                'tanggal'     => Carbon::today(),
            ],
            [
                'user_id' => Auth::id(),
                'status'  => $request->status,
                'materi'  => $request->materi,
                'catatan' => $request->catatan,
            ]
        );

        return redirect()->route('teaching-attendance.index')->with('success', 'Absen mengajar berhasil disimpan');
    }

    /** Admin: isi/perbarui absen mengajar dosen secara manual (jam & tanggal) */
    public function adminStore(Request $request)
    {
        abort_if(Auth::user()->role !== 'admin', 403);

        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'tanggal'     => 'required|date',
            'jam'         => 'nullable|date_format:H:i',
            'status'      => 'required|in:hadir,izin,sakit,tidak_hadir',
            'catatan'     => 'nullable|string',
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);

        TeachingAttendance::updateOrCreate(
            [
                'schedule_id' => $schedule->id,
                'tanggal'     => $request->tanggal,
            ],
            [
                'user_id' => $schedule->user_id,
                'jam'     => $request->jam,
                'status'  => $request->status,
                'catatan' => $request->catatan,
            ]
        );

        return redirect()->back()->with('success', 'Absen mengajar dosen berhasil disimpan');
    }

    /** Admin: riwayat absen mengajar semua dosen */
    public function history(Request $request)
    {
        $query = TeachingAttendance::with(['dosen', 'schedule.course', 'schedule.kelas']);

        if ($request->filled('dosen_id')) {
            $query->where('user_id', $request->dosen_id);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $attendances = $query->orderBy('tanggal', 'desc')->paginate(20);
        $dosenList   = \App\Models\User::where('role', 'dosen')->orderBy('name')->get();

        return view('admin.teaching-attendance.index', compact('attendances', 'dosenList'));
    }
}
