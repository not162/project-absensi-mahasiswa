@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0 fw-bold">
                <i class="fas fa-file-alt me-2 text-primary"></i>
                Jadwal {{ strtoupper($tipe) }}
            </h2>
            <small class="text-muted">Tahun Ajaran {{ $tahunAjaran }} &mdash; Semester {{ $periode ? ucfirst($periode) : 'Semua' }}</small>
        </div>
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('exam.create', ['tipe' => $tipe]) }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Jadwal
        </a>
        @endif
    </div>

    {{-- Tab UTS / UAS --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tipe === 'uts' ? 'active fw-bold' : '' }}"
               href="{{ route('exam.index', array_merge(request()->all(), ['tipe' => 'uts'])) }}">
               <i class="fas fa-pencil-alt me-1"></i> UTS
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tipe === 'uas' ? 'active fw-bold' : '' }}"
               href="{{ route('exam.index', array_merge(request()->all(), ['tipe' => 'uas'])) }}">
               <i class="fas fa-pen-fancy me-1"></i> UAS
            </a>
        </li>
    </ul>

    {{-- Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="tipe" value="{{ $tipe }}">
                <div class="col-auto">
                    <label class="form-label small mb-1">Tahun Ajaran</label>
                    <select name="tahun_ajaran" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(['2023/2024','2024/2025','2025/2026'] as $ta)
                            <option value="{{ $ta }}" @selected($tahunAjaran==$ta)>{{ $ta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Periode</label>
                    <select name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="ganjil" @selected($periode=='ganjil')>Ganjil</option>
                        <option value="genap"  @selected($periode=='genap')>Genap</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Semester</label>
                    <select name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @for($i=1; $i<=8; $i++)
                            <option value="{{ $i }}" @selected($semester==$i)>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Program Studi</label>
                    <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Prodi</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(request('department_id')==$dept->id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <a href="{{ route('exam.index', ['tipe' => $tipe]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Ringkasan --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body py-2">
                    <div class="fs-3 fw-bold text-primary">{{ $exams->count() }}</div>
                    <small class="text-muted">Total Jadwal</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body py-2">
                    <div class="fs-3 fw-bold text-success">{{ $exams->unique('department_id')->count() }}</div>
                    <small class="text-muted">Program Studi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body py-2">
                    <div class="fs-3 fw-bold text-warning">{{ $exams->unique('tanggal')->count() }}</div>
                    <small class="text-muted">Hari Ujian</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body py-2">
                    <div class="fs-3 fw-bold text-info">{{ $exams->unique('ruangan')->count() }}</div>
                    <small class="text-muted">Ruangan Dipakai</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel per Tanggal --}}
    @forelse($examsByDate as $tanggal => $items)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white py-2">
                <i class="fas fa-calendar-day me-2"></i>
                <strong>{{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</strong>
                <span class="badge bg-light text-primary ms-2">{{ $items->count() }} ujian</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Jam</th>
                            <th>Kode</th>
                            <th>Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Smt</th>
                            <th>Kelas</th>
                            <th>Prodi</th>
                            <th>Ruangan</th>
                            <th>Pengawas</th>
                            <th>Tipe Soal</th>
                            @if(auth()->user()->role === 'admin')
                            <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items->sortBy('jam_mulai') as $exam)
                        <tr>
                            <td class="fw-bold text-nowrap">
                                {{ substr($exam->jam_mulai,0,5) }} –<br>{{ substr($exam->jam_selesai,0,5) }}
                            </td>
                            <td><code>{{ $exam->course->kode_matkul ?? '-' }}</code></td>
                            <td>{{ $exam->course->nama_matkul ?? '-' }}</td>
                            <td class="text-center">{{ $exam->course->sks ?? '-' }}</td>
                            <td class="text-center">{{ $exam->semester }}</td>
                            <td class="text-center">{{ $exam->kelas->nomor_kelas ?? '-' }}</td>
                            <td>{{ $exam->department->name ?? '-' }}</td>
                            <td>{{ $exam->ruangan }}</td>
                            <td>{{ $exam->pengawas->name ?? '-' }}</td>
                            <td>
                                @php
                                    $badge = match($exam->tipe_soal) {
                                        'online'    => 'bg-info',
                                        'take-home' => 'bg-warning text-dark',
                                        default     => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ ucfirst($exam->tipe_soal) }}</span>
                            </td>
                            @if(auth()->user()->role === 'admin')
                            <td class="text-nowrap">
                                <a href="{{ route('exam.edit', $exam) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('exam.destroy', $exam) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3"></i>
            <p>Belum ada jadwal {{ strtoupper($tipe) }} untuk filter yang dipilih.</p>
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('exam.create', ['tipe' => $tipe]) }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Jadwal
            </a>
            @endif
        </div>
    @endforelse
</div>
@endsection