<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="fas fa-clipboard-check me-2"></i> Sistem Absensi
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('dashboard') }}">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-university me-1"></i> Data Akademik
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('akademik.index') }}">
                                    <i class="fas fa-layer-group me-1"></i> Semester & Kelas
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('attendance.index') }}">
                                    <i class="fas fa-clipboard-list me-1"></i> Absensi
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('users.index') }}">
                                    <i class="fas fa-users me-1"></i> Mahasiswa
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('dosen.index') }}">
                                    <i class="fas fa-chalkboard-teacher me-1"></i> Dosen
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('schedules.index') }}">
                                    <i class="fas fa-calendar-alt me-1"></i> Jadwal Mengajar
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('exam.index', ['tipe' => 'uts']) }}">
                                    <i class="fas fa-file-alt me-1"></i> Jadwal UTS
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('exam.index', ['tipe' => 'uas']) }}">
                                    <i class="fas fa-file-alt me-1"></i> Jadwal UAS
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('grades.index') }}">
                                    <i class="fas fa-star me-1"></i> Input Nilai
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('repeats.index') }}">
                                    <i class="fas fa-redo me-1"></i> Pengajuan Pengulangan
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('teaching-attendance.history') }}">
                                    <i class="fas fa-history me-1"></i> Riwayat Absen Mengajar
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('absensi.rekapAdmin') }}">
                                    <i class="fas fa-chart-bar me-1"></i> Rekap Kehadiran Mahasiswa
                                </a></li>
                            </ul>
                        </li>
                    @elseif(auth()->user()->role === 'dosen')
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('dashboard') }}">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('schedules.byDosen', auth()->user()) }}">
                                <i class="fas fa-calendar me-1"></i> Jadwal Mengajar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('absensi.rekap') }}">
                                <i class="fas fa-clipboard-check me-1"></i> Rekap Kehadiran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('exam.mySupervisions') }}">
                                <i class="fas fa-user-shield me-1"></i> Jadwal Mengawas
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active fw-bold' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('profile.show') ? 'active fw-bold' : '' }}" href="{{ route('profile.show') }}">
                                <i class="fas fa-user-circle me-1"></i> Profile Mahasiswa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('exam.*') ? 'active fw-bold' : '' }}" href="{{ route('exam.index', ['tipe' => 'uts']) }}">
                                <i class="fas fa-file-alt me-1"></i> Ujian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('mahasiswa.toefl') ? 'active fw-bold' : '' }}" href="{{ route('mahasiswa.toefl') }}">
                                <i class="fas fa-language me-1"></i> Ujian TOEFL/IELTS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('mahasiswa.jadwal_kelas') ? 'active fw-bold' : '' }}" href="{{ route('mahasiswa.jadwal_kelas') }}">
                                <i class="fas fa-calendar-alt me-1"></i> Jadwal Kuliah & Kelas Pengganti
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3 d-none d-lg-block">
                    <span class="text-white fw-bold" id="realtime-clock">
                        <i class="far fa-clock"></i> --:--:--
                    </span>
                </li>
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit">Logout</button>
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