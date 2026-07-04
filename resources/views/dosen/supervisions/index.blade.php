@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-user-shield text-primary me-2"></i>Jadwal Mengawas Ujian</h2>

    @forelse($examsByDate as $tanggal => $items)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white py-2">
                <i class="fas fa-calendar-day me-2"></i>
                <strong>{{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</strong>
                <span class="badge bg-light text-primary ms-2">{{ $items->count() }} ujian</span>
            </div>
            <div class="card-body">
                @foreach($items->sortBy('jam_mulai') as $exam)
                <div class="border rounded p-3 mb-2">
                    <div class="row mb-2">
                        <div class="col-md-3"><strong>Jam:</strong> {{ substr($exam->jam_mulai,0,5) }} - {{ substr($exam->jam_selesai,0,5) }}</div>
                        <div class="col-md-3"><strong>Tipe:</strong> <span class="badge bg-info">{{ strtoupper($exam->tipe) }}</span></div>
                        <div class="col-md-3"><strong>Ruangan:</strong> {{ $exam->ruangan }}</div>
                        <div class="col-md-3"><strong>Jurusan:</strong> {{ $exam->department->name ?? '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6"><strong>Mata Kuliah:</strong> {{ $exam->course->nama_matkul ?? '-' }} ({{ $exam->course->kode_matkul ?? '-' }})</div>
                        <div class="col-md-3"><strong>Kelas:</strong> Kelas {{ $exam->kelas->nomor_kelas ?? '-' }}</div>
                        <div class="col-md-3"><strong>Semester:</strong> {{ $exam->semester }}</div>
                    </div>

                    <strong>Daftar Mahasiswa yang Diawas ({{ $exam->kelas->mahasiswa->count() ?? 0 }} orang):</strong>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th width="40">#</th><th>NIM</th><th>Nama</th></tr>
                            </thead>
                            <tbody>
                                @forelse($exam->kelas->mahasiswa ?? [] as $i => $mhs)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $mhs->nim }}</td>
                                    <td>{{ $mhs->name }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada mahasiswa di kelas ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3"></i>
            <p>Belum ada jadwal mengawas ujian untuk Anda.</p>
        </div>
    @endforelse
</div>
@endsection
