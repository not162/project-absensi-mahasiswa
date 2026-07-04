@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i>Absensi Saya</h4>
            <small class="text-muted">Riwayat kehadiran Anda</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
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
                        <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                        <td>{{ $attendance->attendance_date?->format('d/m/Y') }}</td>
                        <td>{{ $attendance->time_in ?? '-' }}</td>
                        <td>{{ $attendance->time_out ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $attendance->status === 'hadir' ? 'success' : 'secondary' }}">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </td>
                        <td>{{ $attendance->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                            Belum ada riwayat absensi.
                        </td>
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
