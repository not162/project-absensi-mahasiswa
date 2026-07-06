<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Figtree', sans-serif;
            font-size: 1.05rem; /* Accessibility: Larger base font */
            color: #2b2d42; /* Accessibility: High contrast dark text */
        }
        
        /* Accessibility: Improve contrast for muted text */
        .text-muted {
            color: #495057 !important; 
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }

        .sidebar {
            background-color: white;
            min-height: calc(100vh - 70px);
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
        }

        .sidebar .nav-link {
            color: #2b2d42; /* High contrast */
            padding: 0.75rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #f0f0f0;
            color: #667eea;
            border-left-color: #667eea;
        }

        .main-content {
            padding: 2rem;
        }

        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #667eea;
            border-color: #667eea;
        }

        .btn-primary:hover {
            background-color: #5568d3;
            border-color: #5568d3;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }

        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>

    @stack('styles')
</head>
<body>
    @include('layouts.navbar')

    <div class="container-fluid">
        <div class="row">
            @auth
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>

                        @if(auth()->user()->role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}" href="{{ route('attendance.index') }}">
                                    <i class="fas fa-clipboard-list me-2"></i> Absensi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dosenpengajar.*') ? 'active' : '' }}" href="{{ route('dosenpengajar.index') }}">
                                    <i class="fas fa-chalkboard-teacher me-2"></i> Dosen Pengajar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <i class="fas fa-user-graduate me-2"></i> Mahasiswa
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">
                                    <i class="fas fa-book me-2"></i> Program Studi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                    <i class="fas fa-chart-bar me-2"></i> Laporan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('exam.replacement.admin.index') ? 'active' : '' }}" href="{{ route('exam.replacement.admin.index') }}">
                                    <i class="fas fa-file-invoice me-2"></i> Ujian Pengganti
                                </a>
                            </li>
                        @elseif(auth()->user()->role === 'dosen')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('schedules.byDosen') ? 'active' : '' }}" href="{{ route('schedules.byDosen', auth()->user()) }}">
                                    <i class="fas fa-calendar me-2"></i> Jadwal Saya
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('schedules.index') ? 'active' : '' }}" href="{{ route('schedules.index') }}">
                                    <i class="fas fa-calendar-alt me-2"></i> Kelola Jadwal
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('absensi.rekap') ? 'active' : '' }}" href="{{ route('absensi.rekap') }}">
                                    <i class="fas fa-chart-bar me-2"></i> Rekap Kehadiran
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('exam.*') ? 'active' : '' }}" href="{{ route('exam.index', ['tipe' => 'uts']) }}">
                                    <i class="fas fa-file-signature me-2"></i> Jadwal Ujian
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('exam.replacement.admin.index') ? 'active' : '' }}" href="{{ route('exam.replacement.admin.index') }}">
                                    <i class="fas fa-file-invoice me-2"></i> Ujian Pengganti
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dosen.modules.*') ? 'active' : '' }}" href="{{ route('dosen.modules.index') }}">
                                    <i class="fas fa-book-open me-2"></i> Modul LMS
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                                    <i class="fas fa-user-circle me-2"></i> Profile Mahasiswa
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('exam.*') ? 'active' : '' }}" href="{{ route('exam.index', ['tipe' => 'uts']) }}">
                                    <i class="fas fa-file-signature me-2"></i> Ujian
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('exam.replacement.index') ? 'active' : '' }}" href="{{ route('exam.replacement.index') }}">
                                    <i class="fas fa-file-invoice me-2"></i> Ujian Pengganti
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.rekap_absen') ? 'active' : '' }}" href="{{ route('mahasiswa.rekap_absen') }}">
                                    <i class="fas fa-history me-2"></i> Rekap Absensi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.toefl') ? 'active' : '' }}" href="{{ route('mahasiswa.toefl') }}">
                                    <i class="fas fa-language me-2"></i> Ujian TOEFL/IELTS
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.jadwal_kelas') ? 'active' : '' }}" href="{{ route('mahasiswa.jadwal_kelas') }}">
                                    <i class="fas fa-calendar-alt me-2"></i> Jadwal Kuliah & Kelas Pengganti
                                </a>
                            </li>
                        @endif

                        <hr>

                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="nav-link" style="border: none; background: none; cursor: pointer;">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
            @endauth

            <main class="col-md-9 col-lg-10 main-content">
                {{-- SWAL handles these server-side flashes now --}}
                @yield('content')
            </main>
        </div>
    </div>

    <!-- jQuery, DataTables, and SWAL JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <!-- Global SWAL Notification Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Toast config for success messages
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Aksi Dibatalkan!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#d33'
                });
            @endif

            @if ($errors->any())
                let errorHtml = '<ul class="text-start">';
                @foreach ($errors->all() as $error)
                    errorHtml += '<li>{{ $error }}</li>';
                @endforeach
                errorHtml += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Data Tidak Valid / Hilang!',
                    html: errorHtml,
                    confirmButtonColor: '#d33'
                });
            @endif

            // Global Confirmation Delete Script
            $(document).on('submit', '.form-delete', function(e) {
                e.preventDefault();
                let form = this;
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const clockElement = document.getElementById('realtime-clock');
            if(clockElement) {
                clockElement.innerHTML = `<i class="far fa-clock"></i> ${hours}:${minutes}:${seconds}`;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    <!-- Real-time Polling & Offline Cache Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Only start polling if we are on a page that needs real-time updates
            const url = window.location.href;
            if (url.includes('rekap') || url.includes('dashboard') || url.includes('attendance')) {
                let lastTimestamp = 0;
                let isFirstPoll = true;

                setInterval(() => {
                    fetch('{{ route("api.attendance.latest") }}')
                        .then(response => response.json())
                        .then(data => {
                            // 1. Cache the last 2 records into LocalStorage
                            if (data.last_two) {
                                localStorage.setItem('absensi_last_two', JSON.stringify(data.last_two));
                            }

                            // 2. Check for updates
                            if (isFirstPoll) {
                                lastTimestamp = data.latest_timestamp;
                                isFirstPoll = false;
                                return;
                            }

                             if (data.latest_timestamp > lastTimestamp) {
                                lastTimestamp = data.latest_timestamp;
                                window.location.reload();
                            }
                        })
                        .catch(err => {
                            console.error('Polling error (Server Off):', err);
                            // 3. Fallback: Show last 2 cached records when server stops
                            const cached = localStorage.getItem('absensi_last_two');
                            if (cached) {
                                try {
                                    const parsed = JSON.parse(cached);
                                    let msg = "Koneksi ke server (localhost) terputus.\n\n[TRACK RECORD TERAKHIR]:\n";
                                    parsed.forEach((item, index) => {
                                        msg += `${index + 1}. ${item.nama} - ${item.status} (${item.waktu})\n`;
                                    });
                                    
                                    // Make sure we only show this alert once per session/disconnect
                                    if (!window.offlineAlertShown) {
                                        alert(msg);
                                        window.offlineAlertShown = true;
                                    }
                                } catch (e) { }
                            }
                        });
                }, 3000); // Poll every 3 seconds
            }
        });
    </script>
    @stack('scripts')
</body>
</html>