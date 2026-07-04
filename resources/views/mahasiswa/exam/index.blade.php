@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-alt text-primary me-2"></i> Jadwal {{ strtoupper($tipe) }}
            </h1>
            <p class="text-muted mt-2">Tahun Ajaran: {{ $tahunAjaran }}</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="btn-group">
                <a href="{{ route('exam.index', ['tipe' => 'uts']) }}" class="btn btn-{{ $tipe === 'uts' ? 'primary' : 'outline-primary' }}">
                    Jadwal UTS
                </a>
                <a href="{{ route('exam.index', ['tipe' => 'uas']) }}" class="btn btn-{{ $tipe === 'uas' ? 'primary' : 'outline-primary' }}">
                    Jadwal UAS
                </a>
            </div>
        </div>
    </div>

    @if($examsByDate->isEmpty())
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Belum ada jadwal {{ strtoupper($tipe) }}</h5>
                <p class="mb-0">Jadwal ujian untuk kelas Anda belum dipublikasikan oleh Administrator.</p>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($examsByDate as $date => $examsOnDate)
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                            <h5 class="text-primary fw-bold mb-0">
                                <i class="fas fa-calendar-day me-2"></i> {{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Mata Kuliah</th>
                                            <th>Ruangan</th>
                                            <th>Tipe Soal</th>
                                            <th>Pengawas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($examsOnDate as $exam)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                                        <i class="far fa-clock me-1"></i> 
                                                        {{ substr($exam->jam_mulai, 0, 5) }} - {{ substr($exam->jam_selesai, 0, 5) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $exam->course->nama }}</div>
                                                    <small class="text-muted">Kode: {{ $exam->course->kode }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-door-open me-1"></i> {{ $exam->ruangan }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($exam->tipe_soal == 'tulis')
                                                        <span class="badge bg-info text-dark"><i class="fas fa-pen-nib me-1"></i> Tulis</span>
                                                    @elseif($exam->tipe_soal == 'online')
                                                        <span class="badge bg-success"><i class="fas fa-laptop me-1"></i> Online</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark"><i class="fas fa-home me-1"></i> Take-Home</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($exam->pengawas)
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: bold;">
                                                                {{ substr($exam->pengawas->name, 0, 1) }}
                                                            </div>
                                                            <span>{{ $exam->pengawas->name }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted fst-italic">Belum ditentukan</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
