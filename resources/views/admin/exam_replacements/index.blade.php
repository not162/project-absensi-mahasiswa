@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-file-signature text-primary me-2"></i>Persetujuan Ujian Pengganti</h2>
            <p class="text-muted">Universitas Tangsel Raya | Kelola pengajuan ujian susulan/pengganti dari mahasiswa.</p>
        </div>
        <div>
            <div class="btn-group">
                <a href="{{ route('exam.replacement.admin.index') }}" class="btn btn-{{ !$status ? 'primary' : 'outline-primary' }}">Semua</a>
                <a href="{{ route('exam.replacement.admin.index', ['status' => 'pending']) }}" class="btn btn-{{ $status === 'pending' ? 'warning' : 'outline-warning' }}">Pending</a>
                <a href="{{ route('exam.replacement.admin.index', ['status' => 'approved']) }}" class="btn btn-{{ $status === 'approved' ? 'success' : 'outline-success' }}">Disetujui</a>
                <a href="{{ route('exam.replacement.admin.index', ['status' => 'rejected']) }}" class="btn btn-{{ $status === 'rejected' ? 'danger' : 'outline-danger' }}">Ditolak</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Mahasiswa</th>
                            <th>Kelas</th>
                            <th>Mata Kuliah</th>
                            <th>Jenis Ujian</th>
                            <th>Alasan</th>
                            <th>Bukti</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($replacements as $index => $rep)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong class="text-dark">{{ $rep->student->name ?? '-' }}</strong><br>
                                <small class="text-muted">NIM: {{ $rep->student->nim ?? '-' }}</small>
                            </td>
                            <td>
                                @if($rep->student->kelas)
                                    Semester {{ $rep->student->kelas->semester }} - Kelas {{ $rep->student->kelas->nomor_kelas }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="d-block fw-semibold text-dark">{{ $rep->examSchedule->course->nama_matkul ?? '-' }}</span>
                                <small class="text-muted">{{ $rep->examSchedule->course->kode_matkul ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">{{ $rep->examSchedule->tipe ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-wrap d-block" style="max-width: 250px;">{{ $rep->alasan }}</span>
                            </td>
                            <td>
                                @if($rep->bukti_foto)
                                    <a href="{{ asset('storage/' . $rep->bukti_foto) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2">
                                        <i class="fas fa-image me-1"></i> Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-muted small">Tidak ada</span>
                                @endif
                            </td>
                            <td>
                                @if($rep->status === 'pending')
                                    <span class="badge bg-warning text-dark py-2 px-3"><i class="fas fa-hourglass-half me-1"></i> Pending</span>
                                @elseif($rep->status === 'approved')
                                    <span class="badge bg-success py-2 px-3"><i class="fas fa-check-circle me-1"></i> Disetujui</span>
                                @else
                                    <span class="badge bg-danger py-2 px-3"><i class="fas fa-times-circle me-1"></i> Ditolak</span>
                                @endif
                            </td>
                            <td>
                                @if($rep->status === 'pending')
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('exam.replacement.approve', $rep) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('exam.replacement.reject', $rep) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small">Selesai diproses</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                Belum ada pengajuan ujian pengganti.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
