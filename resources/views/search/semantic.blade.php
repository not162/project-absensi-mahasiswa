@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-search-plus text-primary me-2"></i>Pencarian Semantik Matakuliah</h2>
        <p class="text-muted">Universitas Tangsel Raya | Temukan mata kuliah secara pintar menggunakan pencarian berbasis kemiripan vektor teks (Cosine Similarity).</p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('semantic.search') }}" method="GET">
                <div class="input-group input-group-lg">
                    <input type="text" name="query" class="form-control" placeholder="Cari matakuliah (misal: 'pemrograman visual', 'basis data enterprise', 'analisis bisnis')" value="{{ $query }}" required>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($query)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold mb-0 text-dark">Hasil Pencarian untuk: "<span class="text-primary">{{ $query }}</span>"</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Kode Matkul</th>
                                <th>Nama Matkul</th>
                                <th class="text-end">Vector Similarity Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $index => $res)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="badge bg-secondary">{{ $res['kode'] }}</span></td>
                                <td><strong class="text-dark">{{ $res['nama'] }}</strong></td>
                                <td class="text-end fw-bold text-success">
                                    {{ round($res['score'] * 100, 2) }}%
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-search-minus fa-2x mb-2 d-block"></i>
                                    Tidak ada mata kuliah yang cocok secara semantik.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
