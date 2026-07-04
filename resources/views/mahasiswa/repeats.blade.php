@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-redo text-primary me-2"></i>Pengajuan Pengulangan Mata Kuliah</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Form ajukan pengulangan (hanya untuk matkul yang nilainya D/E dan belum diajukan) --}}
    @if($failedGrades->count())
    <div class="card shadow-sm mb-4 border-warning">
        <div class="card-header bg-warning-subtle">
            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
            <strong>Mata Kuliah yang Bisa Diajukan Pengulangan</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('repeats.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Mata Kuliah <span class="text-danger">*</span></label>
                        <select name="course_id" id="courseSelect" class="form-select" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach($failedGrades as $grade)
                                <option value="{{ $grade->course_id }}" data-grade-id="{{ $grade->id }}">
                                    {{ $grade->course->kode_matkul }} — {{ $grade->course->nama_matkul }}
                                    (Nilai: {{ $grade->grade_huruf }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="grade_id" id="gradeIdInput">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Alasan <span class="text-danger">*</span></label>
                        <textarea name="alasan" class="form-control" rows="2" required
                                  placeholder="Jelaskan alasan ingin mengulang mata kuliah ini"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-paper-plane me-1"></i> Ajukan Pengulangan
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="alert alert-secondary">
        <i class="fas fa-info-circle me-1"></i> Tidak ada mata kuliah dengan nilai D/E yang bisa diajukan pengulangan saat ini.
    </div>
    @endif

    {{-- Riwayat pengajuan --}}
    <div class="card shadow-sm">
        <div class="card-header"><strong>Riwayat Pengajuan</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mata Kuliah</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Bukti Foto</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Jam Pengajuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repeats as $repeat)
                    <tr>
                        <td>{{ $repeat->course->kode_matkul ?? '-' }} — {{ $repeat->course->nama_matkul ?? '-' }}</td>
                        <td>{{ $repeat->alasan }}</td>
                        <td>
                            @if($repeat->status=='pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($repeat->status=='disetujui')
                                <span class="badge bg-success d-block mb-1">Disetujui</span>
                                @if($repeat->tanggal_ujian_ulang)
                                    <small class="text-muted">Ujian ulang: {{ $repeat->tanggal_ujian_ulang->translatedFormat('d F Y') }}</small>
                                @endif
                            @else
                                <span class="badge bg-danger d-block mb-1">Ditolak</span>
                                @if($repeat->catatan_admin)
                                    <small class="text-muted">{{ $repeat->catatan_admin }}</small>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($repeat->bukti_foto)
                                <a href="{{ asset('storage/'.$repeat->bukti_foto) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$repeat->bukti_foto) }}" alt="Bukti"
                                         style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>{{ $repeat->created_at->translatedFormat('d F Y') }}</td>
                        <td>{{ $repeat->created_at->format('H:i') }}</td>
                        <td>
                            @if($repeat->status=='pending')
                                <form action="{{ route('repeats.destroy', $repeat) }}" method="POST"
                                      onsubmit="return confirm('Batalkan pengajuan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Batal
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('courseSelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('gradeIdInput').value = opt.dataset.gradeId || '';
});
</script>
@endsection
