@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:750px">
    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('exam.index', ['tipe' => $exam->tipe]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="mb-0 fw-bold">Edit Jadwal {{ strtoupper($exam->tipe) }}</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('exam.update', $exam) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipe Ujian</label>
                        <select name="tipe" class="form-select">
                            <option value="uts" @selected($exam->tipe=='uts')>UTS</option>
                            <option value="uas" @selected($exam->tipe=='uas')>UAS</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tahun Ajaran</label>
                        <select name="tahun_ajaran" class="form-select">
                            @foreach(['2023/2024','2024/2025','2025/2026'] as $ta)
                                <option value="{{ $ta }}" @selected($exam->tahun_ajaran==$ta)>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Periode</label>
                        <select name="periode" class="form-select">
                            <option value="ganjil" @selected($exam->periode=='ganjil')>Ganjil</option>
                            <option value="genap"  @selected($exam->periode=='genap')>Genap</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Program Studi</label>
                        <select name="department_id" class="form-select">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected($exam->department_id==$dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Semester</label>
                        <select name="semester" class="form-select">
                            @for($i=1; $i<=8; $i++)
                                <option value="{{ $i }}" @selected($exam->semester==$i)>Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Mata Kuliah</label>
                        <select name="course_id" class="form-select">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected($exam->course_id==$course->id)>
                                    {{ $course->kode_matkul }} — {{ $course->nama_matkul }} ({{ $course->sks }} SKS)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kelas</label>
                        <select name="class_id" class="form-select">
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected($exam->class_id==$kelas->id)>
                                    Kelas {{ $kelas->nomor_kelas }} ({{ $kelas->department->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control"
                               value="{{ $exam->tanggal->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="form-control"
                               value="{{ substr($exam->jam_mulai,0,5) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="form-control"
                               value="{{ substr($exam->jam_selesai,0,5) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ruangan</label>
                        <input type="text" name="ruangan" class="form-control" value="{{ $exam->ruangan }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Pengawas</label>
                        <select name="pengawas_id" class="form-select">
                            <option value="">-- Pilih Pengawas --</option>
                            @foreach($dosenList as $dosen)
                                <option value="{{ $dosen->id }}" @selected($exam->pengawas_id==$dosen->id)>
                                    {{ $dosen->kode_dosen }} — {{ $dosen->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipe Soal</label>
                        <select name="tipe_soal" class="form-select">
                            <option value="tulis"     @selected($exam->tipe_soal=='tulis')>Tulis</option>
                            <option value="online"    @selected($exam->tipe_soal=='online')>Online</option>
                            <option value="take-home" @selected($exam->tipe_soal=='take-home')>Take-Home</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2">{{ $exam->catatan }}</textarea>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                    <a href="{{ route('exam.index', ['tipe' => $exam->tipe]) }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection