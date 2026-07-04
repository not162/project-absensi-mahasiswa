@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Dashboard Admin Akademik</h1>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('dosen.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i> Tambah Dosen
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">{{ $totalUsers }}</div>
                <div>👨‍🎓 Total Mahasiswa</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">{{ $totalDosen }}</div>
                <div>👨‍🏫 Total Dosen</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">{{ $totalCourses }}</div>
                <div>📚 Mata Kuliah</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">{{ $todayTotalAttendance }}</div>
                <div>✅ Total Absensi Hari Ini</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Statistik Kehadiran (7 Hari Terakhir)</h5>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="90"></canvas>
                </div>
            </div>
        </div>
    </div>


    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Dosen Pengajar</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Kode Dosen</th>
                                    <th style="width: 25%">Nama Dosen</th>
                                    <th style="width: 25%">Prodi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($lecturers ?? collect()) as $lecturer)
                                    <tr>
                                        <td>{{ $lecturer->kode_dosen ?? ($lecturer->lecturer->kode_dosen ?? '-') }}</td>
                                        <td>{{ $lecturer->name ?? ($lecturer->lecturer->name ?? '-') }}</td>
                                        <td>{{ $lecturer->department?->name ?? ($lecturer->lecturer->department?->name ?? '-') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data dosen pengajar</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-8">
            <div class="card">

                <div class="card-header">
                    <h5 class="mb-0">Absensi Terakhir</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendances as $attendance)
                                    <tr>
                                        <td>{{ $attendance->user->name }}</td>
                                        <td>{{ $attendance->attendance_date->format('d M Y') }}</td>
                                        <td>{{ $attendance->time_in ?? '-' }}</td>
                                        <td>{{ $attendance->time_out ?? '-' }}</td>
                                        <td>
                                            @if($attendance->status === 'present')
                                                <span class="badge bg-success">Hadir</span>
                                            @elseif($attendance->status === 'late')
                                                <span class="badge bg-warning">Terlambat</span>
                                            @elseif($attendance->status === 'absent')
                                                <span class="badge bg-danger">Absen</span>
                                            @elseif($attendance->status === 'sick')
                                                <span class="badge bg-info">Sakit</span>
                                            @else
                                                <span class="badge bg-secondary">Izin</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Ringkasan Hari Ini</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Hadir</span>
                            <span class="badge bg-success">{{ $todayPresent }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Terlambat</span>
                            <span class="badge bg-warning">{{ $todayLate }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Absen</span>
                            <span class="badge bg-danger">{{ $todayAbsent }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Akses Cepat</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('akademik.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-university me-2 text-primary"></i> Data Akademik
                    </a>
                    <a href="{{ route('schedules.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i> Jadwal Mengajar
                    </a>
                    <a href="{{ route('exam.index', ['tipe'=>'uts']) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt me-2 text-primary"></i> Jadwal UTS/UAS
                    </a>
                    <a href="{{ route('grades.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-star me-2 text-warning"></i> Input Nilai
                    </a>
                    <a href="{{ route('repeats.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-redo me-2 text-primary"></i> Pengajuan Pengulangan</span>
                        @if($totalPendingRepeats)
                            <span class="badge bg-danger rounded-pill">{{ $totalPendingRepeats }}</span>
                        @endif
                    </a>
                    <a href="{{ route('absensi.rekapAdmin') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2 text-primary"></i> Rekap Kehadiran Mahasiswa
                    </a>
                </div>
            </div>

            @if($totalPendingRepeats)
            <div class="card mt-3 border-warning">
                <div class="card-header bg-warning-subtle d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-redo me-2"></i>Pengajuan Pengulangan Terbaru</h6>
                    <span class="badge bg-danger">{{ $totalPendingRepeats }} pending</span>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($pendingRepeats as $repeat)
                    <a href="{{ route('repeats.index', ['status' => 'pending']) }}"
                       class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $repeat->student->name ?? '-' }}</strong>
                            <small class="text-muted">{{ $repeat->created_at->diffForHumans() }}</small>
                        </div>
                        <small class="text-muted">{{ $repeat->course->nama_matkul ?? '-' }}</small>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('attendanceChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($weeklyLabels),
            datasets: [
                {
                    label: 'Statistik Kehadiran',
                    data: @json($weeklyPresent),
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderRadius: 6,
                },
                {
                    label: 'Statistik Ketidakhadiran',
                    data: @json($weeklyAbsent),
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
</script>
@endsection
