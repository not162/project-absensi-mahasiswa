@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Laporan Bulanan</h1>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="month" class="form-label">Bulan</label>
                    <input type="month" name="month" id="month" class="form-control" 
                           value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}">
                </div>
                <div class="col-md-3">
                    <label for="department_id" class="form-label">Departemen</label>
                    <select name="department_id" id="department_id" class="form-control">
                        <option value="">-- Semua Departemen --</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="user_id" class="form-label">Pengguna</label>
                    <select name="user_id" id="user_id" class="form-control">
                        <option value="">-- Semua Pengguna --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Ringkasan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 text-center">
                    <h3 class="text-success">{{ $summary['present'] }}</h3>
                    <p>Hadir</p>
                </div>
                <div class="col-md-2 text-center">
                    <h3 class="text-warning">{{ $summary['late'] }}</h3>
                    <p>Terlambat</p>
                </div>
                <div class="col-md-2 text-center">
                    <h3 class="text-danger">{{ $summary['absent'] }}</h3>
                    <p>Absen</p>
                </div>
                <div class="col-md-2 text-center">
                    <h3 class="text-info">{{ $summary['sick'] }}</h3>
                    <p>Sakit</p>
                </div>
                <div class="col-md-2 text-center">
                    <h3 class="text-secondary">{{ $summary['permission'] }}</h3>
                    <p>Izin</p>
                </div>
                <div class="col-md-2">
                    <form action="{{ route('reports.export') }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="type" value="monthly">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->user->name }}</td>
                            <td>{{ $attendance->attendance_date->format('d M Y') }}</td>
                            <td>{{ $attendance->time_in ?? '-' }}</td>
                            <td>{{ $attendance->time_out ?? '-' }}</td>
                            <td>
                                @if($attendance->status === 'present')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($attendance->status === 'late')
                                    <span class="badge bg-warning text-dark">Terlambat</span>
                                @elseif($attendance->status === 'absent')
                                    <span class="badge bg-danger">Absen</span>
                                @elseif($attendance->status === 'sick')
                                    <span class="badge bg-info">Sakit</span>
                                @else
                                    <span class="badge bg-secondary">Izin</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $attendances->links() }}
    </div>
</div>
@endsection
