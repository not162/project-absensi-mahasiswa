@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="fw-bold mb-0">{{ $assignment->judul }}</h2>
            <small class="text-muted">
                {{ $meeting->schedule->course->nama_matkul ?? '-' }} &mdash;
                Kelas {{ $meeting->schedule->kelas->nomor_kelas ?? '-' }} &mdash;
                Pertemuan ke-{{ $meeting->pertemuan_ke }}
            </small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Detail Tugas --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            @if($assignment->deskripsi)
                <p class="mb-2">{{ $assignment->deskripsi }}</p>
            @endif
            <div class="d-flex flex-wrap gap-3 align-items-center">
                @if($assignment->deadline)
                    <span class="badge {{ $assignment->isDeadlinePassed() ? 'bg-danger' : 'bg-warning text-dark' }}">
                        <i class="fas fa-clock me-1"></i>
                        Deadline: {{ $assignment->deadline->format('d/m/Y H:i') }}
                        {{ $assignment->isDeadlinePassed() ? '(Sudah lewat)' : '' }}
                    </span>
                @endif
                @if($assignment->hasFile())
                    <a href="{{ asset('storage/'.$assignment->file_tugas) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-1"></i>Unduh File Soal ({{ $assignment->file_tugas_original }})
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Ringkasan --}}
    @php
        $totalMhs   = $mahasiswa->count();
        $sudahKumpul = $submissions->count();
        $belumKumpul = $totalMhs - $sudahKumpul;
        $sudahDinilai = $submissions->filter(fn($s) => $s->sudahDinilai())->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-primary">{{ $totalMhs }}</div>
                    <div class="text-muted small mt-1">Total Mahasiswa</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-success" id="count-sudah">{{ $sudahKumpul }}</div>
                    <div class="text-muted small mt-1">Sudah Kumpul</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-danger" id="count-belum">{{ $belumKumpul }}</div>
                    <div class="text-muted small mt-1">Belum Kumpul</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-info">{{ $sudahDinilai }}</div>
                    <div class="text-muted small mt-1">Sudah Dinilai</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Mahasiswa --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="40">#</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Status</th>
                        <th>File Jawaban</th>
                        <th>Waktu Kumpul</th>
                        <th width="220">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswa as $i => $mhs)
                    @php $sub = $submissions->get($mhs->id); @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $mhs->nim ?? '-' }}</td>
                        <td>{{ $mhs->name }}</td>
                        <td>
                            @if($sub)
                                <span class="badge bg-success" id="status-badge-{{ $mhs->id }}">Sudah Kumpul</span>
                            @else
                                <span class="badge bg-secondary" id="status-badge-{{ $mhs->id }}">Belum Kumpul</span>
                            @endif
                        </td>
                        <td id="file-td-{{ $mhs->id }}">
                            @if($sub && $sub->hasFile())
                                <a href="{{ asset('storage/'.$sub->file_tugas) }}" target="_blank" class="small">
                                    <i class="fas fa-file-download me-1"></i>{{ $sub->file_tugas_original }}
                                </a>
                                @if($sub->catatan)
                                    <div class="text-muted small mt-1"><i class="fas fa-sticky-note me-1"></i>{{ $sub->catatan }}</div>
                                @endif
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="small" id="time-td-{{ $mhs->id }}">{{ $sub?->submitted_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td id="nilai-td-{{ $mhs->id }}">
                            @if($sub)
                                <form action="{{ route('assignments.nilai', $sub) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    <input type="number" name="nilai" class="form-control form-control-sm" style="width:80px"
                                           min="0" max="100" step="0.01" value="{{ $sub->nilai }}" placeholder="0-100" required>
                                    <input type="text" name="feedback" class="form-control form-control-sm"
                                           value="{{ $sub->feedback }}" placeholder="Feedback (opsional)">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">Belum bisa dinilai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada mahasiswa di kelas ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Ruang Diskusi Live --}}
    @include('components.discussion-box', ['assignment' => $assignment])

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    let assignmentId = {{ $assignment->id }};
    let countSudahKumpul = document.getElementById('count-sudah');
    let countBelumKumpul = document.getElementById('count-belum');
    let totalMhs = {{ $totalMhs }};
    
    // Polling function to update submission statuses live
    function pollSubmissions() {
        fetch(`{{ route('api.assignments.submissions', $assignment->id) }}`)
            .then(res => res.json())
            .then(data => {
                if (data.submissions) {
                    let totalSubmitted = data.total_submitted;
                    if(countSudahKumpul) countSudahKumpul.innerText = totalSubmitted;
                    if(countBelumKumpul) countBelumKumpul.innerText = totalMhs - totalSubmitted;

                    for(const [studentId, sub] of Object.entries(data.submissions)) {
                        // Cek apakah mahasiswa ini sudah ditandai "Sudah Kumpul" di UI
                        let badgeStatus = document.getElementById(`status-badge-${studentId}`);
                        if(badgeStatus && badgeStatus.innerText === 'Belum Kumpul') {
                            // Update badge
                            badgeStatus.className = 'badge bg-success';
                            badgeStatus.innerText = 'Sudah Kumpul';
                            
                            // Update file link
                            let fileTd = document.getElementById(`file-td-${studentId}`);
                            if(fileTd) {
                                fileTd.innerHTML = `
                                    <a href="${sub.file_url}" target="_blank" class="small">
                                        <i class="fas fa-file-download me-1"></i>${sub.file_name}
                                    </a>
                                    ${sub.catatan ? `<div class="text-muted small mt-1"><i class="fas fa-sticky-note me-1"></i>${sub.catatan}</div>` : ''}
                                `;
                            }
                            
                            // Update waktu kumpul
                            let timeTd = document.getElementById(`time-td-${studentId}`);
                            if(timeTd) timeTd.innerText = sub.submitted_at;
                            
                            // Update form nilai
                            let nilaiTd = document.getElementById(`nilai-td-${studentId}`);
                            if(nilaiTd && nilaiTd.innerHTML.includes('Belum bisa dinilai')) {
                                nilaiTd.innerHTML = `
                                    <form action="{{ url('/assignments/submission') }}/${studentId}/nilai" method="POST" class="d-flex gap-1">
                                        @csrf
                                        <input type="number" name="nilai" class="form-control form-control-sm" style="width:80px"
                                               min="0" max="100" step="0.01" value="" placeholder="0-100" required>
                                        <input type="text" name="feedback" class="form-control form-control-sm"
                                               value="" placeholder="Feedback (opsional)">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                `;
                            }

                            // Add a little highlight animation to show it updated live
                            let row = badgeStatus.closest('tr');
                            row.style.backgroundColor = '#e8f5e9';
                            setTimeout(() => {
                                row.style.backgroundColor = '';
                            }, 2000);
                        }
                    }
                }
            })
            .catch(err => console.error('Live submission polling error:', err));
    }
    
    setInterval(pollSubmissions, 4000); // Poll every 4 seconds
});
</script>
@endpush
