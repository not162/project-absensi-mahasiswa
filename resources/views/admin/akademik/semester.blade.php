@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Semester {{ $semester }}</h1>
        <div>
            <a href="{{ route('akademik.kelas.create', $semester) }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Kelas
            </a>
            <a href="{{ route('akademik.index') }}" class="btn btn-secondary ms-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @forelse($kelasList as $departmentId => $kelasDiProdi)
        @php $prodi = $kelasDiProdi->first()->department @endphp
        <div class="mb-5">
            {{-- Nama Prodi --}}
            <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="fas fa-graduation-cap me-2"></i>{{ $prodi->name }}
            </h4>

            {{-- Kelas dalam prodi ini --}}
            <div class="row g-3">
                @foreach($kelasDiProdi as $kelas)
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0">Kelas {{ $kelas->nomor_kelas }}</h5>
                            <div>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#assignModal{{ $kelas->id }}">
                                    <i class="fas fa-book me-1"></i> Assign Matkul
                                </button>
                                <form action="{{ route('akademik.kelas.destroy', $kelas) }}"
                                    method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                        onclick="return confirm('Hapus kelas ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Mata Kuliah</th>
                                        <th>SKS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelas->courses as $course)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $course->kode_matkul }}</span></td>
                                        <td>{{ $course->nama_matkul }}</td>
                                        <td>{{ $course->sks }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-2">
                                            Belum ada mata kuliah
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Modal Assign Matkul --}}
                <div class="modal fade" id="assignModal{{ $kelas->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Assign Matkul - Kelas {{ $kelas->nomor_kelas }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('akademik.assign', $kelas) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <p class="text-muted">Pilih mata kuliah semester {{ $semester }} untuk kelas ini:</p>
                                    @foreach($departments as $dept)
                                        @if($dept->courses->count() > 0)
                                        <p class="fw-bold mb-1">{{ $dept->name }}</p>
                                        @foreach($dept->courses as $course)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="course_ids[]" value="{{ $course->id }}"
                                                id="course{{ $kelas->id }}_{{ $course->id }}"
                                                {{ $kelas->courses->contains($course->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="course{{ $kelas->id }}_{{ $course->id }}">
                                                <span class="badge bg-secondary">{{ $course->kode_matkul }}</span>
                                                {{ $course->nama_matkul }}
                                            </label>
                                        </div>
                                        @endforeach
                                        @endif
                                    @endforeach
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-5">
            <i class="fas fa-folder-open fa-3x mb-3"></i>
            <p>Belum ada kelas di semester ini. Klik "Tambah Kelas" untuk mulai.</p>
        </div>
    @endforelse
</div>
@endsection