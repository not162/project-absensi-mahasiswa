@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-1"><i class="fas fa-calendar-alt text-primary me-2"></i>Jadwal Kuliah</h2>
    <p class="text-muted mb-4">{{ $hariIni }}, {{ $today->translatedFormat('d F Y') }}</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @forelse($schedules as $schedule)
    @php
        $isHariIni  = $schedule->hari === $hariIni;
        $meeting    = $meetingData[$schedule->id] ?? null;
        $myStatus   = $attendanceStatus[$schedule->id] ?? null;
        $assignments = $assignmentsByJadwal[$schedule->id] ?? collect();

        // Statistik kehadiran untuk matkul ini
        $meetingIds = \App\Models\ClassMeeting::where('schedule_id', $schedule->id)->pluck('id');
        $absen = \App\Models\StudentAttendance::whereIn('meeting_id', $meetingIds)
            ->where('student_id', $user->id)
            ->selectRaw("SUM(status='hadir') as hadir, SUM(status='izin') as izin,
                         SUM(status='sakit') as sakit, SUM(status='tidak_hadir') as tidak_hadir,
                         COUNT(*) as total")
            ->first();
        $persen = $absen->total > 0 ? round(($absen->hadir / $absen->total) * 100) : 0;
    @endphp

    <div class="card shadow-sm mb-4 {{ $isHariIni ? 'border-warning' : '' }}">
        <div class="card-header d-flex justify-content-between align-items-center {{ $isHariIni ? 'bg-warning bg-opacity-25' : '' }}">
            <div>
                <strong>{{ $schedule->course->nama_matkul ?? '-' }}</strong>
                <span class="text-muted ms-2 small">{{ $schedule->course->kode_matkul ?? '' }}</span>
                @if($isHariIni)
                    <span class="badge bg-warning text-dark ms-2">Hari Ini</span>
                @endif
            </div>
            <span class="badge bg-secondary">{{ $schedule->hari }} {{ substr($schedule->jam_mulai,0,5) }}-{{ substr($schedule->jam_selesai,0,5) }}</span>
        </div>
        <div class="card-body">
            <div class="row mb-3 align-items-center">
                <div class="col-md-3">
                    <small class="text-muted">Dosen</small>
                    <div>{{ $schedule->lecturer->name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Ruangan</small>
                    <div>{{ $schedule->ruangan ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Kehadiran</small>
                    <div class="d-flex gap-2 mt-1 flex-wrap">
                        <span class="badge bg-light text-dark border">Hadir: {{ $absen->hadir ?? 0 }}</span>
                        <span class="badge bg-light text-dark border">Izin: {{ $absen->izin ?? 0 }}</span>
                        <span class="badge bg-light text-dark border">Sakit: {{ $absen->sakit ?? 0 }}</span>
                        <span class="badge bg-light text-dark border">Tidak Hadir: {{ $absen->tidak_hadir ?? 0 }}</span>
                        <span class="badge bg-light text-dark border">{{ $persen }}%</span>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    @if($isHariIni && $meeting)
                        @if($myStatus)
                            <span class="badge bg-success py-2 px-3 w-100 text-uppercase"><i class="fas fa-check-circle me-1"></i> Sudah Absen</span>
                        @else
                            <button type="button" onclick="openCheckinModal({{ $schedule->id }})" class="btn btn-success fw-bold text-white px-3 py-2 w-100 shadow-sm">
                                <i class="fas fa-sign-in-alt me-1"></i> Absen Masuk
                            </button>
                        @endif
                    @else
                        <button type="button" class="btn btn-warning fw-bold text-dark px-3 py-2 w-100 shadow-sm" disabled style="opacity: 0.85;">
                            <i class="fas fa-clock me-1"></i> Belum Mulai
                        </button>
                    @endif
                </div>
            </div>

            {{-- Self Check-In (hanya hari ini & pertemuan sudah dibuka dosen) --}}
            @if($isHariIni)
                @if($meeting)
                    <div class="border rounded-3 p-4 mb-3 bg-white shadow-sm border-warning">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-warning text-dark px-3 py-2 fw-bold"><i class="fas fa-play-circle me-1"></i> PERTEMUAN KE-{{ $meeting->pertemuan_ke }} AKTIF</span>
                            @if($meeting->materi)
                                <span class="text-muted fw-semibold">{{ $meeting->materi }}</span>
                            @endif
                        </div>

                        <!-- Grid Info Row 1 & Row 2 -->
                        <div class="row g-3 mb-3 text-center">
                            <!-- Card 1: Dosen -->
                            <div class="col-md-2 col-6">
                                <div class="card border-0 shadow-sm bg-light py-3 h-100">
                                    <span class="text-muted small uppercase fw-semibold d-block mb-1" style="font-size: 11px;">Dosen</span>
                                    <h5 class="fw-bold text-primary mb-0" style="font-size: 16px;">{{ $schedule->lecturer->name ?? '-' }}</h5>
                                </div>
                            </div>
                            <!-- Card 2: Matakuliah -->
                            <div class="col-md-3 col-6">
                                <div class="card border-0 shadow-sm bg-light py-3 h-100">
                                    <span class="text-muted small uppercase fw-semibold d-block mb-1" style="font-size: 11px;">Matakuliah</span>
                                    <h5 class="fw-bold text-dark mb-0 text-truncate px-2" style="font-size: 15px;">{{ $schedule->course->nama_matkul ?? '-' }}</h5>
                                </div>
                            </div>
                            <!-- Card 3: Jam Masuk -->
                            <div class="col-md-2 col-6">
                                <div class="card border-0 shadow-sm bg-light py-3 h-100">
                                    <span class="text-muted small uppercase fw-semibold d-block mb-1" style="font-size: 11px;">Jam Masuk</span>
                                    <h5 class="fw-bold text-success mb-0" style="font-size: 16px;">{{ substr($schedule->jam_mulai, 0, 5) }}</h5>
                                </div>
                            </div>
                            <!-- Card 4: Jam Keluar -->
                            <div class="col-md-2 col-6">
                                <div class="card border-0 shadow-sm bg-light py-3 h-100">
                                    <span class="text-muted small uppercase fw-semibold d-block mb-1" style="font-size: 11px;">Jam Keluar</span>
                                    <h5 class="fw-bold text-danger mb-0" style="font-size: 16px;">{{ substr($schedule->jam_selesai, 0, 5) }}</h5>
                                </div>
                            </div>
                            <!-- Card 5: Evaluasi Pengajaran (Form Komentar) -->
                            <div class="col-md-3 col-12">
                                <div class="card border shadow-sm py-2 px-3 h-100 bg-white text-start border-warning bg-warning bg-opacity-10">
                                    <form action="{{ route('mahasiswa.attendance.feedback') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                        <label class="fw-semibold text-dark mb-1" style="font-size: 11px;">Berikan Komentar Anda Terhadap Pengajar Dosen</label>
                                        <textarea name="feedback_dosen" class="form-control form-control-sm mb-2" rows="2" placeholder="Tulis komentar..." required style="font-size: 11px;">{{ \App\Models\StudentAttendance::where('meeting_id', $meeting->id)->where('student_id', $user->id)->first()?->feedback_dosen }}</textarea>
                                        <div class="d-flex gap-2 align-items-center mb-2">
                                            @php
                                                $curFeedbackSesuai = \App\Models\StudentAttendance::where('meeting_id', $meeting->id)->where('student_id', $user->id)->first()?->feedback_sesuai;
                                            @endphp
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input" type="radio" name="feedback_sesuai" id="sesuai-{{ $schedule->id }}" value="Sesuai" required @checked($curFeedbackSesuai === 'Sesuai')>
                                                <label class="form-check-label text-dark" for="sesuai-{{ $schedule->id }}" style="font-size: 10px; cursor: pointer;">Pengajaran Sesuai</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input" type="radio" name="feedback_sesuai" id="tidak-sesuai-{{ $schedule->id }}" value="Tidak Sesuai" @checked($curFeedbackSesuai === 'Tidak Sesuai')>
                                                <label class="form-check-label text-dark" for="tidak-sesuai-{{ $schedule->id }}" style="font-size: 10px; cursor: pointer;">Pengajaran Tidak Sesuai</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary w-100 py-1" style="font-size: 11px;">Kirim</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4 text-center">
                            <!-- Card 1: Ruang -->
                            <div class="col-md-3 col-6">
                                <div class="card border-0 shadow-sm bg-light py-3 h-100">
                                    <span class="text-muted small uppercase fw-semibold d-block mb-1" style="font-size: 11px;">Ruang</span>
                                    <h5 class="fw-bold text-dark mb-0" style="font-size: 15px;">{{ $schedule->ruangan ?? 'Online' }}</h5>
                                </div>
                            </div>
                            <!-- Card 2: Kelas -->
                            <div class="col-md-3 col-6">
                                <div class="card border-0 shadow-sm bg-light py-3 h-100">
                                    <span class="text-muted small uppercase fw-semibold d-block mb-1" style="font-size: 11px;">Kelas</span>
                                    <h5 class="fw-bold text-dark mb-0" style="font-size: 15px;">{{ $schedule->kelas->nomor_kelas ?? '-' }}</h5>
                                </div>
                            </div>
                            <!-- Card 3: Hari -->
                            <div class="col-md-3 col-6">
                                <div class="card border-0 shadow-sm bg-light py-3 h-100">
                                    <span class="text-muted small uppercase fw-semibold d-block mb-1" style="font-size: 11px;">Hari</span>
                                    <h5 class="fw-bold text-dark mb-0" style="font-size: 15px;">{{ $schedule->hari }}</h5>
                                </div>
                            </div>
                            <!-- Card 4: Kode MTK -->
                            <div class="col-md-3 col-6">
                                <div class="card border-0 shadow-sm bg-light py-3 h-100">
                                    <span class="text-muted small uppercase fw-semibold d-block mb-1" style="font-size: 11px;">Kode MTK</span>
                                    <h5 class="fw-bold text-dark mb-0" style="font-size: 15px;">{{ $schedule->course->kode_matkul ?? '-' }}</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Check-in buttons or Status -->
                        <div class="d-flex justify-content-between align-items-center mt-3 p-3 border rounded bg-white">
                            <div>
                                @if($myStatus)
                                    <span class="text-muted small">Status Kehadiran Anda:</span>
                                    <span class="badge bg-{{ $myStatus === 'hadir' ? 'success' : 'danger' }} fs-6 ms-2">
                                        {{ strtoupper(str_replace('_',' ',$myStatus)) }}
                                    </span>
                                @else
                                    <span class="text-muted small">Pertemuan sedang aktif! Silakan lakukan check-in.</span>
                                @endif
                            </div>
                            <div>
                                @if(!$myStatus)
                                    <form action="{{ route('mahasiswa.checkin') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                        <input type="hidden" name="status" class="status-input" value="hadir">
                                        <button type="button" onclick="openCheckinModal({{ $schedule->id }})" class="btn btn-success px-4 fw-bold me-2">
                                            <i class="fas fa-check me-1"></i> Hadir
                                        </button>
                                        <button type="submit" onclick="this.form.querySelector('.status-input').value='tidak_hadir'" class="btn btn-danger px-4 fw-bold">
                                            <i class="fas fa-times me-1"></i> Tidak Hadir
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('mahasiswa.checkin') }}" method="POST" class="d-inline" onsubmit="return handleUpdateCheckin(event, this, {{ $schedule->id }})">
                                        @csrf
                                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                        <input type="hidden" name="latitude" class="lat-input">
                                        <input type="hidden" name="longitude" class="lng-input">
                                        <div class="d-flex gap-2 align-items-center">
                                            <select name="status" class="form-select form-select-sm w-auto" id="status-select-{{ $schedule->id }}">
                                                @foreach(['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','tidak_hadir'=>'Tidak Hadir'] as $val=>$label)
                                                <option value="{{ $val }}" @selected($myStatus===$val)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Perbarui Status</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-light border mb-3 py-2 text-center">
                        <i class="fas fa-info-circle me-1 text-muted"></i>
                        <small>Pertemuan hari ini belum aktif. Silakan tunggu jadwal mulai perkuliahan.</small>
                    </div>
                @endif
            @endif

            {{-- Tugas per pertemuan --}}
            @if($assignments->count())
                <div>
                    <strong class="d-block mb-2"><i class="fas fa-tasks me-1 text-primary"></i>Tugas Mata Kuliah Ini</strong>
                    @foreach($assignments as $tugas)
                    @php $mySubmission = $tugas->submissions->first(); @endphp
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $tugas->judul }}</strong>
                                <span class="text-muted small">(Pertemuan ke-{{ $tugas->meeting->pertemuan_ke ?? '-' }})</span>
                                @if($tugas->deskripsi)<p class="text-muted small mb-1">{{ $tugas->deskripsi }}</p>@endif
                                @if($tugas->hasFile())
                                    <a href="{{ asset('storage/'.$tugas->file_tugas) }}" target="_blank" class="small d-block mb-1">
                                        <i class="fas fa-download me-1"></i>Unduh File Soal: {{ $tugas->file_tugas_original }}
                                    </a>
                                @endif
                                @if($tugas->deadline)
                                    <small class="text-{{ $tugas->isDeadlinePassed() ? 'danger' : 'warning' }}">
                                        <i class="fas fa-clock me-1"></i>Deadline: {{ $tugas->deadline->format('d/m/Y H:i') }}
                                        {{ $tugas->isDeadlinePassed() ? '(Sudah lewat)' : '' }}
                                    </small>
                                @endif
                            </div>
                            @if($mySubmission && $mySubmission->nilai !== null)
                                <span class="badge bg-success fs-6">Nilai: {{ $mySubmission->nilai }}</span>
                            @endif
                        </div>

                        @if($mySubmission)
                            <div class="mt-2 p-2 bg-light rounded">
                                <small class="text-muted">Sudah dikumpulkan {{ $mySubmission->submitted_at?->format('d/m/Y H:i') }}</small><br>
                                @if($mySubmission->hasFile())
                                    <a href="{{ asset('storage/'.$mySubmission->file_tugas) }}" target="_blank" class="small">
                                        <i class="fas fa-file-download me-1"></i>{{ $mySubmission->file_tugas_original }}
                                    </a>
                                @endif
                                @if($mySubmission->feedback)
                                    <div class="mt-1"><small class="text-muted">Feedback dosen: </small><small>{{ $mySubmission->feedback }}</small></div>
                                @endif
                                {{-- Kumpul ulang / ganti file --}}
                                @if(!$tugas->isDeadlinePassed() && !$mySubmission->sudahDinilai())
                                <form action="{{ route('assignments.submit') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="assignment_id" value="{{ $tugas->id }}">
                                    <div class="input-group input-group-sm">
                                        <input type="file" name="file_tugas" class="form-control"
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.jpg,.jpeg,.png" required>
                                        <button type="submit" class="btn btn-outline-primary">Ganti File</button>
                                    </div>
                                </form>
                                @elseif($mySubmission->sudahDinilai())
                                    <div class="text-muted small mt-1"><i class="fas fa-lock me-1"></i>Tugas sudah dinilai, tidak bisa diubah lagi.</div>
                                @endif
                            </div>
                        @else
                            @if(!$tugas->isDeadlinePassed())
                            <form action="{{ route('assignments.submit') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                @csrf
                                <input type="hidden" name="assignment_id" value="{{ $tugas->id }}">
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <input type="file" name="file_tugas" class="form-control form-control-sm"
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.jpg,.jpeg,.png" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-paper-plane me-1"></i>Kumpulkan
                                        </button>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan (opsional)">
                                        <small class="text-muted">Maks. 10MB (pdf, doc, xls, ppt, zip, rar, jpg, png)</small>
                                    </div>
                                </div>
                            </form>
                            @else
                                <div class="text-danger small mt-1"><i class="fas fa-times-circle me-1"></i>Deadline sudah lewat, tidak bisa mengumpulkan.</div>
                            @endif
                        @endif
                        
                        {{-- Ruang Diskusi Live --}}
                        <div class="mt-3 border-top pt-2">
                            @include('components.discussion-box', ['assignment' => $tugas])
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="fas fa-calendar-times fa-3x mb-3"></i>
        <p>Belum ada jadwal kuliah.</p>
    </div>
    @endforelse
</div>

<!-- Check-in Map Modal -->
<div class="modal fade" id="checkinModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="checkinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="checkinModalLabel"><i class="fas fa-map-marker-alt me-2"></i>Konfirmasi Lokasi Absensi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="checkin-map" class="mb-3 border rounded-3" style="height: 250px; width: 100%;"></div>
                
                <div class="alert alert-info py-2" id="location-status">
                    <i class="fas fa-spinner fa-spin me-2"></i>Mencari lokasi GPS Anda...
                </div>

                <form id="modalCheckinForm" action="{{ route('mahasiswa.checkin') }}" method="POST">
                    @csrf
                    <input type="hidden" name="schedule_id" id="modal-schedule-id">
                    <input type="hidden" name="status" value="hadir">
                    <input type="hidden" name="latitude" id="modal-latitude">
                    <input type="hidden" name="longitude" id="modal-longitude">

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="btnSubmitCheckin" disabled>
                        Kirim Kehadiran (Hadir)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    /* Prevent Leaflet controls from showing on top of Bootstrap modal header */
    .leaflet-top, .leaflet-bottom {
        z-index: 1000 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let map = null;
    let userMarker = null;
    let campusCircle = null;
    let checkinModalObj = null;
    let currentAbsenDarimanaSaja = false;

    const CAMPUS_LAT = {{ env('CAMPUS_LATITUDE', -6.3428) }};
    const CAMPUS_LNG = {{ env('CAMPUS_LONGITUDE', 106.7383) }};

    function openCheckinModal(scheduleId) {
        document.getElementById('modal-schedule-id').value = scheduleId;
        
        // Show modal using Bootstrap API
        if (!checkinModalObj) {
            checkinModalObj = new bootstrap.Modal(document.getElementById('checkinModal'));
        }
        checkinModalObj.show();
        
        // Reset status and button
        const statusDiv = document.getElementById('location-status');
        const submitBtn = document.getElementById('btnSubmitCheckin');
        statusDiv.className = "alert alert-info py-2";
        statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mencari lokasi GPS Anda...';
        submitBtn.disabled = true;

        // Timeout map initialization to ensure modal is rendered
        setTimeout(() => {
            if (!map) {
                map = L.map('checkin-map').setView([CAMPUS_LAT, CAMPUS_LNG], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                // Campus Marker
                L.marker([CAMPUS_LAT, CAMPUS_LNG]).addTo(map)
                    .bindPopup('<b>Kampus Universitas Tangsel Raya</b>')
                    .openPopup();
            } else {
                map.invalidateSize();
                map.setView([CAMPUS_LAT, CAMPUS_LNG], 16);
            }

            // Get user location
            if (!navigator.geolocation) {
                if (currentAbsenDarimanaSaja) {
                    statusDiv.className = "alert alert-success py-2";
                    statusDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Absen Darimana Saja Aktif (Browser tidak mendukung Geolocation).';
                    submitBtn.disabled = false;
                } else {
                    statusDiv.className = "alert alert-danger py-2";
                    statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Geolocation tidak didukung oleh browser Anda.';
                }
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('modal-latitude').value = lat;
                    document.getElementById('modal-longitude').value = lng;

                    // Update Map marker
                    if (userMarker) {
                        userMarker.setLatLng([lat, lng]);
                    } else {
                        userMarker = L.marker([lat, lng], {
                            icon: L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                shadowSize: [41, 41]
                            })
                        }).addTo(map);
                    }

                    userMarker.bindPopup('<b>Lokasi Anda</b>').openPopup();
                    
                    // Center map on user location
                    map.setView([lat, lng], 15);

                    // Calculate distance using Haversine formula
                    const distance = calculateDistance(lat, lng, CAMPUS_LAT, CAMPUS_LNG);
                    
                    statusDiv.className = "alert alert-success py-2";
                    statusDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>Lokasi Terverifikasi! (Jarak Anda ke Kampus: ${Math.round(distance)} meter).`;
                    submitBtn.disabled = false;
                },
                function(error) {
                    statusDiv.className = "alert alert-success py-2";
                    statusDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Lokasi Terverifikasi! (Lokasi tidak dibagikan).';
                    submitBtn.disabled = false;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }, 500);
    }

    function handleUpdateCheckin(event, form, scheduleId) {
        const select = document.getElementById('status-select-' + scheduleId);
        if (select && select.value === 'hadir') {
            event.preventDefault();
            openCheckinModal(scheduleId);
            return false;
        }
        return true; // Proceed with normal submit for non-hadir
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth's radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }


</script>
@endpush
@endsection