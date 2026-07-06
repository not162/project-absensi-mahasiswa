<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            @auth
                <!-- Hamburger Menu Trigger -->
                <button class="btn btn-outline-light me-3 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
            @endauth
            <a class="navbar-brand fw-bold fs-4" href="{{ route('dashboard') }}">
                <i class="fas fa-university me-2"></i> Univ Tangsel Raya
            </a>
        </div>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left empty since links are in offcanvas -->
            <ul class="navbar-nav me-auto"></ul>
            
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3 d-none d-lg-block">
                    <a class="nav-link text-white fw-bold me-3" href="{{ route('semantic.search') }}">
                        <i class="fas fa-search me-1"></i> Cari Pintar (Vector Search)
                    </a>
                </li>
                <li class="nav-item me-3 d-none d-lg-block">
                    <span class="text-white fw-bold" id="realtime-clock">
                        <i class="far fa-clock"></i> --:--:--
                    </span>
                </li>
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white fw-bold d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            @if(auth()->user()->photo)
                                <img src="{{ asset('storage/'.auth()->user()->photo) }}" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover; border: 2px solid white;">
                            @else
                                <i class="fas fa-user-circle"></i>
                            @endif
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user-circle me-2 text-primary"></i>Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('auth.login') }}">Login</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

@auth
<!-- Offcanvas Hamburger Menu Drawer -->
<div class="offcanvas offcanvas-start bg-white text-dark" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel" style="max-width: 320px;">
    <div class="offcanvas-header border-bottom py-3">
        <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasNavbarLabel">
            <i class="fas fa-university me-2"></i> Univ Tangsel Raya
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body py-4 px-3">
        <div class="d-flex align-items-center gap-3 mb-4 px-2">
            @if(auth()->user()->photo)
                <img src="{{ asset('storage/'.auth()->user()->photo) }}" class="rounded-circle shadow-sm" style="width: 55px; height: 55px; object-fit: cover; border: 2px solid var(--bs-primary);">
            @else
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 55px; height: 55px; font-size: 22px;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 15px;">{{ auth()->user()->name }}</h6>
                <span class="badge bg-primary text-capitalize fw-semibold mt-1" style="font-size: 10px;">{{ auth()->user()->role }}</span>
            </div>
        </div>
        <hr class="my-2">
        <ul class="nav flex-column gap-1">
            @if(auth()->user()->role === 'admin')
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('dashboard') }}"><i class="fas fa-home text-primary w-20"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('profile.show') }}"><i class="fas fa-user-circle text-primary w-20"></i> Profil Saya</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('akademik.index') }}"><i class="fas fa-layer-group text-primary w-20"></i> Semester & Kelas</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('attendance.index') }}"><i class="fas fa-clipboard-list text-primary w-20"></i> Absensi</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('users.index') }}"><i class="fas fa-users text-primary w-20"></i> Mahasiswa</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('dosen.index') }}"><i class="fas fa-chalkboard-teacher text-primary w-20"></i> Dosen</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('schedules.index') }}"><i class="fas fa-calendar-alt text-primary w-20"></i> Jadwal Mengajar</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('exam.index', ['tipe' => 'uts']) }}"><i class="fas fa-file-alt text-primary w-20"></i> Jadwal UTS</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('exam.index', ['tipe' => 'uas']) }}"><i class="fas fa-file-alt text-primary w-20"></i> Jadwal UAS</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('grades.index') }}"><i class="fas fa-star text-primary w-20"></i> Input Nilai</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('repeats.index') }}"><i class="fas fa-redo text-primary w-20"></i> Pengajuan Pengulangan</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('exam.replacement.admin.index') }}"><i class="fas fa-file-invoice text-primary w-20"></i> Pengajuan Ujian Pengganti</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('teaching-attendance.history') }}"><i class="fas fa-history text-primary w-20"></i> Riwayat Absen Mengajar</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('absensi.rekapAdmin') }}"><i class="fas fa-chart-bar text-primary w-20"></i> Rekap Kehadiran Mahasiswa</a></li>
            @elseif(auth()->user()->role === 'dosen')
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('dashboard') }}"><i class="fas fa-home text-success w-20"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('profile.show') }}"><i class="fas fa-user-circle text-success w-20"></i> Profil Saya</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('schedules.byDosen', auth()->user()) }}"><i class="fas fa-calendar text-success w-20"></i> Jadwal Mengajar</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('absensi.rekap') }}"><i class="fas fa-clipboard-check text-success w-20"></i> Rekap Kehadiran</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('exam.mySupervisions') }}"><i class="fas fa-user-shield text-success w-20"></i> Jadwal Mengawas</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('exam.replacement.admin.index') }}"><i class="fas fa-file-invoice text-success w-20"></i> Ujian Pengganti</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('schedules.index') }}"><i class="fas fa-calendar-alt text-success w-20"></i> Kelola Jadwal</a></li>
            @else
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('dashboard') }}"><i class="fas fa-home text-info w-20"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('profile.show') }}"><i class="fas fa-user-circle text-info w-20"></i> Profil Saya</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('myuniv.ai') }}"><i class="fas fa-brain text-info w-20"></i> MyUniv AI Advisor</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('exam.index', ['tipe' => 'uts']) }}"><i class="fas fa-file-alt text-info w-20"></i> Ujian</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('mahasiswa.toefl') }}"><i class="fas fa-language text-info w-20"></i> Ujian TOEFL/IELTS</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('exam.replacement.index') }}"><i class="fas fa-file-invoice text-info w-20"></i> Ujian Pengganti</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('mahasiswa.rekap_absen') }}"><i class="fas fa-history text-info w-20"></i> Rekap Absensi</a></li>
                <li class="nav-item"><a class="nav-link text-dark py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('mahasiswa.jadwal_kelas') }}"><i class="fas fa-calendar-alt text-info w-20"></i> Jadwal Kuliah & Kelas Pengganti</a></li>
            @endif
            <hr class="my-2">
            <li class="nav-item"><a class="nav-link text-warning py-2 px-3 rounded d-flex align-items-center gap-2 hover-bg" href="{{ route('semantic.search') }}"><i class="fas fa-search-plus w-20"></i> Cari Pintar (Vector)</a></li>
        </ul>
    </div>
</div>

<style>
    .w-20 {
        width: 20px;
        text-align: center;
    }
    .hover-bg:hover {
        background-color: rgba(0, 0, 0, 0.05);
        color: #000 !important;
    }
</style>
@endauth