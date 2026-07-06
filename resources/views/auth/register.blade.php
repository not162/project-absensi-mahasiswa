@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <h4 class="mb-0 text-center py-3">
                        <i class="fas fa-user-plus me-2"></i> Daftar Akun (Gmail)
                    </h4>
                </div>
                <div class="card-body p-4">
                    <h5 class="text-center mb-4">Registrasi Mahasiswa Baru</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Gagal!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('auth.register.submit') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-2"></i> Nama Lengkap
                            </label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="Masukkan nama lengkap Anda" required autofocus>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">
                                <i class="fas fa-users-cog me-2 text-primary"></i> Daftar Sebagai
                            </label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Peran / Role --</option>
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="dosen" {{ old('role') == 'dosen' ? 'selected' : '' }}>Dosen Pengajar</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Program Studi Selection -->
                        <div class="mb-3" id="departmentWrapper" style="display: none;">
                            <label for="department_id" class="form-label">
                                <i class="fas fa-university me-2 text-info"></i> Program Studi
                            </label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="" disabled selected>-- Pilih Program Studi --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Semester Selection -->
                        <div class="mb-3" id="semesterWrapper" style="display: none;">
                            <label for="semester_select" class="form-label">
                                <i class="fas fa-layer-group me-2 text-success"></i> Pilih Semester
                            </label>
                            <select id="semester_select" class="form-select">
                                <option value="" disabled selected>-- Pilih Semester --</option>
                                @for($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}">Semester {{ $i }}</option>
                                @endfor
                            </select>
                            <small class="form-text text-muted">Pilih semester aktif perkuliahan Anda saat ini.</small>
                        </div>

                        <!-- Kelas Selection -->
                        <div class="mb-3" id="classWrapper" style="display: none;">
                            <label for="class_id" class="form-label">
                                <i class="fas fa-graduation-cap me-2 text-info"></i> Pilih Kelas
                            </label>
                            <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror">
                                <option value="" disabled selected>-- Pilih Kelas --</option>
                                @foreach($classes as $cls)
                                    <option value="{{ $cls->id }}" data-dept-id="{{ $cls->department_id }}" data-semester="{{ $cls->semester }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>
                                        {{ $cls->department->name }} - Semester {{ $cls->semester }} - Kelas {{ $cls->nomor_kelas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Mode Perkuliahan Info -->
                        <div class="mb-3" id="modeInfoWrapper" style="display: none;">
                            <label class="form-label">
                                <i class="fas fa-chalkboard-teacher me-2 text-warning"></i> Mode Perkuliahan
                            </label>
                            <div class="d-flex gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_perkuliahan" id="modeLuring" value="offline" checked>
                                    <label class="form-check-label" for="modeLuring">
                                        <i class="fas fa-building me-1"></i> Luring (Tatap Muka)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_perkuliahan" id="modeDaring" value="online">
                                    <label class="form-check-label" for="modeDaring">
                                        <i class="fas fa-laptop me-1"></i> Daring (Online)
                                    </label>
                                </div>
                            </div>
                            <small class="form-text text-muted">Pilih mode perkuliahan sesuai informasi dari program studi Anda.</small>
                        </div>

                        <!-- Dynamic Input Wrapper -->
                        <div class="mb-3" id="dynamicInputWrapper" style="display: none;">
                            <label for="identifier" id="dynamicLabel" class="form-label">
                                <i class="fas fa-id-card me-2 text-info"></i> <span id="labelText">Identifier</span>
                            </label>
                            <input type="text" name="identifier" id="identifier" class="form-control @error('identifier') is-invalid @enderror" 
                                   value="{{ old('identifier') }}" placeholder="Masukkan Data">
                            <small id="dynamicHelp" class="form-text text-muted"></small>
                            @error('identifier')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fab fa-google me-2 text-danger"></i> Email Gmail Pribadi
                            </label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="contoh@gmail.com" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2 text-secondary"></i> Password
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                       required minlength="6" placeholder="Minimal 6 karakter">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label text-muted small" for="terms">
                                Saya menyetujui Syarat dan Ketentuan serta Kebijakan Privasi sistem absensi ini.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2" id="btnSubmit">
                            <i class="fas fa-sign-in-alt me-2"></i> Daftar Sekarang
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Sudah punya akun? <a href="{{ route('auth.login') }}" class="text-decoration-none fw-bold">Login di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- 1. Toggle Password Visibility ---
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function (e) {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // Toggle Icon
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });

        // --- 2. Dynamic Role Input & Fields ---
        const roleSelect = document.getElementById('role');
        const dynamicWrapper = document.getElementById('dynamicInputWrapper');
        const dynamicLabel = document.getElementById('labelText');
        const dynamicInput = document.getElementById('identifier');
        const dynamicHelp = document.getElementById('dynamicHelp');
        
        const deptSelect = document.getElementById('department_id');
        const deptWrapper = document.getElementById('departmentWrapper');
        const semesterSelect = document.getElementById('semester_select');
        const semesterWrapper = document.getElementById('semesterWrapper');
        const classSelect = document.getElementById('class_id');
        const classWrapper = document.getElementById('classWrapper');
        const modeInfoWrapper = document.getElementById('modeInfoWrapper');

        const classOptions = Array.from(classSelect.options);

        function filterClasses() {
            const selectedDept = deptSelect.value;
            const selectedSemester = semesterSelect.value;

            classSelect.value = '';
            classOptions.forEach(opt => {
                if (opt.value === "") return;
                const deptId = opt.getAttribute('data-dept-id');
                const semester = opt.getAttribute('data-semester');
                const matchDept = !selectedDept || deptId === selectedDept;
                const matchSem = !selectedSemester || semester === selectedSemester;
                opt.style.display = (matchDept && matchSem) ? '' : 'none';
            });
        }

        deptSelect.addEventListener('change', function() {
            semesterSelect.value = '';
            classSelect.value = '';
            if (roleSelect.value === 'user') {
                semesterWrapper.style.display = 'block';
                classWrapper.style.display = 'none';
            }
            filterClasses();
        });

        semesterSelect.addEventListener('change', function() {
            filterClasses();
            if (roleSelect.value === 'user') {
                classWrapper.style.display = 'block';
            }
        });

        function updateForm() {
            const role = roleSelect.value;
            if (!role) {
                dynamicWrapper.style.display = 'none';
                dynamicInput.removeAttribute('required');
                deptWrapper.style.display = 'none';
                deptSelect.removeAttribute('required');
                semesterWrapper.style.display = 'none';
                classWrapper.style.display = 'none';
                classSelect.removeAttribute('required');
                modeInfoWrapper.style.display = 'none';
                return;
            }

            dynamicWrapper.style.display = 'block';
            dynamicInput.setAttribute('required', 'required');

            if (role === 'user') {
                dynamicLabel.innerText = "Nomor Induk Mahasiswa (NIM)";
                dynamicInput.placeholder = "Contoh: 20240010001";
                dynamicHelp.innerText = "NIM digunakan sebagai identitas utama pencatatan absensi kuliah.";
                
                deptWrapper.style.display = 'block';
                deptSelect.setAttribute('required', 'required');
                semesterWrapper.style.display = deptSelect.value ? 'block' : 'none';
                classWrapper.style.display = (deptSelect.value && semesterSelect.value) ? 'block' : 'none';
                classSelect.setAttribute('required', 'required');
                modeInfoWrapper.style.display = 'block';
            } else if (role === 'dosen') {
                dynamicLabel.innerText = "NIP Dosen (Nomor Induk Pegawai)";
                dynamicInput.placeholder = "Contoh: DSN-2026-001 atau DSN001";
                dynamicHelp.innerText = "NIP digunakan untuk validasi jadwal mengajar.";
                
                deptWrapper.style.display = 'block';
                deptSelect.setAttribute('required', 'required');
                semesterWrapper.style.display = 'none';
                classWrapper.style.display = 'none';
                classSelect.removeAttribute('required');
                modeInfoWrapper.style.display = 'none';
            } else if (role === 'admin') {
                dynamicLabel.innerText = "Kode Registrasi Admin";
                dynamicInput.placeholder = "Contoh: admin-2026-1234";
                dynamicHelp.innerText = "Format kode registrasi: admin-2026-nomor (Contoh: admin-2026-123).";
                
                deptWrapper.style.display = 'none';
                deptSelect.removeAttribute('required');
                semesterWrapper.style.display = 'none';
                classWrapper.style.display = 'none';
                classSelect.removeAttribute('required');
                modeInfoWrapper.style.display = 'none';
            }
            filterClasses();
        }

        roleSelect.addEventListener('change', updateForm);
        
        // Trigger once on load in case of validation errors with old input
        updateForm();
        if(deptSelect.value) {
            filterClasses();
        }
    });
</script>
@endpush

@endsection
