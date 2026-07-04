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
            <div class="row mb-3">
                <div class="col-md-3"><small class="text-muted">Dosen</small><div>{{ $schedule->lecturer->name ?? '-' }}</div></div>
                <div class="col-md-3"><small class="text-muted">Ruangan</small><div>{{ $schedule->ruangan ?? '-' }}</div></div>
                <div class="col-md-6">
                    <small class="text-muted">Kehadiran</small>
                    <div class="d-flex gap-2 mt-1">
                        <span class="badge bg-light text-dark border">Hadir: {{ $absen->hadir ?? 0 }}</span>
                        <span class="badge bg-light text-dark border">Izin: {{ $absen->izin ?? 0 }}</span>
                        <span class="badge bg-light text-dark border">Sakit: {{ $absen->sakit ?? 0 }}</span>
                        <span class="badge bg-light text-dark border">Tidak Hadir: {{ $absen->tidak_hadir ?? 0 }}</span>
                        <span class="badge bg-light text-dark border">{{ $persen }}%</span>
                    </div>
                </div>
            </div>

            {{-- Self Check-In (hanya hari ini & pertemuan sudah dibuka dosen) --}}
            @if($isHariIni)
                @if($meeting)
                    <div class="border rounded p-3 mb-3 bg-light">
                        <strong>Pertemuan Ke-{{ $meeting->pertemuan_ke }}</strong>
                        @if($meeting->materi) <span class="text-muted ms-2">— {{ $meeting->materi }}</span> @endif

                        @if($myStatus)
                            <div class="mt-2">
                                Status absensi kamu:
                                <span class="badge bg-primary">{{ ucfirst(str_replace('_',' ',$myStatus)) }}</span>
                                <small class="text-muted ms-2">(Dosen dapat mengubah status ini)</small>
                            </div>
                            <form action="{{ route('mahasiswa.checkin') }}" method="POST" class="mt-2">
                                @csrf
                                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                <div class="d-flex gap-2 align-items-center">
                                    <select name="status" class="form-select form-select-sm w-auto">
                                        @foreach(['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','tidak_hadir'=>'Tidak Hadir'] as $val=>$label)
                                        <option value="{{ $val }}" @selected($myStatus===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Perbarui</button>
                                </div>
                            </form>
                        @else
                            <div class="mt-2">
                                <p class="mb-2 text-muted small">Pertemuan sudah dibuka — catat kehadiranmu:</p>
                                <form action="{{ route('mahasiswa.checkin') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                    <div class="d-flex gap-2 flex-wrap">
                                        @foreach(['hadir'=>['primary','Hadir'],'izin'=>['warning','Izin'],'sakit'=>['info','Sakit'],'tidak_hadir'=>['secondary','Tidak Hadir']] as $val=>[$color,$label])
                                        <button type="submit" name="status" value="{{ $val }}" class="btn btn-sm btn-{{ $color }}">
                                            {{ $label }}
                                        </button>
                                        @endforeach
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="alert alert-light border mb-3 py-2">
                        <i class="fas fa-info-circle me-1 text-muted"></i>
                        <small>Pertemuan hari ini belum dibuka dosen. Absensi akan tersedia setelah dosen membuka pertemuan.</small>
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
@endsection