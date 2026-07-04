@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manajemen Program Studi</h1>
        <a href="{{ route('departments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Tambah Program Studi
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama Program Studi</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Mahasiswa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td><strong>{{ $department->name }}</strong></td>
                            <td>{{ $department->description ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $department->users_count }}</span>
                            </td>
                            <td>
                                <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('departments.destroy', $department) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Belum ada data program studi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $departments->links() }}
    </div>
</div>
@endsection
