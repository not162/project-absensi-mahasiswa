@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-file-signature text-primary me-2"></i>Pengajuan Ujian Pengganti</h2>
        <p class="text-muted">Universitas Tangsel Raya | Ajukan ujian susulan/pengganti jika Anda berhalangan hadir pada ujian utama.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Form Pengajuan --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-paper-plane text-success me-2"></i>Form Pengajuan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('exam.replacement.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="exam_schedule_id" class="form-label fw-semibold">Jadwal Ujian Utama</label>
                            <select name="exam_schedule_id" id="exam_schedule_id" class="form-select @error('exam_schedule_id') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Jadwal Ujian --</option>
                                @foreach($examSchedules as $schedule)
                                    <option value="{{ $schedule->id }}">
                                        {{ strtoupper($schedule->tipe) }} - {{ $schedule->course->nama_matkul }} ({{ \Carbon\Carbon::parse($schedule->tanggal)->translatedFormat('d M Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('exam_schedule_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="alasan" class="form-label fw-semibold">Alasan Berhalangan</label>
                            <textarea name="alasan" id="alasan" rows="4" class="form-control @error('alasan') is-invalid @enderror" placeholder="Jelaskan alasan detail mengapa Anda berhalangan hadir..." required>{{ old('alasan') }}</textarea>
                            @error('alasan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="bukti_foto" class="form-label fw-semibold">Unggah Bukti (Foto/Dokumen)</label>
                            <input type="file" name="bukti_foto" id="bukti_foto" class="form-control @error('bukti_foto') is-invalid @enderror" accept="image/*" required>
                            <small class="text-muted">Unggah surat keterangan sakit, surat dinas, atau bukti valid lainnya (Format: JPG, PNG. Max: 2MB).</small>
                            @error('bukti_foto')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-check-circle me-1"></i> Kirim Pengajuan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Riwayat Pengajuan --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-info me-2"></i>Riwayat Pengajuan Ujian Pengganti</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Mata Kuliah</th>
                                    <th>Jenis Ujian</th>
                                    <th>Alasan</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th>Tanggal Pengajuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($replacements as $index => $rep)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
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
                                    <td>{{ $rep->created_at->translatedFormat('d M Y, H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
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
    </div>
</div>
@endsection
