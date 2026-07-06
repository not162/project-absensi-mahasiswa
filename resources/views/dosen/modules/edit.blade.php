@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Modul Belajar</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('dosen.modules.update', $module->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Modul</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $module->title) }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Kategori / Topik (Node Dijkstra)</label>
                            <select name="category_stem" class="form-select @error('category_stem') is-invalid @enderror" required>
                                <option value="S" @selected($module->category_stem == 'S')>S (Science/Sains Umum)</option>
                                <option value="T" @selected($module->category_stem == 'T')>T (Technology/Teknologi Umum)</option>
                                <option value="E" @selected($module->category_stem == 'E')>E (Engineering/Teknik Umum)</option>
                                <option value="M" @selected($module->category_stem == 'M')>M (Math/Matematika Umum)</option>
                                <option disabled>── Spesifik (Dijkstra Nodes) ──</option>
                                <option value="Dasar Logika" @selected($module->category_stem == 'Dasar Logika')>Dasar Logika</option>
                                <option value="Matematika Dasar" @selected($module->category_stem == 'Matematika Dasar')>Matematika Dasar</option>
                                <option value="Aljabar (Math)" @selected($module->category_stem == 'Aljabar (Math)')>Aljabar (Math)</option>
                                <option value="Kalkulus (Math)" @selected($module->category_stem == 'Kalkulus (Math)')>Kalkulus (Math)</option>
                                <option value="Algoritma (Tech)" @selected($module->category_stem == 'Algoritma (Tech)')>Algoritma (Tech)</option>
                                <option value="Pemrograman (Tech)" @selected($module->category_stem == 'Pemrograman (Tech)')>Pemrograman (Tech)</option>
                                <option value="Fisika (Science)" @selected($module->category_stem == 'Fisika (Science)')>Fisika (Science)</option>
                                <option value="Mekanika (Science)" @selected($module->category_stem == 'Mekanika (Science)')>Mekanika (Science)</option>
                                <option value="Sistem Teknik (Eng)" @selected($module->category_stem == 'Sistem Teknik (Eng)')>Sistem Teknik (Eng)</option>
                                <option value="Advanced STEM" @selected($module->category_stem == 'Advanced STEM')>Advanced STEM</option>
                            </select>
                            @error('category_stem') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi Ringkas</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $module->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Update File Materi (Opsional)</label>
                            @if($module->file_path)
                                <div class="mb-2">
                                    <a href="{{ Storage::url($module->file_path) }}" target="_blank" class="badge bg-info text-decoration-none p-2">Lihat File Saat Ini</a>
                                </div>
                            @endif
                            <input type="file" name="file_path" class="form-control @error('file_path') is-invalid @enderror" accept=".pdf,.mp4,.pptx,.docx">
                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah file.</div>
                            @error('file_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dosen.modules.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Update Modul</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
