@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Dosen Pengajar</h1>
        <div>
            <a href="{{ route('dosenpengajar.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('dosen-pengajar.update', $lecturerCourse) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Kode Dosen</label>
                            <input type="text" class="form-control" value="{{ $lecturerCourse->lecturer->kode_dosen ?? '-' }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Dosen</label>
                            <input type="text" class="form-control" value="{{ $lecturerCourse->lecturer->name ?? '-' }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Program Studi</label>
                            <input type="text" class="form-control" value="{{ $lecturerCourse->lecturer->department->name ?? '-' }}" disabled>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <input type="number" min="1" max="8" class="form-control @error('semester') is-invalid @enderror" name="semester" value="{{ old('semester', $lecturerCourse->semester) }}" required>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(empty($courses))
                            <div class="alert alert-warning">
                                Daftar mata kuliah untuk prodi dosen ini tidak ditemukan.
                            </div>
                        @endif


                        <div class="mb-3">
                            <label class="form-label">Mata Kuliah</label>
                            <select class="form-select @error('course_id') is-invalid @enderror" name="course_id" required>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ (string)$course->id === (string)$lecturerCourse->course_id ? 'selected' : '' }}>
                                        {{ $course->kode_matkul }} - {{ $course->nama_matkul }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

