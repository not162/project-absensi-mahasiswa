@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="text-center mb-5">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-2 fw-semibold">
            <i class="fas fa-brain me-1"></i> AI-Powered Feature
        </span>
        <h1 class="display-5 fw-extrabold text-dark mb-2">Pencarian Semantik Matakuliah</h1>
        <p class="text-muted mx-auto" style="max-width: 600px;">
            Temukan mata kuliah secara pintar menggunakan pencarian berbasis kemiripan vektor teks (Cosine Similarity). Masukkan konsep, deskripsi singkat, atau kata kunci.
        </p>
    </div>

    {{-- Search Form Card --}}
    <div class="card border-0 shadow-lg mb-5 overflow-hidden position-relative search-card">
        <div class="absolute-gradient-bg"></div>
        <div class="card-body p-4 p-md-5 position-relative">
            <form action="{{ route('semantic.search') }}" method="GET" id="semanticSearchForm">
                <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border">
                    <span class="input-group-text bg-white border-0 text-muted px-4">
                        <i class="fas fa-search-plus text-primary fs-4"></i>
                    </span>
                    <input type="text" name="query" id="searchInput" class="form-control border-0 ps-0 text-dark fw-medium" 
                           placeholder="Cari matakuliah (misal: 'pemrograman visual', 'basis data', 'analisis bisnis')" 
                           value="{{ $query }}" required autocomplete="off" style="font-size: 1.1rem; height: 60px;">
                    <button type="submit" class="btn btn-primary px-5 fw-bold text-white d-flex align-items-center gap-2 transition-all">
                        <span>Cari</span><i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>

            {{-- Quick Suggestion Tags --}}
            <div class="mt-4 d-flex align-items-center flex-wrap gap-2">
                <span class="text-muted small fw-semibold me-2"><i class="fas fa-lightbulb text-warning me-1"></i> Rekomendasi Pencarian:</span>
                @foreach(['Pemrograman Visual', 'Basis Data', 'Sistem Informasi', 'Analisis Bisnis', 'Jaringan Komputer'] as $tag)
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-xs suggestion-tag transition-all" 
                            onclick="setSearchQuery('{{ $tag }}')">
                        {{ $tag }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Results Section --}}
    @if($query)
        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">
                Hasil Pencarian
            </h4>
            <p class="text-muted small">
                Menemukan beberapa kecocokan semantik untuk kata kunci: <strong class="text-primary">"{{ $query }}"</strong>
            </p>
        </div>

        <div class="row g-4">
            @forelse($results as $index => $res)
                @php
                    $percentage = round($res['score'] * 100, 1);
                    $badgeColor = $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'primary' : 'warning');
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 course-result-card transition-all">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                {{-- Top Badge --}}
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                        {{ $res['kode'] }}
                                    </span>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-xs text-muted fw-semibold">Match:</span>
                                        <span class="badge bg-{{ $badgeColor }} text-white px-2 py-1">
                                            {{ $percentage }}%
                                        </span>
                                    </div>
                                </div>

                                {{-- Course Title --}}
                                <h5 class="fw-bold text-dark mb-2 course-title">{{ $res['nama'] }}</h5>
                                <p class="text-muted text-xs mb-3">
                                    <i class="fas fa-graduation-cap me-1"></i> Program Studi: <strong>{{ $res['department'] }}</strong>
                                </p>
                            </div>

                            {{-- Bottom Info --}}
                            <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                                <span class="text-xs text-muted">
                                    <i class="far fa-file-alt me-1"></i> {{ $res['sks'] }} SKS
                                </span>
                                <span class="text-xs text-muted">
                                    <i class="far fa-calendar-alt me-1"></i> Semester {{ $res['semester'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm py-5 text-center">
                        <div class="card-body">
                            <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="fas fa-search-minus text-danger fs-3"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Tidak Ada Kecocokan</h5>
                            <p class="text-muted mx-auto" style="max-width: 400px;">
                                Sistem tidak menemukan mata kuliah yang memiliki kemiripan semantik dengan kata kunci tersebut. Coba gunakan istilah lain.
                            </p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    @endif
</div>

<style>
    /* Styling Premium & Rich Aesthetics */
    .fw-extrabold { font-weight: 800; }
    .text-xs { font-size: 0.8rem; }
    
    .absolute-gradient-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(147, 26, 222, 0.05) 100%);
        z-index: 1;
    }
    
    .search-card {
        border-radius: 16px;
    }

    .suggestion-tag {
        font-size: 0.8rem;
        border-color: rgba(0, 0, 0, 0.1);
        color: #555;
    }
    .suggestion-tag:hover {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .transition-all {
        transition: all 0.25s ease-in-out;
    }

    /* Course Cards Styling */
    .course-result-card {
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.03) !important;
    }
    .course-result-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
        border-color: rgba(102, 126, 234, 0.2) !important;
    }
    .course-title {
        line-height: 1.4;
        font-size: 1.15rem;
    }
</style>

<script>
    function setSearchQuery(text) {
        const input = document.getElementById('searchInput');
        input.value = text;
        
        // Add active style to suggestion tags
        document.querySelectorAll('.suggestion-tag').forEach(tag => {
            if (tag.innerText.trim().toLowerCase() === text.toLowerCase()) {
                tag.classList.add('active');
            } else {
                tag.classList.remove('active');
            }
        });
        
        // Submit Form
        document.getElementById('semanticSearchForm').submit();
    }
</script>
@endsection
