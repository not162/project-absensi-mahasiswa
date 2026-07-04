@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-1"><i class="fas fa-clipboard-check text-primary me-2"></i>Absen Mengajar Hari Ini</h2>
    <p class="text-muted">{{ $hari }}, {{ $today->translatedFormat('d F Y') }}</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @forelse($schedules as $schedule)
        @php $att = $attendances->get($schedule->id); @endphp
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <div>
                    <strong>{{ substr($schedule->jam_mulai,0,5) }} - {{ substr($schedule->jam_selesai,0,5) }}</strong>
                    &mdash; {{ $schedule->course->nama_matkul ?? '-' }} ({{ $schedule->course->kode_matkul ?? '-' }})
                </div>
                @if($att)
                    <span class="badge bg-{{ $att->status=='hadir' ? 'success' : ($att->status=='tidak_hadir' ? 'danger' : 'warning') }}">
                        {{ ucfirst(str_replace('_',' ',$att->status)) }}
                    </span>
                @else
                    <span class="badge bg-secondary">Belum Diabsen</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Jurusan:</strong> {{ $schedule->course->department->name ?? '-' }}</div>
                    <div class="col-md-3"><strong>Semester:</strong> {{ $schedule->course->semester ?? '-' }}</div>
                    <div class="col-md-3"><strong>Kelas:</strong> {{ $schedule->kelas->nomor_kelas ?? '-' }}</div>
                    <div class="col-md-3"><strong>Ruangan:</strong> {{ $schedule->ruangan ?? $schedule->lokasi }}</div>
                </div>

                <div class="mb-3">
                    <strong>Daftar Mahasiswa ({{ $schedule->kelas->mahasiswa->count() ?? 0 }} orang):</strong>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        @forelse($schedule->kelas->mahasiswa ?? [] as $mhs)
                            <span class="badge bg-light text-dark border">{{ $mhs->nim }} — {{ $mhs->name }}</span>
                        @empty
                            <span class="text-muted small">Belum ada mahasiswa di kelas ini.</span>
                        @endforelse
                    </div>
                </div>

                <hr>
                <form action="{{ route('teaching-attendance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Status Mengajar</label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="hadir" @selected($att?->status=='hadir')>Hadir</option>
                                <option value="izin" @selected($att?->status=='izin')>Izin</option>
                                <option value="sakit" @selected($att?->status=='sakit')>Sakit</option>
                                <option value="tidak_hadir" @selected($att?->status=='tidak_hadir')>Tidak Hadir</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Materi yang Diajarkan</label>
                            <input type="text" name="materi" class="form-control form-control-sm"
                                   value="{{ $att?->materi }}" placeholder="cth: Pertemuan 5 - Looping">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Catatan</label>
                            <input type="text" name="catatan" class="form-control form-control-sm" value="{{ $att?->catatan }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3"></i>
            <p>Tidak ada jadwal mengajar hari ini ({{ $hari }}).</p>
        </div>
    @endforelse
</div>
@endsection
