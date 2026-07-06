@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header border-0 text-white p-4" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="fw-bold mb-1"><i class="fas fa-language me-2"></i>Simulasi Ujian TOEFL & IELTS</h2>
                    <p class="mb-0 text-white-50">Ukur kemampuan bahasa Inggris Anda secara mandiri di sini.</p>
                </div>
                <span class="badge bg-white text-primary px-3 py-2 fw-semibold rounded-pill">E-Learning Center</span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-lg-8">
                    <h4 class="fw-bold mb-3">Selamat datang di English Proficiency Test!</h4>
                    <p class="text-muted leading-relaxed mb-4">
                        Halaman ini menyediakan simulasi tes TOEFL (Test of English as a Foreign Language) dan IELTS (International English Language Testing System) untuk mahasiswa. Hasil tes simulasi ini dapat digunakan sebagai bahan evaluasi kesiapan Anda sebelum mengambil tes resmi.
                    </p>

                    <div class="card bg-light border-0 rounded-3 p-4 mb-4">
                        <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-exclamation-circle me-2"></i>Ketentuan Pelaksanaan Ujian:</h5>
                        <ul class="mb-0 text-muted ps-3">
                            <li class="mb-2">Pastikan koneksi internet Anda stabil sebelum menekan tombol <strong>Mulai Ujian</strong>.</li>
                            <li class="mb-2">Ujian terdiri dari 3 bagian: Listening Comprehension, Structure & Written Expression, dan Reading Comprehension.</li>
                            <li class="mb-2">Total waktu pengerjaan adalah 120 menit dengan total 140 soal pilihan ganda.</li>
                            <li>Sekali ujian dimulai, waktu akan terus berjalan dan tidak dapat dihentikan (pause).</li>
                        </ul>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border border-2 rounded-3 p-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3">
                                        <i class="fas fa-headphones fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">TOEFL Simulation</h6>
                                        <small class="text-muted">Format: PBT (Paper Based)</small>
                                    </div>
                                </div>
                                <p class="text-muted small">Listening (50 soal), Structure (40 soal), Reading (50 soal).</p>
                                <a href="{{ route('mahasiswa.toefl.take') }}" class="btn btn-primary w-100 mt-2">
                                    Mulai Ujian TOEFL
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border border-2 rounded-3 p-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                                        <i class="fas fa-file-alt fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">IELTS Simulation</h6>
                                        <small class="text-muted">Format: Academic Module</small>
                                    </div>
                                </div>
                                <p class="text-muted small">Listening (40 soal), Reading (40 soal), Writing & Speaking.</p>
                                <button class="btn btn-success w-100 mt-2" onclick="alert('Ujian IELTS belum dibuka untuk periode semester ini.')">
                                    Mulai Ujian IELTS
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 bg-light rounded-3 p-4 h-100">
                        <h5 class="fw-bold mb-3"><i class="fas fa-history me-2 text-primary"></i>Riwayat Ujian Saya</h5>
                        
                        @forelse(($results ?? collect()) as $res)
                            <div class="card border-0 shadow-sm rounded-3 p-3 mb-3 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-success fs-5">{{ $res->total_score }}</span>
                                    <small class="text-muted">{{ $res->created_at->format('d M Y') }}</small>
                                </div>
                                <div class="row g-1 text-center text-muted small border-top pt-2 mt-1" style="font-size: 0.8rem;">
                                    <div class="col-4 border-end">L: {{ $res->listening_score }}</div>
                                    <div class="col-4 border-end">S: {{ $res->structure_score }}</div>
                                    <div class="col-4">R: {{ $res->reading_score }}</div>
                                </div>
                                <a href="{{ route('mahasiswa.toefl.result', $res->id) }}" class="btn btn-sm btn-outline-success w-100 mt-2">Detail Hasil</a>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-black-50"></i>
                                <p class="mb-0">Anda belum pernah mengikuti simulasi TOEFL atau IELTS.</p>
                                <small>Skor riwayat ujian Anda akan tampil di sini setelah menyelesaikan tes.</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
