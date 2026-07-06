@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <!-- Header -->
                <div class="card-header bg-success text-white p-4 text-center">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2"></i>Hasil Simulasi TOEFL Anda</h4>
                    <p class="mb-0 small text-white-50">Skor Anda telah dihitung menggunakan standar penilaian TOEFL PBT</p>
                </div>

                <div class="card-body p-5">
                    <!-- Score Board -->
                    <div class="text-center mb-5">
                        <h1 class="display-3 fw-bold text-success mb-1">{{ $result->total_score }}</h1>
                        <h5 class="text-secondary fw-semibold">Total TOEFL Score</h5>
                        
                        @php
                            $badgeColor = 'bg-secondary';
                            $levelName = 'Novice';
                            if ($result->total_score >= 600) {
                                $badgeColor = 'bg-danger';
                                $levelName = 'Advanced / Fluent (Gold)';
                            } elseif ($result->total_score >= 500) {
                                $badgeColor = 'bg-primary';
                                $levelName = 'High Intermediate (Silver)';
                            } elseif ($result->total_score >= 400) {
                                $badgeColor = 'bg-warning text-dark';
                                $levelName = 'Low Intermediate (Bronze)';
                            }
                        @endphp
                        <span class="badge {{ $badgeColor }} px-4 py-2 rounded-pill fs-6 mt-2">{{ $levelName }}</span>
                    </div>

                    <!-- Detail Section Scores -->
                    <div class="row g-3 mb-5 text-center">
                        <div class="col-md-4">
                            <div class="card border border-2 rounded-3 p-3">
                                <h6 class="text-muted mb-2">Listening</h6>
                                <h4 class="fw-bold text-dark mb-0">{{ $result->listening_score }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border border-2 rounded-3 p-3">
                                <h6 class="text-muted mb-2">Structure</h6>
                                <h4 class="fw-bold text-dark mb-0">{{ $result->structure_score }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border border-2 rounded-3 p-3">
                                <h6 class="text-muted mb-2">Reading</h6>
                                <h4 class="fw-bold text-dark mb-0">{{ $result->reading_score }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 p-4 mb-4">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Skor:</h6>
                        <p class="small text-muted mb-0">
                            Skor TOEFL PBT resmi berkisar antara 310 hingga 677. Penilaian dihitung secara tertimbang berdasarkan persentase jawaban benar di masing-masing bagian: Listening Comprehension (Maks. 68), Structure and Written Expression (Maks. 68), dan Reading Comprehension (Maks. 67).
                        </p>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('mahasiswa.toefl') }}" class="btn btn-primary px-4 py-2 fw-bold">
                            <i class="fas fa-undo me-2"></i> Kembali ke Menu TOEFL
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
