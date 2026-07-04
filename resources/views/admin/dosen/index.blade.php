@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manajemen Dosen</h1>
        <a href="{{ route('dosen.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Tambah Dosen
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, kode dosen, atau email..."
                               value="{{ $search }}">
                        <button type="submit" class="btn btn-primary">Cari</button>
                        @if($search)
                            <a href="{{ route('dosen.index') }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Dosen</th>
                        <th>Nama Dosen</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Program Studi</th>
                        <th>Mata Kuliah Diajar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
@forelse($dosen ?? [] as $user)


                        <tr>


                            <td>
                                <span class="badge bg-info text-dark">{{ $user->kode_dosen }}</span>
                            </td>

                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>{{ $user->phone ?? '-' }}</td>

                            <td>{{ $user->department?->name ?? '-' }}</td>

                            <td>
                                @php $taughtCourses = $user->lecturerCourses->pluck('course')->filter()->unique('id'); @endphp
                                @forelse($taughtCourses as $course)
                                    <span class="badge bg-light text-dark border mb-1">{{ $course->nama_matkul }}</span>
                                @empty
                                    <span class="text-muted small">Belum ada matkul</span>
                                @endforelse
                            </td>

                            <td>
                                <a href="{{ route('dosen.show', $user) }}" class="btn btn-sm btn-info" title="Lihat Detail">

                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('dosen.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit">

                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('dosen.destroy', $user) }}" method="POST" style="display: inline;">

                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada data dosen</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{-- $dosen adalah Paginator; jangan shadow variabel di foreach/forelse --}}
        {{ ($dosen ?? null) ? $dosen->links() : '' }}
    </div>



</div>
@endsection