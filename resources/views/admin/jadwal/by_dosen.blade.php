@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Jadwal Mengajar</h2>
        <p class="text-muted">{{ $dosen->name }} &mdash; {{ $dosen->kode_dosen ?? '' }} &mdash; {{ $dosen->department->name ?? '-' }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Mata Kuliah</th>
                        <th>Prodi</th>
                        <th>Semester</th>
                        <th>Kelas</th>
                        <th>Ruangan</th>
                        <th>Mahasiswa</th>
                        <th>Absensi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                    <tr class="{{ $schedule->hari === $hariIni ? 'table-warning' : '' }}">
                        <td>
                            <span class="badge {{ $schedule->hari === $hariIni ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                {{ $schedule->hari }}
                            </span>
                        </td>
                        <td>{{ substr($schedule->jam_mulai,0,5) }} - {{ substr($schedule->jam_selesai,0,5) }}</td>
                        <td>
                            <strong>{{ $schedule->course->nama_matkul ?? '-' }}</strong><br>
                            <small class="text-muted">{{ $schedule->course->kode_matkul ?? '' }}</small>
                        </td>
                        <td>{{ $schedule->kelas->department->name ?? '-' }}</td>
                        <td>Semester {{ $schedule->kelas->semester ?? '-' }}</td>
                        <td>Kelas {{ $schedule->kelas->nomor_kelas ?? '-' }}</td>
                        <td>{{ $schedule->ruangan ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                {{ $schedule->kelas->mahasiswa->count() ?? 0 }} orang
                            </span>
                        </td>
                        <td>
                            @if(auth()->user()->id === $dosen->id)
                                @if($schedule->hari === $hariIni)
                                    @if($meetingsDone->contains($schedule->id))
                                        <a href="{{ route('absensi.start', $schedule) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-check-circle me-1"></i> Sudah Diabsen
                                        </a>
                                    @else
                                        <a href="{{ route('absensi.start', $schedule) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-clipboard-list me-1"></i> Mulai Absensi
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('absensi.start', $schedule) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-history me-1"></i> Lihat/Edit
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('absensi.start', $schedule) }}" class="btn btn-sm btn-outline-secondary btn-sm">
                                    <i class="fas fa-eye me-1"></i> Lihat
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                            Belum ada jadwal mengajar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
