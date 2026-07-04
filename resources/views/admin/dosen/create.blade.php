@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <h1>Tambah Dosen</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('dosen.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Dosen <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name') }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kode_dosen" class="form-label">Kode Dosen <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('kode_dosen') is-invalid @enderror"
                                   id="kode_dosen" name="kode_dosen"
                                   value="{{ old('kode_dosen') }}"
                                   required>
                            @error('kode_dosen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">No. Telepon</label>
                            <input type="text"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone"
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Program Studi <span class="text-danger">*</span></label>
                            <select class="form-select @error('department_id') is-invalid @enderror"
                                    id="department_id" name="department_id" required>
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester yang Diajarkan (1-8) <span class="text-danger">*</span></label>
                            <div class="text-muted mb-2">Pilih semester terlebih dahulu, lalu pilih mata kuliah yang diampu pada semester tersebut.</div>

                            <select class="form-select @error('semester') is-invalid @enderror"
                                    id="semester" name="semester" required>
                                <option value="">-- Pilih Semester --</option>
                                @for($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>
                                        Semester {{ $i }}
                                    </option>
                                @endfor
                            </select>

                            @error('semester')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mata Kuliah yang Diajarkan</label>
                            <div class="text-muted mb-2">Prodi + semester menentukan daftar mata kuliah yang muncul.</div>

                            <div id="courses-container" class="row g-3">
                                @foreach($courses as $course)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input @error('course_ids') is-invalid @enderror"
                                                   type="checkbox"
                                                   name="course_ids[]"
                                                   value="{{ $course->id }}"
                                                   id="course_{{ $course->id }}"
                                                   {{ in_array($course->id, old('course_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="course_{{ $course->id }}">
                                                {{ $course->kode_matkul }} - {{ $course->nama_matkul }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div id="courses-empty" class="text-muted" style="display:none;">Pilih Prodi dan Semester untuk menampilkan mata kuliah.</div>

                            @error('course_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>



                        <hr>

                        @push('scripts')
                        <script>
                            (function () {
                                const departmentSelect = document.getElementById('department_id');
                                const semesterSelect = document.getElementById('semester');
                                const container = document.getElementById('courses-container');
                                const emptyEl = document.getElementById('courses-empty');

                                const loadCourses = async () => {
                                    const departmentId = departmentSelect.value;
                                    const semester = semesterSelect.value;

                                    if (!departmentId || !semester) {
                                        container.innerHTML = '';
                                        emptyEl.style.display = 'block';
                                        return;
                                    }

                                    emptyEl.style.display = 'none';
                                    container.innerHTML = '<div class="col-12 text-muted">Memuat mata kuliah...</div>';

                                    const url = `{{ url('dosen/courses') }}?department_id=${encodeURIComponent(departmentId)}&semester=${encodeURIComponent(semester)}`;

                                    try {
                                        const res = await fetch(url, { method: 'GET' });
                                        const data = await res.json();

                                        container.innerHTML = '';

                                        if (!data.length) {
                                            container.innerHTML = '<div class="col-12 text-muted">Tidak ada mata kuliah untuk kombinasi ini.</div>';
                                            return;
                                        }

                                        data.forEach(c => {
                                            const col = document.createElement('div');
                                            col.className = 'col-md-6';
                                            col.innerHTML = `
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="course_ids[]" value="${c.id}" id="course_${c.id}">
                                                    <label class="form-check-label" for="course_${c.id}">${c.kode_matkul} - ${c.nama_matkul}</label>
                                                </div>
                                            `;
                                            container.appendChild(col);
                                        });
                                    } catch (e) {
                                        container.innerHTML = '<div class="col-12 text-danger">Gagal memuat mata kuliah.</div>';
                                    }
                                };

                                departmentSelect.addEventListener('change', loadCourses);
                                semesterSelect.addEventListener('change', loadCourses);

                                // initial load (kalau user sudah isi nilai lama)
                                if (departmentSelect.value && semesterSelect.value) {
                                    loadCourses();
                                } else {
                                    emptyEl.style.display = 'block';
                                }
                            })();
                        </script>
                        @endpush


                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Password dosen digunakan untuk login.
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>

                       <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Simpan
        </button>
        <a href="{{ route('dosen.index') }}" class="btn btn-secondary">
            <i class="fa fa-times"></i> Batal
        </a>
    </div>
</form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

