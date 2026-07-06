<?php

namespace App\Http\Controllers;

use App\Models\LearningModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LearningModuleController extends Controller
{
    // --- KHUSUS DOSEN / ADMIN ---
    
    public function index()
    {
        $modules = LearningModule::with('dosen')->latest()->get();
        return view('dosen.modules.index', compact('modules'));
    }

    public function create()
    {
        return view('dosen.modules.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_stem' => 'required|string',
            'file_path' => 'nullable|file|mimes:pdf,mp4,pptx,docx|max:10240', // max 10MB
        ]);

        $path = null;
        if ($request->hasFile('file_path')) {
            $path = $request->file('file_path')->store('learning_modules', 'public');
        }

        LearningModule::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_stem' => $request->category_stem,
            'file_path' => $path,
            'dosen_id' => Auth::id(),
        ]);

        return redirect()->route('dosen.modules.index')->with('success', 'Modul Belajar berhasil ditambahkan!');
    }

    public function edit(LearningModule $module)
    {
        return view('dosen.modules.edit', compact('module'));
    }

    public function update(Request $request, LearningModule $module)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_stem' => 'required|string',
            'file_path' => 'nullable|file|mimes:pdf,mp4,pptx,docx|max:10240',
        ]);

        $path = $module->file_path;
        if ($request->hasFile('file_path')) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            $path = $request->file('file_path')->store('learning_modules', 'public');
        }

        $module->update([
            'title' => $request->title,
            'description' => $request->description,
            'category_stem' => $request->category_stem,
            'file_path' => $path,
        ]);

        return redirect()->route('dosen.modules.index')->with('success', 'Modul Belajar berhasil diperbarui!');
    }

    public function destroy(LearningModule $module)
    {
        if ($module->file_path) {
            Storage::disk('public')->delete($module->file_path);
        }
        $module->delete();

        return redirect()->route('dosen.modules.index')->with('success', 'Modul Belajar berhasil dihapus!');
    }

    // --- KHUSUS MAHASISWA ---

    public function show(int $id)
    {
        $module = LearningModule::findOrFail($id);
        return view('mahasiswa.stem_exam.module_show', compact('module'));
    }
}
