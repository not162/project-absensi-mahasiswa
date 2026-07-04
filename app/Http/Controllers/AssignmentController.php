<?php
namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassMeeting;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /** Dosen: buat tugas untuk pertemuan tertentu (dengan file soal, opsional) */
    public function store(Request $request)
    {
        $request->validate([
            'meeting_id'  => 'required|exists:class_meetings,id',
            'judul'       => 'required|string|max:255',
            'deskripsi'   => 'nullable|string',
            'deadline'    => 'nullable|date',
            'file_tugas'  => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png',
        ]);

        $meeting = ClassMeeting::findOrFail($request->meeting_id);
        abort_if($meeting->lecturer_id !== Auth::id(), 403);

        $data = $request->only('meeting_id', 'judul', 'deskripsi', 'deadline');

        if ($request->hasFile('file_tugas')) {
            $data['file_tugas']          = $request->file('file_tugas')->store('tugas-soal', 'public');
            $data['file_tugas_original'] = $request->file('file_tugas')->getClientOriginalName();
        }

        Assignment::create($data);

        return redirect()->back()->with('success', 'Tugas berhasil dibuat');
    }

    /** Dosen: lihat detail tugas + siapa sudah/belum kumpul */
    public function show(Assignment $assignment)
    {
        $user = Auth::user();
        if ($user->role === 'dosen') {
            abort_if($assignment->meeting->lecturer_id !== $user->id, 403);
        } elseif ($user->role === 'user') {
            abort_if($assignment->meeting->schedule->class_id !== $user->class_id, 403);
        } else {
            abort_if($user->role !== 'admin', 403);
        }

        $meeting   = $assignment->meeting->load('schedule.kelas.mahasiswa');
        $mahasiswa = $meeting->schedule->kelas->mahasiswa ?? collect();

        $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->with('student')
            ->get()
            ->keyBy('student_id');

        return view('dosen.tugas.show', compact('assignment', 'meeting', 'mahasiswa', 'submissions'));
    }

    /** Dosen: beri nilai pada submission */
    public function nilaiSubmission(Request $request, AssignmentSubmission $submission)
    {
        abort_if($submission->assignment->meeting->lecturer_id !== Auth::id(), 403);

        $request->validate([
            'nilai'    => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'nilai'    => $request->nilai,
            'feedback' => $request->feedback,
        ]);

        return redirect()->back()->with('success', 'Nilai berhasil disimpan');
    }

    /** Dosen: hapus tugas */
    public function destroy(Assignment $assignment)
    {
        abort_if($assignment->meeting->lecturer_id !== Auth::id(), 403);

        if ($assignment->file_tugas) {
            Storage::disk('public')->delete($assignment->file_tugas);
        }
        foreach ($assignment->submissions as $submission) {
            if ($submission->file_tugas) {
                Storage::disk('public')->delete($submission->file_tugas);
            }
        }

        $assignment->delete();
        return redirect()->back()->with('success', 'Tugas berhasil dihapus');
    }

    /** Mahasiswa: kumpul tugas (upload file jawaban) */
    public function submit(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'file_tugas'    => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png',
            'catatan'       => 'nullable|string|max:500',
        ]);

        $assignment = Assignment::findOrFail($request->assignment_id);
        abort_if($assignment->isDeadlinePassed(), 403, 'Deadline pengumpulan tugas sudah lewat.');

        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', Auth::id())
            ->first();

        // Hapus file lama kalau mengumpulkan ulang
        if ($existing && $existing->file_tugas) {
            Storage::disk('public')->delete($existing->file_tugas);
        }

        $path         = $request->file('file_tugas')->store('tugas-jawaban', 'public');
        $originalName = $request->file('file_tugas')->getClientOriginalName();

        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => Auth::id()],
            [
                'link_tugas'          => '',
                'file_tugas'          => $path,
                'file_tugas_original' => $originalName,
                'catatan'             => $request->catatan,
                'submitted_at'        => now(),
            ]
        );

        return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan');
    }

}