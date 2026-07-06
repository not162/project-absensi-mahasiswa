@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h2 class="fw-bold">Selamat Datang, {{ $user->name }} 👋</h2>
            <p class="text-muted mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stem.index') }}" class="btn btn-warning shadow-sm fw-bold">
                <i class="fas fa-brain me-1"></i> Ujian Uji Coba STEM
            </a>
            <a href="{{ route('repeats.my') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-redo me-1"></i> Ajukan Pengulangan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Widget Absen Cepat (Menampilkan kelas aktif hari ini) --}}
    @if(isset($activeMeetings) && $activeMeetings->count() > 0)
        <div class="row mb-4">
            @foreach($activeMeetings as $meeting)
            <div class="col-12">
                <div class="card border-primary shadow-sm">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <div class="mb-3 mb-md-0">
                            <h5 class="fw-bold text-primary mb-1">
                                <span class="spinner-grow spinner-grow-sm text-danger me-2" role="status"></span>
                                Sedang Berlangsung: {{ $meeting->schedule->course->nama_matkul ?? '-' }}
                            </h5>
                            <p class="text-muted mb-0">Pertemuan Ke-{{ $meeting->pertemuan_ke }} &mdash; {{ $meeting->schedule->lecturer->name ?? '-' }}</p>
                        </div>
                        <div>
                            @php
                                $alreadyCheckedIn = \App\Models\StudentAttendance::where('meeting_id', $meeting->id)
                                                    ->where('student_id', $user->id)
                                                    ->exists();
                            @endphp
                            @if($alreadyCheckedIn)
                                <a href="{{ route('mahasiswa.jadwal') }}" class="btn btn-success px-4 rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i> Sudah Absen
                                </a>
                            @else
                                <a href="{{ route('mahasiswa.jadwal') }}" class="btn btn-danger px-4 rounded-pill shadow-sm heartbeat-animation">
                                    <i class="fas fa-fingerprint me-1"></i> ABSEN SEKARANG
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <style>
            .heartbeat-animation { animation: heartbeat 1.5s infinite; }
            @keyframes heartbeat {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
        </style>
    @endif

    {{-- Kartu Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-primary">{{ $totalMatkul }}</div>
                    <div class="text-muted small mt-1">📚 Mata Kuliah Diambil</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-success">{{ $totalHadir }}</div>
                    <div class="text-muted small mt-1">✅ Hadir</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-warning">{{ $totalIzin + $totalSakit }}</div>
                    <div class="text-muted small mt-1">🟡 Izin / Sakit</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-info">{{ $persenKehadiran }}%</div>
                    <div class="text-muted small mt-1">📊 Persentase Kehadiran</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik Kehadiran per Matkul --}}
    @if(count($grafikLabels))
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>Persentase Kehadiran per Mata Kuliah</h5>
        </div>
        <div class="card-body">
            <canvas id="kehadiranChart" height="80"></canvas>
        </div>
    </div>
    @endif

    {{-- Jadwal Kuliah --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Jadwal Kuliah</h5>
            <span class="badge bg-secondary">{{ $kelas ? $kelas->department->name.' - Kelas '.$kelas->nomor_kelas.' Sem '.$kelas->semester : '-' }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Mata Kuliah</th>
                        <th>Dosen</th>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Ruangan</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center">Sakit</th>
                        <th class="text-center">Tidak Hadir</th>
                        <th class="text-center">%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalKuliah as $jadwal)
                    @php
                        $meetingIds = \App\Models\ClassMeeting::where('schedule_id', $jadwal->id)->pluck('id');
                        $absenData  = \App\Models\StudentAttendance::whereIn('meeting_id', $meetingIds)
                            ->where('student_id', $user->id)
                            ->selectRaw("
                                SUM(status='hadir') as hadir,
                                SUM(status='izin') as izin,
                                SUM(status='sakit') as sakit,
                                SUM(status='tidak_hadir') as tidak_hadir,
                                COUNT(*) as total
                            ")->first();
                        $persen = $absenData->total > 0 ? round(($absenData->hadir / $absenData->total) * 100) : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $jadwal->course->nama_matkul ?? '-' }}</strong><br>
                            <small class="text-muted">{{ $jadwal->course->kode_matkul ?? '' }}</small>
                        </td>
                        <td>{{ $jadwal->lecturer->name ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $jadwal->hari }}</span></td>
                        <td>{{ substr($jadwal->jam_mulai,0,5) }} - {{ substr($jadwal->jam_selesai,0,5) }}</td>
                        <td>{{ $jadwal->ruangan ?? '-' }}</td>
                        <td class="text-center">{{ $absenData->hadir ?? 0 }}</td>
                        <td class="text-center">{{ $absenData->izin ?? 0 }}</td>
                        <td class="text-center">{{ $absenData->sakit ?? 0 }}</td>
                        <td class="text-center">{{ $absenData->tidak_hadir ?? 0 }}</td>
                        <td class="text-center">{{ $persen }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-4 text-muted">Belum ada jadwal kuliah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Riwayat Absensi --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-clipboard-list text-primary me-2"></i>Riwayat Absensi</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Mata Kuliah</th>
                        <th>Pertemuan</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatAbsensi as $abs)
                    <tr>
                        <td>{{ $abs->meeting->schedule->course->nama_matkul ?? '-' }}</td>
                        <td>Pertemuan ke-{{ $abs->meeting->pertemuan_ke ?? '-' }}</td>
                        <td>{{ $abs->meeting->tanggal ? $abs->meeting->tanggal->format('d/m/Y') : '-' }}</td>
                        <td>
                            @php
                                $statusMap = [
                                    'hadir'       => 'Hadir',
                                    'izin'        => 'Izin',
                                    'sakit'       => 'Sakit',
                                    'tidak_hadir' => 'Tidak Hadir',
                                ];
                            @endphp
                            {{ $statusMap[$abs->status] ?? $abs->status }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat absensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@if(count($grafikLabels))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('kehadiranChart'), {
    type: 'bar',
    data: {
        labels: @json($grafikLabels),
        datasets: [{
            label: '% Kehadiran',
            data: @json($grafikData),
            backgroundColor: 'rgba(102, 126, 234, 0.7)',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } },
        plugins: { legend: { display: false } }
    }
});
</script>
@endif
@endsection
