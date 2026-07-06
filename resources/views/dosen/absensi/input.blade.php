@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="fw-bold mb-0">Input Absensi</h2>
            <small class="text-muted">
                {{ $schedule->course->nama_matkul ?? '-' }} |
                {{ $schedule->kelas->department->name ?? '-' }} |
                Kelas {{ $schedule->kelas->nomor_kelas ?? '-' }} |
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

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Toggle Method Selection --}}
            <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-light rounded border">
                <div>
                    <h6 class="mb-1 fw-bold text-dark"><i class="fas fa-sliders-h me-1 text-primary"></i> Metode Validasi Kehadiran</h6>
                    <small class="text-muted">Pilih apakah perubahan status langsung tersimpan otomatis ke database (Asynchronous) atau harus klik tombol simpan manual (Synchronous).</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="toggleSyncMode" checked style="cursor: pointer; transform: scale(1.2);">
                    <label class="form-check-label fw-bold text-success ms-2" for="toggleSyncMode" id="toggleSyncModeLabel">Asynchronous (Auto-Save)</label>
                </div>
            </div>

            <form action="{{ route('absensi.store') }}" method="POST" id="attendanceForm">
                @csrf
                <input type="hidden" name="meeting_id" id="current_meeting_id" value="{{ $meeting->id }}">

                {{-- Materi --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Materi Pertemuan Ke-{{ $meeting->pertemuan_ke }}</label>
                    <input type="text" name="materi" class="form-control"
                           value="{{ $meeting->materi }}"
                           placeholder="cth: Bab 3 - Struktur Data Array">
                </div>

                <div class="d-flex gap-2 mb-3 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAll('hadir')">Semua Hadir</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAll('tidak_hadir')">Semua Tidak Hadir</button>
                </div>

                {{-- Tabel mahasiswa --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="50">#</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mahasiswa as $i => $mhs)
                            @php $currentStatus = $existing->get($mhs->id)?->status ?? 'hadir'; @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $mhs->nim ?? '-' }}</td>
                                <td>{{ $mhs->name }}</td>
                                <td>
                                    <div class="d-flex gap-3">
                                        @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'tidak_hadir' => 'Tidak Hadir'] as $val => $label)
                                        <div class="form-check">
                                            <input class="form-check-input btn-radio-attendance" type="radio"
                                                   name="absen[{{ $mhs->id }}]"
                                                   id="s_{{ $mhs->id }}_{{ $val }}"
                                                   value="{{ $val }}"
                                                   data-student-id="{{ $mhs->id }}"
                                                   {{ $currentStatus === $val ? 'checked' : '' }}>
                                            <label class="form-check-label" for="s_{{ $mhs->id }}_{{ $val }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @php $attModel = $existing->get($mhs->id); @endphp
                                    @if($attModel && $attModel->file_bukti)
                                        <div class="mt-2 text-start">
                                            <a href="{{ asset('storage/'.$attModel->file_bukti) }}" target="_blank" class="small badge bg-info text-decoration-none text-dark">
                                                <i class="fas fa-file-image me-1"></i>Lihat Bukti
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada mahasiswa di kelas ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($mahasiswa->count())
                <div class="border-top pt-3">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-save me-2"></i> Simpan Absensi
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- ═══════════════ TUGAS PERTEMUAN INI ═══════════════ --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <strong><i class="fas fa-tasks me-2 text-primary"></i>Tugas Pertemuan Ke-{{ $meeting->pertemuan_ke }}</strong>
        </div>
        <div class="card-body">

            {{-- Daftar tugas yang sudah dibuat untuk pertemuan ini --}}
            @php $tugasList = $meeting->assignments()->withCount('submissions')->latest()->get(); @endphp
            @if($tugasList->count())
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Judul</th>
                                <th>Deadline</th>
                                <th>File Soal</th>
                                <th>Dikumpulkan</th>
                                <th width="160"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tugasList as $tugas)
                            <tr>
                                <td>{{ $tugas->judul }}</td>
                                <td>{{ $tugas->deadline ? $tugas->deadline->format('d/m/Y H:i') : '-' }}</td>
                                <td>
                                    @if($tugas->hasFile())
                                        <a href="{{ asset('storage/'.$tugas->file_tugas) }}" target="_blank" class="small">
                                            <i class="fas fa-paperclip me-1"></i>{{ $tugas->file_tugas_original }}
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>{{ $tugas->submissions_count }} mahasiswa</td>
                                <td class="d-flex gap-1">
                                    <a href="{{ route('assignments.show', $tugas) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Lihat/Nilai
                                    </a>
                                    <form action="{{ route('assignments.destroy', $tugas) }}" method="POST"
                                          onsubmit="return confirm('Hapus tugas ini beserta seluruh file jawaban mahasiswa?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Form buat tugas baru --}}
            <button class="btn btn-sm btn-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#formTugasBaru">
                <i class="fas fa-plus me-1"></i>Buat Tugas Baru
            </button>
            <div class="collapse" id="formTugasBaru">
                <form action="{{ route('assignments.store') }}" method="POST" enctype="multipart/form-data" class="border rounded p-3 bg-light">
                    @csrf
                    <input type="hidden" name="meeting_id" value="{{ $meeting->id }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Tugas</label>
                        <input type="text" name="judul" class="form-control" required placeholder="cth: Tugas 1 - Latihan Array">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi / Instruksi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan instruksi pengerjaan tugas..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Deadline (opsional)</label>
                            <input type="datetime-local" name="deadline" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">File Soal (opsional)</label>
                            <input type="file" name="file_tugas" class="form-control"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.jpg,.jpeg,.png">
                            <small class="text-muted">Maks. 10MB (pdf, doc, xls, ppt, zip, rar, jpg, png)</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fas fa-paper-plane me-1"></i>Simpan Tugas
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
function setAll(status) {
    document.querySelectorAll(`input[type=radio][value="${status}"]`).forEach(r => {
        r.checked = true;
        // Trigger change event to auto-save if async is active
        r.dispatchEvent(new Event('change'));
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const toggleSyncMode = document.getElementById('toggleSyncMode');
    const toggleSyncModeLabel = document.getElementById('toggleSyncModeLabel');
    const saveButtonContainer = document.getElementById('attendanceForm').querySelector('.border-top');

    // Toggle visual states and save button visibility based on mode
    function updateModeUI() {
        if (toggleSyncMode.checked) {
            toggleSyncModeLabel.innerText = "Asynchronous (Auto-Save)";
            toggleSyncModeLabel.className = "form-check-label fw-bold text-success ms-2";
            if(saveButtonContainer) saveButtonContainer.style.display = 'none';
        } else {
            toggleSyncModeLabel.innerText = "Synchronous (Manual Save)";
            toggleSyncModeLabel.className = "form-check-label fw-bold text-warning ms-2";
            if(saveButtonContainer) saveButtonContainer.style.display = 'block';
        }
    }

    toggleSyncMode.addEventListener('change', updateModeUI);
    updateModeUI(); // initial run

    // Asynchronous Auto-save Listener
    document.querySelectorAll('.btn-radio-attendance').forEach(input => {
        input.addEventListener('change', function() {
            if (toggleSyncMode.checked) {
                const studentId = this.getAttribute('data-student-id');
                const statusValue = this.value;
                const meetingId = document.getElementById('current_meeting_id').value;
                const row = this.closest('tr');

                // Visual loading state
                row.style.backgroundColor = '#f1f8e9';

                fetch("{{ route('absensi.updateAsync') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        meeting_id: meetingId,
                        student_id: studentId,
                        status: statusValue
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Success flash animation
                        row.style.backgroundColor = '#e8f5e9';
                        setTimeout(() => {
                            row.style.backgroundColor = '';
                        }, 800);
                    } else {
                        row.style.backgroundColor = '#ffebee';
                        alert('Gagal memperbarui status absensi!');
                    }
                })
                .catch(err => {
                    console.error('Async save error:', err);
                    row.style.backgroundColor = '#ffebee';
                });
            }
        });
    });

    function pollMeetingAttendance() {
        const meetingId = '{{ $meeting->id }}';
        fetch(`{{ route('api.attendance.latest') }}?meeting_id=${meetingId}`)
            .then(res => res.json())
            .then(data => {
                if(data.meeting_statuses) {
                    for(const [studentId, status] of Object.entries(data.meeting_statuses)) {
                        // Find the radio button for this student and status
                        let radio = document.getElementById(`s_${studentId}_${status}`);
                        if(radio && !radio.checked) {
                            radio.checked = true;
                            // Add a little highlight animation to show it updated live
                            let row = radio.closest('tr');
                            row.style.backgroundColor = '#e8f5e9';
                            setTimeout(() => {
                                row.style.backgroundColor = '';
                            }, 1500);
                        }
                    }
                }
            })
            .catch(err => console.error('Live attendance polling error:', err));
    }
    
    // Check if we should poll (only when not auto-saving on our end, or let it sync updates from students who check in themselves)
    setInterval(pollMeetingAttendance, 4000); 
});
</script>
@endsection