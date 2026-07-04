@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detail Dosen Pengajar</h1>
        <div>
            <a href="{{ route('dosen-pengajar.edit', $lecturerCourse) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('dosenpengajar.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Informasi</h5>

                    <div class="mb-3">
                        <label class="form-label text-muted">Kode Dosen</label>
                        <div class="fs-5 fw-bold">{{ $lecturerCourse->lecturer->kode_dosen ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Nama Dosen</label>
                        <div class="fw-bold">{{ $lecturerCourse->lecturer->name ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Prodi</label>
                        <div>{{ $lecturerCourse->lecturer->department->name ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Semester</label>
                        <div class="fw-bold">{{ $lecturerCourse->semester }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Mata Kuliah</h5>
                    <div class="p-3 bg-light rounded">
                        <div><strong>Kode:</strong> {{ $lecturerCourse->course->kode_matkul ?? '-' }}</div>
                        <div><strong>Nama:</strong> {{ $lecturerCourse->course->nama_matkul ?? '-' }}</div>
                        <div><strong>Semester (course):</strong> {{ $lecturerCourse->course->semester ?? '-' }}</div>
                        <div><strong>Prodi (course):</strong> {{ $lecturerCourse->course->department_id ?? '-' }}</div>
                    </div>

                    <hr>

                    <form action="{{ route('dosen-pengajar.destroy', $lecturerCourse) }}" method="POST" onsubmit="return confirm('Yakin hapus data dosen pengajar ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

