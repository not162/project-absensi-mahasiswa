@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Manajemen Modul Belajar (LMS)</h2>
            <p class="text-muted">Kelola materi untuk remedial mahasiswa (Integrasi Dijkstra)</p>
        </div>
        <a href="{{ route('dosen.modules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Modul Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Judul Modul</th>
                            <th width="15%">Kategori / Node</th>
                            <th width="25%">Deskripsi Singkat</th>
                            <th width="15%">File</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $idx => $mod)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="fw-bold">{{ $mod->title }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $mod->category_stem }}</span>
                                </td>
                                <td>{{ Str::limit($mod->description, 50) }}</td>
                                <td>
                                    @if($mod->file_path)
                                        <a href="{{ Storage::url($mod->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-download me-1"></i> Lihat
                                        </a>
                                    @else
                                        <span class="text-muted fst-italic">Tanpa File</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('dosen.modules.edit', $mod->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('dosen.modules.destroy', $mod->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus modul ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-folder-open fs-1 mb-3 text-light"></i>
                                    <p class="mb-0">Belum ada Modul Belajar yang ditambahkan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
