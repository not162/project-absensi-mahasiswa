@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="mb-4">
        <h2 class="fw-bold">Selamat Datang, {{ auth()->user()->name }} 👋</h2>
        <p class="text-muted">{{ $hari }}, {{ now()->translatedFormat('d F Y') }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Kartu Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-primary">{{ $totalMatkulDiampu }}</div>
                    <div class="text-muted small mt-1">📚 Mata Kuliah Diampu</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-success">{{ $totalMahasiswa }}</div>
                    <div class="text-muted small mt-1">👨‍🎓 Total Mahasiswa</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-warning">{{ $pertemuanHariIni }}</div>
                    <div class="text-muted small mt-1">📋 Pertemuan Hari Ini</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-info">{{ $persenKehadiran }}%</div>
                    <div class="text-muted small mt-1">✅ Persentase Kehadiran</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Akses Cepat --}}
    <div class="row g-3">
        <div class="col-md-6">
            <a href="{{ route('schedules.byDosen', auth()->user()) }}" class="text-decoration-none">
                <div class="card border-primary h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-calendar-alt fa-2x text-primary me-3"></i>
                        <div>
                            <h6 class="mb-0">Jadwal Mengajar</h6>
                            <small class="text-muted">Lihat jadwal & mulai absensi mahasiswa</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('exam.mySupervisions') }}" class="text-decoration-none">
                <div class="card border-info h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-user-shield fa-2x text-info me-3"></i>
                        <div>
                            <h6 class="mb-0">Jadwal Mengawas Ujian</h6>
                            <small class="text-muted">Lihat jadwal & daftar mahasiswa diawas</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('absensi.rekap') }}" class="text-decoration-none">
                <div class="card border-success h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-chart-bar fa-2x text-success me-3"></i>
                        <div>
                            <h6 class="mb-0">Rekap Kehadiran</h6>
                            <small class="text-muted">Statistik kehadiran mahasiswa</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('grades.index') }}" class="text-decoration-none">
                <div class="card border-warning h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-star fa-2x text-warning me-3"></i>
                        <div>
                            <h6 class="mb-0">Input Nilai</h6>
                            <small class="text-muted">Input nilai mahasiswa per matkul</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection
