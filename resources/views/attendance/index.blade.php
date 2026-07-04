@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Data Dosen</h4>
        <small class="text-muted">Kelola daftar dosen di kampus</small>
    </div>
    <a href="{{ route('dosen.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Tambah Dosen
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Nama Dosen</th>
                        <th>Kode Dosen</th>
                        <th>Program Studi</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Mata Kuliah Diajar</th>
                        <th>SKS</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($dosenList as $dsn)
                    @php
                        $taughtCourses = $dsn->lecturerCourses->pluck('course')->filter()->unique('id');
                        $totalSks      = $taughtCourses->sum('sks');
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                        <td>{{ $dsn->name }}</td>
                        <td><span class="badge bg-info text-dark">{{ $dsn->kode_dosen ?? '-' }}</span></td>
                        <td>{{ $dsn->department?->name ?? '-' }}</td>
                        <td>{{ $dsn->email }}</td>
                        <td>{{ $dsn->phone ?? '-' }}</td>
                        <td>
                            @forelse($taughtCourses as $course)
                                <span class="badge bg-light text-dark border mb-1">{{ $course->nama_matkul }}</span>
                            @empty
                                <span class="text-muted small">Belum ada matkul</span>
                            @endforelse
                        </td>
                        <td class="text-center fw-semibold">{{ $totalSks ?: '-' }}</td>
                        <td class="text-center" style="white-space: nowrap;">
                            <a href="{{ route('dosen.show', $dsn) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('dosen.edit', $dsn) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($dsn->schedules->count())
                                <button type="button" class="btn btn-sm btn-success" title="Isi Absen Mengajar"
                                        data-bs-toggle="modal" data-bs-target="#absenModal{{ $dsn->id }}">
                                    <i class="fas fa-calendar-check"></i>
                                </button>
                            @endif
                            <form action="{{ route('dosen.destroy', $dsn) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                                        onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Modal isi absen mengajar (jam & tanggal) untuk dosen ini --}}
                    @if($dsn->schedules->count())
                    <div class="modal fade" id="absenModal{{ $dsn->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('teaching-attendance.adminStore') }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Isi Absen Mengajar &mdash; {{ $dsn->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Jadwal / Mata Kuliah</label>
                                            <select name="schedule_id" class="form-select" required>
                                                @foreach($dsn->schedules as $sch)
                                                    <option value="{{ $sch->id }}">
                                                        {{ $sch->course->nama_matkul ?? '-' }} &mdash; {{ $sch->hari }} ({{ $sch->jam_mulai }}-{{ $sch->jam_selesai }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Tanggal</label>
                                                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Jam</label>
                                                <input type="time" name="jam" class="form-control" value="{{ date('H:i') }}">
                                            </div>
                                        </div>
                                        <div class="mb-3 mt-3">
                                            <label class="form-label fw-semibold">Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="hadir">Hadir</option>
                                                <option value="izin">Izin</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="tidak_hadir">Tidak Hadir</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Catatan (opsional)</label>
                                            <textarea name="catatan" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Simpan Absen
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-2"></i><br>
                            Belum ada data dosen
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $dosenList->links() }}
</div>
@endsection
