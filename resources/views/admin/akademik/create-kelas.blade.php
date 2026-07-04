@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Tambah Kelas - Semester {{ $semester }}</h1>

    <div class="card" style="max-width: 500px">
        <div class="card-body">
            <form action="{{ route('akademik.kelas.store', $semester) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor Kelas</label>
                    <input type="number" name="nomor_kelas" class="form-control"
                        placeholder="contoh: 1, 2, 3" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Program Studi</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">-- Pilih Prodi --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" class="form-control"
                        placeholder="contoh: 2024/2025" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('akademik.semester', $semester) }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection