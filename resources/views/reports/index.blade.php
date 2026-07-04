@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Laporan Absensi</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Laporan Bulanan</h5>
                </div>
                <div class="card-body">
                    <p>Lihat laporan absensi per bulan</p>
                    <a href="{{ route('reports.monthly') }}" class="btn btn-primary w-100">
                        <i class="fas fa-calendar me-2"></i> Lihat Laporan
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Laporan Harian</h5>
                </div>
                <div class="card-body">
                    <p>Lihat laporan absensi harian</p>
                    <a href="{{ route('reports.daily') }}" class="btn btn-success w-100">
                        <i class="fas fa-calendar-day me-2"></i> Lihat Laporan
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Laporan Per Pengguna</h5>
                </div>
                <div class="card-body">
                    <p>Lihat laporan absensi per pengguna</p>
                    <a href="{{ route('reports.user') }}" class="btn btn-info w-100">
                        <i class="fas fa-user me-2"></i> Lihat Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
