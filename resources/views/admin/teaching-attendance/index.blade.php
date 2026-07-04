@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-history text-primary me-2"></i>Riwayat Absen Mengajar Dosen</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-1">Dosen</label>
                    <select name="dosen_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Dosen</option>
                        @foreach($dosenList as $dosen)
                            <option value="{{ $dosen->id }}" @selected(request('dosen_id')==$dosen->id)>{{ $dosen->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control form-control-sm"
                           value="{{ request('tanggal') }}" onchange="this.form.submit()">
                </div>
                <div class="col-auto">
                    <a href="{{ route('teaching-attendance.history') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>Dosen</th>
                        <th>Mata Kuliah</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Materi</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                    <tr>
                        <td>{{ $att->tanggal->format('d/m/Y') }}</td>
                        <td>{{ $att->dosen->name ?? '-' }}</td>
                        <td>{{ $att->schedule->course->nama_matkul ?? '-' }}</td>
                        <td>{{ $att->schedule->kelas->nomor_kelas ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $att->status=='hadir' ? 'success' : ($att->status=='tidak_hadir' ? 'danger' : 'warning') }}">
                                {{ ucfirst(str_replace('_',' ',$att->status)) }}
                            </span>
                        </td>
                        <td>{{ $att->materi ?? '-' }}</td>
                        <td>{{ $att->catatan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $attendances->links() }}</div>
    </div>
</div>
@endsection
