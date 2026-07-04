@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:700px">
    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="mb-0 fw-bold">Edit Jadwal Mengajar</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('schedules.update', $schedule) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mata Kuliah <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected($schedule->course_id==$course->id)>
                                    {{ $course->kode_matkul }} — {{ $course->nama_matkul }} ({{ $course->department->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Dosen <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" @selected($schedule->user_id==$dosen->id)>{{ $dosen->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                        <select name="hari" class="form-select" required>
                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                                <option value="{{ $hari }}" @selected($schedule->hari==$hari)>{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="jam_mulai" class="form-control" value="{{ substr($schedule->jam_mulai,0,5) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" name="jam_selesai" class="form-control" value="{{ substr($schedule->jam_selesai,0,5) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mode <span class="text-danger">*</span></label>
                        <select name="mode" id="modeSelect" class="form-select" required onchange="toggleMode()">
                            <option value="offline" @selected($schedule->mode=='offline')>Offline</option>
                            <option value="online" @selected($schedule->mode=='online')>Online</option>
                        </select>
                    </div>
                    <div class="col-md-8" id="ruanganField">
                        <label class="form-label fw-semibold">Ruangan</label>
                        <input type="text" name="ruangan" class="form-control" value="{{ $schedule->ruangan }}">
                    </div>
                    <div class="col-md-6" id="linkField" style="display:none">
                        <label class="form-label fw-semibold">Link Online</label>
                        <input type="url" name="link_online" class="form-control" value="{{ $schedule->link_online }}">
                    </div>
                    <div class="col-md-6" id="kodeField" style="display:none">
                        <label class="form-label fw-semibold">Kode Online</label>
                        <input type="text" name="kode_online" class="form-control" value="{{ $schedule->kode_online }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select name="tahun_ajaran" class="form-select" required>
                            @foreach(['2023/2024','2024/2025','2025/2026'] as $ta)
                                <option value="{{ $ta }}" @selected($schedule->tahun_ajaran==$ta)>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                    <a href="{{ route('schedules.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleMode() {
    const mode = document.getElementById('modeSelect').value;
    document.getElementById('ruanganField').style.display = mode === 'offline' ? '' : 'none';
    document.getElementById('linkField').style.display = mode === 'online' ? '' : 'none';
    document.getElementById('kodeField').style.display = mode === 'online' ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleMode);
</script>
@endsection
