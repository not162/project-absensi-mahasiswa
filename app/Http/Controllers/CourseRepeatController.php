<?php

namespace App\Http\Controllers;

use App\Models\CourseRepeat;
use App\Models\Grade;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseRepeatController extends Controller
{
    /** Admin: lihat semua pengajuan */
    public function index(Request $request)
    {
        $status = $request->get('status');

        $query = CourseRepeat::with(['student', 'course', 'grade']);
        if ($status) {
            $query->where('status', $status);
        }

        $repeats = $query->orderBy('created_at', 'desc')->get();

        return view('admin.repeats.index', compact('repeats', 'status'));
    }

    /** Mahasiswa: lihat pengajuan sendiri */
    public function myRepeats()
    {
        $user = Auth::user();

        $repeats = CourseRepeat::with(['course', 'grade'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Mata kuliah yang nilainya tidak lulus (D/E) dan belum diajukan ulang
        $failedGrades = Grade::with('course')
            ->where('user_id', $user->id)
            ->whereIn('grade_huruf', ['D', 'E'])
            ->get()
            ->filter(function ($grade) use ($repeats) {
                return !$repeats->contains('course_id', $grade->course_id);
            });

        return view('mahasiswa.repeats', compact('repeats', 'failedGrades'));
    }

    /** Mahasiswa: ajukan pengulangan */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'grade_id'  => 'nullable|exists:grades,id',
            'alasan'    => 'required|string|max:500',
        ]);

        CourseRepeat::create([
            'user_id'   => Auth::id(),
            'course_id' => $request->course_id,
            'grade_id'  => $request->grade_id,
            'alasan'    => $request->alasan,
            'status'    => 'pending',
        ]);

        return redirect()->route('repeats.my')->with('success', 'Pengajuan pengulangan berhasil dikirim');
    }

    /** Admin: setujui pengajuan */
    public function approve(Request $request, CourseRepeat $repeat)
    {
        $request->validate([
            'tanggal_ujian_ulang' => 'required|date',
            'bukti_foto'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'status'               => 'disetujui',
            'tanggal_ujian_ulang'  => $request->tanggal_ujian_ulang,
            'approved_by'          => Auth::id(),
            'approved_at'          => now(),
        ];

        if ($request->hasFile('bukti_foto')) {
            if ($repeat->bukti_foto) {
                Storage::disk('public')->delete($repeat->bukti_foto);
            }
            $data['bukti_foto'] = $request->file('bukti_foto')->store('bukti-pengulangan', 'public');
        }

        $repeat->update($data);

        return redirect()->route('repeats.index')->with('success', 'Pengajuan disetujui');
    }

    /** Admin: tolak pengajuan */
    public function reject(Request $request, CourseRepeat $repeat)
    {
        $repeat->update([
            'status'        => 'ditolak',
            'catatan_admin' => $request->catatan_admin,
            'approved_by'   => Auth::id(),
            'approved_at'   => now(),
        ]);

        return redirect()->route('repeats.index')->with('success', 'Pengajuan ditolak');
    }

    /** Mahasiswa: hapus pengajuan yang masih pending */
    public function destroy(CourseRepeat $repeat)
    {
        if ($repeat->user_id !== Auth::id() || !$repeat->isPending()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus pengajuan ini');
        }

        $repeat->delete();

        return redirect()->route('repeats.my')->with('success', 'Pengajuan dibatalkan');
    }
}
