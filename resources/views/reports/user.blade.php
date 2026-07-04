@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Laporan Per Pengguna</h1>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="user_id" class="form-label">Pengguna *</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">-- Pilih Pengguna --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
        </div>
    </div>

    @if($userId)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Data Pengguna: {{ $user->name }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>No. Telepon:</strong> {{ $user->phone ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Departemen:</strong> {{ $user->department?->name ?? '-' }}</p>
                        <p><strong>Role:</strong> {{ $user->role === 'admin' ? 'Admin' : 'User' }}</p>
                    </div>
                </div>
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
                    <div class="col-md-2 text-center">
                        <h3>{{ $summary['working_days'] }}</h3>
                        <p>Hari Kerja</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Absensi</h5>
                    <form action="{{ route('reports.export') }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="type" value="user">
                        <input type="hidden" name="user_id" value="{{ $userId }}">
                        <input type="hidden" name="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" value="{{ $endDate }}">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
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
                                <td>{{ $attendance->notes ?? '-' }}</td>
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
    @endif
</div>
@endsection
