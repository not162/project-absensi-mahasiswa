@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-redo text-primary me-2"></i>Pengajuan Pengulangan Mata Kuliah</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ !$status ? 'active fw-bold' : '' }}" href="{{ route('repeats.index') }}">Semua</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status=='pending' ? 'active fw-bold' : '' }}" href="{{ route('repeats.index', ['status'=>'pending']) }}">
                Pending
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status=='disetujui' ? 'active fw-bold' : '' }}" href="{{ route('repeats.index', ['status'=>'disetujui']) }}">
                Disetujui
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status=='ditolak' ? 'active fw-bold' : '' }}" href="{{ route('repeats.index', ['status'=>'ditolak']) }}">
                Ditolak
            </a>
        </li>
    </ul>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>NIM</th>
                        <th>Mata Kuliah</th>
                        <th>Nilai Lama</th>
                        <th>Alasan</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Jam Pengajuan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repeats as $repeat)
                    <tr>
                        <td>{{ $repeat->student->name ?? '-' }}</td>
                        <td>{{ $repeat->student->nim ?? '-' }}</td>
                        <td>{{ $repeat->course->kode_matkul ?? '-' }} — {{ $repeat->course->nama_matkul ?? '-' }}</td>
                        <td>
                            @if($repeat->grade)
                                <span class="badge bg-danger">{{ $repeat->grade->grade_huruf }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $repeat->alasan }}</td>
                        <td>{{ $repeat->created_at->translatedFormat('d F Y') }}</td>
                        <td>{{ $repeat->created_at->format('H:i') }}</td>
                        <td>
                            @if($repeat->status=='pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($repeat->status=='disetujui')
                                <span class="badge bg-success d-block mb-1">Disetujui</span>
                                @if($repeat->tanggal_ujian_ulang)
                                    <small class="text-muted d-block">Ujian ulang: {{ $repeat->tanggal_ujian_ulang->translatedFormat('d F Y') }}</small>
                                @endif
                                @if($repeat->bukti_foto)
                                    <a href="{{ asset('storage/'.$repeat->bukti_foto) }}" target="_blank" class="small">
                                        <i class="fas fa-image me-1"></i>Lihat Bukti
                                    </a>
                                @endif
                            @else
                                <span class="badge bg-danger d-block mb-1">Ditolak</span>
                                @if($repeat->catatan_admin)
                                    <small class="text-muted">{{ $repeat->catatan_admin }}</small>
                                @endif
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if($repeat->status=='pending')
                                <button type="button" class="btn btn-sm btn-success" title="Setujui"
                                        data-bs-toggle="modal" data-bs-target="#approveModal{{ $repeat->id }}">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" title="Tolak"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $repeat->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>

                    @if($repeat->status=='pending')
                    {{-- Modal Setujui --}}
                    <div class="modal fade" id="approveModal{{ $repeat->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('repeats.approve', $repeat) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Setujui Pengajuan &mdash; {{ $repeat->student->name ?? '-' }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Tanggal Pengulangan Ujian <span class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_ujian_ulang" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Bukti Foto (opsional)</label>
                                            <input type="file" name="bukti_foto" class="form-control" accept=".jpg,.jpeg,.png">
                                            <small class="text-muted">Maks. 2MB (jpg, png)</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check me-1"></i>Setujui
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Tolak --}}
                    <div class="modal fade" id="rejectModal{{ $repeat->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('repeats.reject', $repeat) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tolak Pengajuan &mdash; {{ $repeat->student->name ?? '-' }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label fw-semibold">Alasan Penolakan</label>
                                        <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Jelaskan alasan penolakan (opsional)"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-times me-1"></i>Tolak
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection
