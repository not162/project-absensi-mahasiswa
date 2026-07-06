@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0"><i class="fas fa-laptop-code me-2"></i> {{ $exam->title }}</h3>
                </div>
                <div class="card-body p-5">
                    <h5 class="fw-bold">Informasi Ujian:</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Durasi Pengerjaan
                            <span class="badge bg-primary rounded-pill">{{ $exam->duration_minutes }} Menit</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Jenis Penilaian
                            <span class="badge bg-success rounded-pill">Fuzzy Logic & Pathfinding</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Status Anda
                            @if($attempt && $attempt->status == 'in_progress')
                                <span class="badge bg-warning text-dark rounded-pill">Sedang Berlangsung</span>
                            @else
                                <span class="badge bg-secondary rounded-pill">Belum Dimulai</span>
                            @endif
                        </li>
                    </ul>

                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i> Penilaian akhir akan disesuaikan dengan bobot Program Studi Anda (<strong>{{ Auth::user()->department->name ?? 'Umum' }}</strong>).
                    </div>

                    <div class="card bg-light border-0 rounded-3 p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="fas fa-university me-2"></i>Referensi Materi & Soal Latihan:</h6>
                        <p class="small text-muted mb-2">Soal ujian evaluasi ini dan modul materi kuliah disusun merujuk pada standar kurikulum dan bank soal dari institusi global berikut:</p>
                        <ul class="mb-0 text-muted ps-3 small">
                            <li class="mb-1"><a href="https://courses.maths.ox.ac.uk/" target="_blank" rel="noopener noreferrer" class="text-decoration-none">Oxford Mathematics Courses</a></li>
                            <li class="mb-1"><a href="https://ocw.mit.edu/search/" target="_blank" rel="noopener noreferrer" class="text-decoration-none">MIT OpenCourseWare (OCW)</a></li>
                            <li class="mb-1"><a href="https://pll.harvard.edu/catalog" target="_blank" rel="noopener noreferrer" class="text-decoration-none">Harvard University Catalog</a></li>
                            <li><a href="https://online.stanford.edu/free-courses" target="_blank" rel="noopener noreferrer" class="text-decoration-none">Stanford Online Free Courses</a></li>
                        </ul>
                    </div>

                    <div class="d-grid mt-4">
                        <a href="{{ route('stem.start', $exam->id) }}" class="btn btn-primary btn-lg">
                            @if($attempt && $attempt->status == 'in_progress')
                                Lanjutkan Ujian
                            @else
                                Mulai Kerjakan Sekarang
                            @endif
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
