@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="mb-4">
        <h2 class="fw-bold">Selamat Datang, {{ auth()->user()->name }} 👋</h2>
        <p class="text-muted">{{ $hari }}, {{ now()->translatedFormat('d F Y') }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(isset($lowAttendanceWarnings) && count($lowAttendanceWarnings) > 0)
        <div class="alert alert-danger alert-dismissible fade show">
            <h6 class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan Kehadiran Rendah (< 75%)</h6>
            <ul class="mb-0 ps-3">
                @foreach($lowAttendanceWarnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Kartu Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-primary">{{ $totalMatkulDiampu }}</div>
                    <div class="text-muted small mt-1">📚 Mata Kuliah Diampu</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-success">{{ $totalMahasiswa }}</div>
                    <div class="text-muted small mt-1">👨‍🎓 Total Mahasiswa</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-warning">{{ $pertemuanHariIni }}</div>
                    <div class="text-muted small mt-1">📋 Pertemuan Hari Ini</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-info">{{ $persenKehadiran }}%</div>
                    <div class="text-muted small mt-1">✅ Persentase Kehadiran</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Akses Cepat --}}
    <div class="row g-3">
        <div class="col-md-6">
            <a href="{{ route('schedules.byDosen', auth()->user()) }}" class="text-decoration-none">
                <div class="card border-primary h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-calendar-alt fa-2x text-primary me-3"></i>
                        <div>
                            <h6 class="mb-0">Jadwal Mengajar</h6>
                            <small class="text-muted">Lihat jadwal & mulai absensi mahasiswa</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('exam.mySupervisions') }}" class="text-decoration-none">
                <div class="card border-info h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-user-shield fa-2x text-info me-3"></i>
                        <div>
                            <h6 class="mb-0">Jadwal Mengawas Ujian</h6>
                            <small class="text-muted">Lihat jadwal & daftar mahasiswa diawas</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('absensi.rekap') }}" class="text-decoration-none">
                <div class="card border-success h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-chart-bar fa-2x text-success me-3"></i>
                        <div>
                            <h6 class="mb-0">Rekap Kehadiran</h6>
                            <small class="text-muted">Statistik kehadiran mahasiswa</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('grades.index') }}" class="text-decoration-none">
                <div class="card border-warning h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-star fa-2x text-warning me-3"></i>
                        <div>
                            <h6 class="mb-0">Input Nilai</h6>
                            <small class="text-muted">Input nilai mahasiswa per matkul</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Jadwal & Ringkasan Kehadiran --}}
    <div class="row mt-4 g-4">
        {{-- Jadwal Mengajar Hari Ini --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-calendar-day text-primary me-2"></i> Jadwal Mengajar Hari Ini
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Jam</th>
                                    <th>Mata Kuliah</th>
                                    <th>Kelas</th>
                                    <th>Ruangan</th>
                                    <th>Mahasiswa</th>
                                    <th>Status / Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todaySchedules as $schedule)
                                <tr>
                                    <td class="fw-bold text-primary">{{ substr($schedule->jam_mulai, 0, 5) }} - {{ substr($schedule->jam_selesai, 0, 5) }}</td>
                                    <td>
                                        <span class="d-block fw-semibold text-dark">{{ $schedule->course->nama_matkul ?? '-' }}</span>
                                        <small class="text-muted">{{ $schedule->course->kode_matkul ?? '' }}</small>
                                    </td>
                                    <td>{{ $schedule->kelas->department->name ?? '-' }} (Sem {{ $schedule->kelas->semester ?? '-' }})</td>
                                    <td>
                                        @if($schedule->isOnline())
                                            <span class="badge bg-info text-white"><i class="fas fa-video me-1"></i> Online</span>
                                        @else
                                            <span class="badge bg-secondary text-white"><i class="fas fa-door-open me-1"></i> {{ $schedule->ruangan ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $schedule->kelas->mahasiswa->count() }} orang</span>
                                    </td>
                                    <td>
                                        @if($meetingsDone->contains($schedule->id))
                                            <span class="badge bg-success py-2 px-3"><i class="fas fa-check-circle me-1"></i> Sudah Diabsen</span>
                                        @else
                                            <a href="{{ route('absensi.start', $schedule) }}" class="btn btn-sm btn-primary py-2 px-3">
                                                <i class="fas fa-clipboard-list me-1"></i> Mulai Absensi
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                        Tidak ada jadwal mengajar hari ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan Kehadiran Kelas yang Diampu --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-chart-line text-success me-2"></i> Kehadiran Kelas diampu
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($classAttendanceSummary as $item)
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark small text-truncate" style="max-width: 180px;" title="{{ $item['course_name'] }}">
                                    {{ $item['course_name'] }}
                                </span>
                                <span class="badge bg-{{ $item['percentage'] >= 80 ? 'success' : ($item['percentage'] >= 60 ? 'warning' : 'danger') }}">
                                    {{ $item['percentage'] }}%
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small mb-2">
                                <span>Kelas: {{ $item['class_name'] }}</span>
                                <span>{{ $item['total_meetings'] }} Pertemuan</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-{{ $item['percentage'] >= 80 ? 'success' : ($item['percentage'] >= 60 ? 'warning' : 'danger') }}" role="progressbar" style="width: {{ $item['percentage'] }}%"></div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                            Belum ada rekap data kelas.
                        </div>
                        @endforelse
                    </div>

                    @if(array_sum($dosenAttendanceStats ?? []) > 0)
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-center mb-3">Total Komposisi Kehadiran</h6>
                        <canvas id="dosenPieChart" height="250"></canvas>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@if(array_sum($dosenAttendanceStats ?? []) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('dosenPieChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Izin', 'Sakit', 'Alpha/Tidak Hadir'],
                    datasets: [{
                        data: [
                            {{ $dosenAttendanceStats['hadir'] }},
                            {{ $dosenAttendanceStats['izin'] }},
                            {{ $dosenAttendanceStats['sakit'] }},
                            {{ $dosenAttendanceStats['tidak_hadir'] }}
                        ],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(23, 162, 184, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    });
</script>
@endif

@endsection
