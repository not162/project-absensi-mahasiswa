<?php

namespace App\Http\Controllers;

use App\Models\ExamReplacement;
use App\Models\ExamSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExamReplacementController extends Controller
{
    /** Mahasiswa: Halaman pengajuan & riwayat */
    public function index()
    {
        $user = Auth::user();
        
        // Pengajuan milik mahasiswa itu sendiri
        $replacements = ExamReplacement::with(['examSchedule.course'])
            ->where('student_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Ambil jadwal ujian yang tersedia untuk kelas mahasiswa tersebut
        $examSchedules = ExamSchedule::with(['course', 'kelas'])
            ->where('class_id', $user->class_id)
            ->get();

        return view('mahasiswa.exam_replacements', compact('replacements', 'examSchedules'));
    }

    /** Mahasiswa: Kirim form pengajuan */
    public function store(Request $request)
    {
        $request->validate([
            'exam_schedule_id' => 'required|exists:exam_schedules,id',
            'alasan'           => 'required|string|max:500',
            'bukti_foto'       => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('bukti_foto')) {
            $path = $request->file('bukti_foto')->store('bukti-ujian-pengganti', 'public');
        }

        ExamReplacement::create([
            'student_id'       => Auth::id(),
            'exam_schedule_id' => $request->exam_schedule_id,
            'alasan'           => $request->alasan,
            'bukti_foto'       => $path,
            'status'           => 'pending',
        ]);

        return redirect()->route('exam.replacement.index')->with('success', 'Pengajuan ujian pengganti berhasil dikirim.');
    }

    /** Admin & Dosen: Daftar pengajuan */
    public function adminIndex(Request $request)
    {
        $status = $request->get('status');

        $query = ExamReplacement::with(['student.kelas', 'examSchedule.course']);
        if ($status) {
            $query->where('status', $status);
        }

        $replacements = $query->orderBy('created_at', 'desc')->get();

        return view('admin.exam_replacements.index', compact('replacements', 'status'));
    }

    /** Admin & Dosen: Setujui pengajuan */
    public function approve(ExamReplacement $replacement)
    {
        $replacement->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Pengajuan ujian pengganti berhasil disetujui.');
    }

    /** Admin & Dosen: Tolak pengajuan */
    public function reject(ExamReplacement $replacement)
    {
        $replacement->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'Pengajuan ujian pengganti berhasil ditolak.');
    }
}
