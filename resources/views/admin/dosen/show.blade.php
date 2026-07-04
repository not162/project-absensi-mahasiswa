@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detail Dosen</h1>
        <div>
            <a href="{{ route('dosen.edit', $dosen) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i> Edit
            </a>
            <a href="{{ route('dosen.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Informasi Dosen</h5>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Kode Dosen</label>
                        <div class="fs-5 fw-bold">
                            <span class="badge bg-info text-dark">{{ $dosen->kode_dosen }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Nama</label>
                        <div class="fs-5 fw-bold">{{ $dosen->name }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Email</label>
                        <div><a href="mailto:{{ $dosen->email }}">{{ $dosen->email }}</a></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">No. Telepon</label>
                        <div>{{ $dosen->phone ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Program Studi</label>
                        <div>{{ $dosen->department?->name ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Terdaftar Sejak</label>
                        <div>{{ $dosen->created_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Jadwal Mengajar</h5>
                </div>
                <div class="card-body">
                    @if($dosen->schedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hari</th>
                                        <th>Jam</th>
                                        <th>Mata Kuliah</th>
                                        <th>Mode</th>
                                        <th>Lokasi/Ruangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dosen->schedules as $schedule)
                                        <tr>
                                            <td>
                                                <strong>{{ $schedule->hari }}</strong>
                                            </td>
                                            <td>
                                                {{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }}
                                            </td>
                                            <td>
                                                <strong>{{ $schedule->course->nama_matkul }}</strong><br>
                                                <small class="text-muted">({{ $schedule->course->kode_matkul }})</small>
                                            </td>
                                            <td>
                                                @if($schedule->mode === 'online')
                                                    <span class="badge bg-primary">Online</span>
                                                @else
                                                    <span class="badge bg-success">Luring</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($schedule->mode === 'online')
                                                    {{ $schedule->link_online ?? '-' }}
                                                    @if($schedule->kode_online)
                                                        <br><small>Kode: {{ $schedule->kode_online }}</small>
                                                    @endif
                                                @else
                                                    {{ $schedule->ruangan ?? '-' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <p>Belum ada jadwal mengajar</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
