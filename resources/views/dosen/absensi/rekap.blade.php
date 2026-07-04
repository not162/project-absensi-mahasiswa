@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-chart-bar text-primary me-2"></i>Rekap Kehadiran Mahasiswa</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-1">Semester</label>
                    <select name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Semester</option>
                        @for($i=1; $i<=8; $i++)
                            <option value="{{ $i }}" @selected($semesterF==$i)>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Mata Kuliah</label>
                    <select name="course_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Matkul</option>
                        @foreach($myCourses as $course)
                            <option value="{{ $course->id }}" @selected($courseF==$course->id)>{{ $course->nama_matkul }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <a href="{{ route('absensi.rekap') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @forelse($rekap as $item)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $item['schedule']->course->nama_matkul ?? '-' }}</strong>
                &mdash; {{ $item['schedule']->kelas->department->name ?? '-' }}
                &mdash; Kelas {{ $item['schedule']->kelas->nomor_kelas ?? '-' }}
                &mdash; Semester {{ $item['schedule']->kelas->semester ?? '-' }}
            </div>
            <span class="badge bg-light text-primary">{{ $item['pertemuan'] }} Pertemuan</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center">Sakit</th>
                        <th class="text-center">Tidak Hadir</th>
                        <th class="text-center">%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($item['rows'] as $i => $row)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $row['mahasiswa']->nim ?? '-' }}</td>
                        <td>{{ $row['mahasiswa']->name }}</td>
                        <td class="text-center">{{ $row['hadir'] }}</td>
                        <td class="text-center">{{ $row['izin'] }}</td>
                        <td class="text-center">{{ $row['sakit'] }}</td>
                        <td class="text-center">{{ $row['tidak_hadir'] }}</td>
                        <td class="text-center">{{ $row['persen'] }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">Belum ada mahasiswa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="fas fa-chart-bar fa-3x mb-3"></i>
        <p>Belum ada data rekap kehadiran.</p>
    </div>
    @endforelse
</div>
@endsection
