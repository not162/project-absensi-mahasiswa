@extends('layouts.app')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Modul: {{ $module->title }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-book-open me-2"></i> {{ $module->title }}</h5>
                    <span class="badge bg-light text-primary">{{ $module->category_stem }}</span>
                </div>
                <div class="card-body p-4">
                    <h6 class="text-muted mb-4">
                        <i class="fas fa-user-tie me-1"></i> Diunggah oleh: {{ $module->dosen->name ?? 'Sistem' }}
                        <span class="mx-2">|</span>
                        <i class="fas fa-calendar-alt me-1"></i> {{ $module->created_at->format('d M Y') }}
                    </h6>

                    <div class="mb-4">
                        <h5 class="fw-bold border-bottom pb-2">Deskripsi Modul</h5>
                        <p class="fs-5" style="white-space: pre-line;">{{ $module->description ?? 'Tidak ada deskripsi.' }}</p>
                    </div>

                    @if($module->file_path)
                        <div class="alert alert-info d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-file-pdf fa-2x me-3"></i>
                                <strong>File Materi Tersedia</strong>
                            </div>
                            <a href="{{ Storage::url($module->file_path) }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Buka / Unduh File
                            </a>
                        </div>
                        
                        @php
                            $ext = pathinfo($module->file_path, PATHINFO_EXTENSION);
                        @endphp
                        
                        @if($ext == 'mp4')
                            <div class="ratio ratio-16x9 mt-4">
                                <video controls>
                                    <source src="{{ Storage::url($module->file_path) }}" type="video/mp4">
                                    Browser Anda tidak mendukung tag video.
                                </video>
                            </div>
                        @elseif($ext == 'pdf')
                            <div class="mt-4 border rounded" style="height: 600px;">
                                <iframe src="{{ Storage::url($module->file_path) }}" width="100%" height="100%" style="border: none;"></iframe>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> Dosen belum mengunggah file untuk materi ini. Silakan pelajari deskripsi di atas atau hubungi dosen yang bersangkutan.
                        </div>
                    @endif
                </div>
                <div class="card-footer text-center py-3 bg-light">
                    <button onclick="window.history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Rute Belajar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
