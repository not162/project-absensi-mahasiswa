@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Hasil Ujian STEM</h5>
                    <a href="{{ route('stem.pdf', $attempt->id) }}" class="btn btn-sm btn-light text-success fw-bold">
                        <i class="fas fa-file-pdf me-1"></i> Cetak PDF
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted text-uppercase small">Skor Mentah (Raw)</h6>
                                <h2 class="fw-bold mb-0">{{ number_format($attempt->raw_score, 1) }}</h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted text-uppercase small">Skor Fuzzy Logic</h6>
                                <h2 class="fw-bold text-primary mb-0">{{ number_format($attempt->fuzzy_score, 1) }}</h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted text-uppercase small">Keputusan (Decision)</h6>
                                @if(str_contains($attempt->decision, 'Lulus'))
                                    <h2 class="fw-bold text-success mb-0">{{ $attempt->decision }}</h2>
                                @else
                                    <h2 class="fw-bold text-danger mb-0">{{ $attempt->decision }}</h2>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="fw-bold mt-4"><i class="fas fa-route text-warning me-2"></i> Rekomendasi Remedial (Dijkstra/A* Pathfinding)</h5>
                    <p class="text-muted">Berdasarkan hasil analisis kategori terlemah, algoritma kami menyarankan Anda untuk mengikuti jalur belajar (Learning Path) berikut:</p>
                    
                    @if(isset($attempt->remedial_path['path']) && count($attempt->remedial_path['path']) > 0)
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                            @foreach($attempt->remedial_path['path'] as $idx => $node)
                                @if(isset($availableModules[$node]))
                                    <a href="{{ route('stem.module.show', $availableModules[$node]->id) }}" class="badge bg-primary fs-6 p-2 text-decoration-none shadow-sm" title="Klik untuk membuka Modul Belajar">
                                        <i class="fas fa-book-reader me-1"></i> {{ $node }}
                                    </a>
                                @else
                                    <span class="badge bg-secondary fs-6 p-2">{{ $node }}</span>
                                @endif

                                @if($idx < count($attempt->remedial_path['path']) - 1)
                                    <i class="fas fa-arrow-right text-muted"></i>
                                @endif
                            @endforeach
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i> Estimasi waktu belajar yang dibutuhkan untuk menyelesaikan jalur ini adalah <strong>{{ $attempt->remedial_path['total_effort_hours'] ?? 0 }} Jam</strong>.
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-star me-2"></i> Nilai Anda sangat memuaskan, tidak ada jalur remedial yang diperlukan!
                        </div>
                    @endif

                    <div class="text-center mt-5">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
