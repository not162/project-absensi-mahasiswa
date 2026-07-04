@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-star text-warning me-2"></i>Input Nilai Mahasiswa</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter kelas & matkul --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small mb-1">Kelas</label>
                    <select name="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" @selected($classId==$kelas->id)>
                                {{ $kelas->department->name ?? '' }} - Semester {{ $kelas->semester }} - Kelas {{ $kelas->nomor_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Mata Kuliah</label>
                    <select name="course_id" class="form-select" onchange="this.form.submit()" @disabled(!$classId)>
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected($courseId==$course->id)>
                                {{ $course->kode_matkul }} — {{ $course->nama_matkul }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($classId && $courseId && $grades->count())
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <form action="{{ route('grades.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $classId }}">
                    <input type="hidden" name="course_id" value="{{ $courseId }}">

                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="120">Tugas</th>
                                <th width="120">UTS</th>
                                <th width="120">UAS</th>
                                <th width="100">Nilai Akhir</th>
                                <th width="80">Huruf</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grades as $grade)
                            <tr>
                                <td>{{ $grade->student->nim ?? '-' }}</td>
                                <td>{{ $grade->student->name }}</td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="100"
                                           name="nilai[{{ $grade->student->id }}][tugas]"
                                           class="form-control form-control-sm"
                                           value="{{ $grade->nilai_tugas }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="100"
                                           name="nilai[{{ $grade->student->id }}][uts]"
                                           class="form-control form-control-sm"
                                           value="{{ $grade->nilai_uts }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="100"
                                           name="nilai[{{ $grade->student->id }}][uas]"
                                           class="form-control form-control-sm"
                                           value="{{ $grade->nilai_uas }}">
                                </td>
                                <td class="fw-bold">{{ $grade->nilai_akhir ?? '-' }}</td>
                                <td>
                                    @if($grade->grade_huruf)
                                        <span class="badge bg-{{ in_array($grade->grade_huruf, ['A','AB','B']) ? 'success' : (in_array($grade->grade_huruf, ['BC','C']) ? 'warning' : 'danger') }}">
                                            {{ $grade->grade_huruf }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan Semua Nilai
                        </button>
                        <small class="text-muted ms-2">Bobot: Tugas 30%, UTS 30%, UAS 40%</small>
                    </div>
                </form>
            </div>
        </div>
    @elseif($classId && $courseId)
        <div class="text-center py-5 text-muted">
            <i class="fas fa-user-slash fa-3x mb-3"></i>
            <p>Belum ada mahasiswa di kelas ini.</p>
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-hand-point-up fa-3x mb-3"></i>
            <p>Pilih kelas dan mata kuliah terlebih dahulu.</p>
        </div>
    @endif
</div>
@endsection
