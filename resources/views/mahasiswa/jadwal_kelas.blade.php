@extends('layouts.app')

@section('content')
<style>
    .schedule-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background-color: #ffffff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .schedule-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .card-header-custom {
        padding: 1rem;
        color: #ffffff;
        text-align: center;
        min-height: 90px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .header-red {
        background-color: #d9383a !important; /* Crimson Red matching the screenshot */
    }

    .header-green {
        background-color: #198754 !important; /* Forest Green matching the screenshot */
    }

    .header-orange {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; /* Orange gradient for Replacement Class */
    }

    .card-title-custom {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
        line-height: 1.3;
    }

    .card-subtitle-custom {
        font-size: 0.8rem;
        font-weight: 500;
        opacity: 0.9;
    }

    .card-body-custom {
        padding: 1.25rem;
        font-size: 0.85rem;
        color: #2b2d42;
        flex-grow: 1;
    }

    .info-row {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .info-label {
        font-weight: 600;
        width: 110px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    .info-label i {
        width: 18px;
        margin-right: 6px;
        color: #6c757d;
    }

    .info-value {
        color: #495057;
    }

    .card-footer-custom {
        padding: 1rem 1.25rem;
        background-color: #f8fafc;
        border-top: 1px solid #edf2f7;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .btn-masuk-kelas {
        background-color: #198754;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.8rem;
        border-radius: 4px;
        padding: 0.4rem 0.8rem;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s;
    }

    .btn-masuk-kelas:hover {
        background-color: #157347;
        color: #ffffff;
    }

    .btn-icon-shortcut {
        background-color: #1a3a60;
        color: #ffffff;
        border-radius: 4px;
        padding: 0.4rem 0.6rem;
        border: none;
        margin-left: 0.25rem;
        font-size: 0.8rem;
        transition: background-color 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-icon-shortcut:hover {
        background-color: #122945;
        color: #ffffff;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-cubes text-primary me-2"></i>Jadwal Kelas</h2>
            <p class="text-muted mb-0">Daftar kelas perkuliahan aktif berdasarkan program studi.</p>
        </div>
        
        <!-- Program Studi Filter -->
        <div class="mt-3 mt-md-0" style="min-width: 250px;">
            <form action="{{ route('mahasiswa.jadwal_kelas') }}" method="GET" id="filterForm">
                <label class="form-label text-muted small fw-bold">Pilih Program Studi</label>
                <select name="department_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected($selectedDeptId == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Schedules Grid -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse($schedules as $schedule)
            @php
                // Online/Green, Offline/Red, Replacement/Orange
                $isOnline = $schedule->mode === 'online';
                $isReplacement = $schedule->is_replacement;
                $headerColorClass = $isReplacement ? 'header-orange' : ($isOnline ? 'header-green' : 'header-red');
                
                // Get lecturer code
                $kodeDosen = '-';
                if ($schedule->lecturer) {
                    if ($schedule->lecturer->kode_dosen) {
                        $kodeDosen = $schedule->lecturer->kode_dosen;
                    } else {
                        // Extract initials from name if no code
                        $words = explode(' ', preg_replace('/[^\w\s]/', '', $schedule->lecturer->name));
                        $initials = '';
                        foreach (array_slice($words, 0, 3) as $w) {
                            if (!empty($w)) $initials .= strtoupper($w[0]);
                        }
                        $kodeDosen = $initials;
                    }
                }
            @endphp
            <div class="col">
                <div class="schedule-card text-decoration-none">
                    <!-- Header -->
                    <div class="card-header-custom {{ $headerColorClass }}">
                        <div class="card-title-custom">
                            @if($isReplacement)
                                <span class="badge bg-white text-dark me-1" style="font-size: 0.7rem; vertical-align: middle;">KLS PENGGANTI</span>
                            @endif
                            {{ $schedule->course->nama_matkul ?? '-' }}
                        </div>
                        <div class="card-subtitle-custom">
                            {{ $schedule->hari }} - {{ substr($schedule->jam_mulai, 0, 5) }} - {{ substr($schedule->jam_selesai, 0, 5) }}
                        </div>
                    </div>
                    
                    <!-- Body -->
                    <div class="card-body-custom">
                        @if($isReplacement)
                        <div class="info-row">
                            <div class="info-label text-warning-emphasis"><i class="fas fa-calendar-alt text-warning"></i> Tgl Pengganti</div>
                            <div class="info-value">: <strong>{{ \Carbon\Carbon::parse($schedule->replacement_date)->format('d M Y') }}</strong></div>
                        </div>
                        @endif
                        <div class="info-row">
                            <div class="info-label"><i class="fas fa-chalkboard-teacher"></i> Kode Dosen</div>
                            <div class="info-value">: {{ $kodeDosen }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="fas fa-barcode"></i> Kode MTK</div>
                            <div class="info-value">: {{ $schedule->course->kode_matkul ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="fas fa-graduation-cap"></i> SKS</div>
                            <div class="info-value">: {{ $schedule->course->sks ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="fas fa-door-open"></i> No Ruang</div>
                            <div class="info-value">: {{ $schedule->ruangan ?? ($isOnline ? 'Online Meeting' : '-') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="fas fa-calendar-alt"></i> Kal Praktek</div>
                            <div class="info-value">: -</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="fas fa-link"></i> Kode Gabung</div>
                            <div class="info-value">: {{ $schedule->kode_online ?? '-' }}</div>
                        </div>
                    </div>

                    <!-- Footer / Buttons (Sesuai dengan screenshot) -->
                    <div class="card-footer-custom">
                        <a href="{{ route('mahasiswa.jadwal') }}" class="btn-masuk-kelas">
                            Masuk Kelas
                        </a>
                        <a href="{{ route('mahasiswa.jadwal') }}" class="btn-icon-shortcut" title="Jadwal & Absensi">
                            <i class="fas fa-calendar-alt"></i>
                        </a>
                        <a href="{{ route('grades.my') }}" class="btn-icon-shortcut" title="Nilai Ujian">
                            <i class="fas fa-folder"></i>
                        </a>
                        <a href="{{ route('profile.show') }}" class="btn-icon-shortcut" title="Informasi Profil">
                            <i class="fas fa-info-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 w-100 text-center py-5 text-muted">
                <i class="fas fa-calendar-times fa-3x mb-3 text-black-50"></i>
                <p class="mb-0">Tidak ada jadwal kelas aktif untuk Program Studi ini.</p>
                <small>Silakan pilih Program Studi lain dari menu saringan.</small>
            </div>
        @endforelse
    </div>
</div>
@endsection
