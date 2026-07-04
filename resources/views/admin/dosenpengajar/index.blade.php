@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <h1>Dosen Pengajar</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Dosen Pengajar (Mata Kuliah & Semester)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Kode Dosen</th>
                                    <th style="width: 20%">Nama Dosen</th>
                                    <th style="width: 20%">Prodi</th>
                                    <th style="width: 25%">Mata Kuliah</th>
                                    <th style="width: 10%">Semester</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lecturerCourses as $lc)
                                    <tr>
                                        <td>{{ $lc->lecturer->kode_dosen ?? '-' }}</td>
                                        <td>{{ $lc->lecturer->name ?? '-' }}</td>
                                        <td>{{ $lc->lecturer->department?->name ?? '-' }}</td>
                                        <td>
                                            {{ $lc->course->kode_matkul ?? '-' }}<br>
                                            <small class="text-muted">{{ $lc->course->nama_matkul ?? '-' }}</small>
                                        </td>
                                        <td>{{ $lc->semester ?? '-' }}</td>
                                        <td style="white-space: nowrap;">
                                            <a href="{{ route('dosen-pengajar.show', $lc) }}" class="btn btn-sm btn-info" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('dosen-pengajar.edit', $lc) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('dosen-pengajar.destroy', $lc) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada data dosen pengajar</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

