@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:750px">
    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('exam.index', ['tipe' => $tipe]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="mb-0 fw-bold">Tambah Jadwal {{ strtoupper($tipe) }}</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('exam.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    {{-- Tipe & Periode --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipe Ujian <span class="text-danger">*</span></label>
                        <select name="tipe" class="form-select">
                            <option value="uts" @selected(old('tipe',$tipe)=='uts')>UTS</option>
                            <option value="uas" @selected(old('tipe',$tipe)=='uas')>UAS</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select name="tahun_ajaran" class="form-select">
                            @foreach(['2023/2024','2024/2025','2025/2026'] as $ta)
                                <option value="{{ $ta }}" @selected(old('tahun_ajaran','2024/2025')==$ta)>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Periode <span class="text-danger">*</span></label>
                        <select name="periode" class="form-select">
                            <option value="ganjil" @selected(old('periode')=='ganjil')>Ganjil</option>
                            <option value="genap"  @selected(old('periode')=='genap')>Genap</option>
                        </select>
                    </div>

                    {{-- Prodi & Semester --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Program Studi <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id')==$dept->id)>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select">
                            @for($i=1; $i<=8; $i++)
                                <option value="{{ $i }}" @selected(old('semester',$semester)==$i)>Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Mata Kuliah --}}
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Mata Kuliah <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(old('course_id')==$course->id)>
                                    {{ $course->kode_matkul }} — {{ $course->nama_matkul }} ({{ $course->sks }} SKS)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kelas --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select">
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected(old('class_id')==$kelas->id)>
                                    Kelas {{ $kelas->nomor_kelas }} ({{ $kelas->department->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal & Jam --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai') }}" required>
                    </div>

                    {{-- Ruangan --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="ruangan" class="form-control"
                               placeholder="cth: Gedung A - R.101" value="{{ old('ruangan') }}" required>
                    </div>

                    {{-- Pengawas (Dosen) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Pengawas</label>
                        <select name="pengawas_id" class="form-select">
                            <option value="">-- Pilih Pengawas --</option>
                            @foreach($dosenList as $dosen)
                                <option value="{{ $dosen->id }}" @selected(old('pengawas_id')==$dosen->id)>
                                    {{ $dosen->kode_dosen }} — {{ $dosen->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tipe Soal --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipe Soal <span class="text-danger">*</span></label>
                        <select name="tipe_soal" class="form-select">
                            <option value="tulis"     @selected(old('tipe_soal')=='tulis')>Tulis</option>
                            <option value="online"    @selected(old('tipe_soal')=='online')>Online</option>
                            <option value="take-home" @selected(old('tipe_soal')=='take-home')>Take-Home</option>
                        </select>
                    </div>

                    {{-- Catatan --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2"
                                  placeholder="cth: Bawa KTM, dilarang buka catatan">{{ old('catatan') }}</textarea>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                    <a href="{{ route('exam.index', ['tipe' => $tipe]) }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection