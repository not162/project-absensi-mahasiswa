@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Tambah Modul Belajar</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('dosen.modules.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Modul</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="Contoh: Pengantar Logika Algoritma">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Kategori / Topik (Node Dijkstra)</label>
                            <select name="category_stem" class="form-select @error('category_stem') is-invalid @enderror" required>
                                <option value="">-- Pilih Topik Modul --</option>
                                <option value="S">S (Science/Sains Umum)</option>
                                <option value="T">T (Technology/Teknologi Umum)</option>
                                <option value="E">E (Engineering/Teknik Umum)</option>
                                <option value="M">M (Math/Matematika Umum)</option>
                                <option disabled>── Spesifik (Dijkstra Nodes) ──</option>
                                <option value="Dasar Logika">Dasar Logika</option>
                                <option value="Matematika Dasar">Matematika Dasar</option>
                                <option value="Aljabar (Math)">Aljabar (Math)</option>
                                <option value="Kalkulus (Math)">Kalkulus (Math)</option>
                                <option value="Algoritma (Tech)">Algoritma (Tech)</option>
                                <option value="Pemrograman (Tech)">Pemrograman (Tech)</option>
                                <option value="Fisika (Science)">Fisika (Science)</option>
                                <option value="Mekanika (Science)">Mekanika (Science)</option>
                                <option value="Sistem Teknik (Eng)">Sistem Teknik (Eng)</option>
                                <option value="Advanced STEM">Advanced STEM</option>
                            </select>
                            <div class="form-text">Sesuaikan nama topik dengan node yang digunakan di Algoritma Pathfinding agar link otomatis menyala untuk mahasiswa.</div>
                            @error('category_stem') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi Ringkas</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan isi modul secara singkat...">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Upload File Materi (Opsional)</label>
                            <input type="file" name="file_path" class="form-control @error('file_path') is-invalid @enderror" accept=".pdf,.mp4,.pptx,.docx">
                            <div class="form-text">Maksimal 10MB (Disarankan format PDF).</div>
                            @error('file_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dosen.modules.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Modul</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
