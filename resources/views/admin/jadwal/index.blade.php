@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-calendar-alt text-primary me-2"></i>Jadwal Mengajar</h2>
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('schedules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Jadwal
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        // Kelompokkan per Prodi -> Semester -> Kelas (urutan sudah dijamin dari controller)
        $byProdi = $schedules->groupBy(fn($s) => $s->kelas->department->name ?? 'Tanpa Prodi');
    @endphp

    @forelse($byProdi as $prodiName => $schedulesByProdi)
        <h4 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4">{{ $prodiName }}</h4>

        @php
            $bySemester = $schedulesByProdi->groupBy(fn($s) => $s->kelas->semester ?? '-');
        @endphp

        @foreach($bySemester as $semester => $schedulesBySemester)
            <h6 class="fw-semibold text-muted mb-2">Semester {{ $semester }}</h6>

            @php
                $byKelas = $schedulesBySemester->groupBy(fn($s) => $s->kelas->nomor_kelas ?? '-');
            @endphp

            @foreach($byKelas as $nomorKelas => $schedulesByKelas)
            <div class="mb-4">
                <div class="fw-semibold mb-1"><i class="fas fa-door-open me-1 text-secondary"></i> Kelas {{ $nomorKelas }}</div>
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Hari</th>
                                    <th>Jam</th>
                                    <th>Mata Kuliah</th>
                                    <th>Dosen</th>
                                    <th>Mode</th>
                                    <th>Ruangan / Link</th>
                                    <th>Tahun Ajaran</th>
                                    @if(auth()->user()->role === 'admin')
                                    <th>Aksi</th>
                                    @elseif(auth()->user()->role === 'dosen' && $schedule->user_id === auth()->id())
                                    <th>Ubah</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedulesByKelas as $schedule)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $schedule->hari }}</span></td>
                                    <td>{{ substr($schedule->jam_mulai,0,5) }} - {{ substr($schedule->jam_selesai,0,5) }}</td>
                                    <td>{{ $schedule->course->kode_matkul ?? '-' }} — {{ $schedule->course->nama_matkul ?? '-' }}</td>
                                    <td>{{ $schedule->lecturer->name ?? '-' }}</td>
                                    <td>
                                        @if($schedule->mode === 'online')
                                            <span class="badge bg-info">Online</span>
                                        @else
                                            <span class="badge bg-secondary">Offline</span>
                                        @endif
                                    </td>
                                    <td>{{ $schedule->lokasi }}</td>
                                    <td>{{ $schedule->tahun_ajaran }}</td>
                                    @if(auth()->user()->role === 'admin')
                                    <td class="text-nowrap">
                                        <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('schedules.destroy', $schedule) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                    @elseif(auth()->user()->role === 'dosen' && $schedule->user_id === auth()->id())
                                    <td class="text-nowrap">
                                        <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i> Ubah
                                        </a>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        @endforeach
    @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3"></i>
            <p>Belum ada jadwal mengajar.</p>
        </div>
    @endforelse
</div>
@endsection