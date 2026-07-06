<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Mahasiswa</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts (Outfit / Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            margin: 0;
            padding: 0;
            scroll-behavior: smooth;
        }

        /* Hero Section Styling */
        .hero-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #ffffff;
            padding: 2rem 0 6rem 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Navbar Styling */
        .landing-nav .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            font-size: 1.05rem;
            margin-right: 1.5rem;
            transition: color 0.3s ease;
        }

        .landing-nav .nav-link:hover,
        .landing-nav .nav-link.active {
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        /* Hero Content */
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 300;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        .btn-masuk {
            background-color: #4ea8de;
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 600;
            padding: 0.75rem 2.5rem;
            border-radius: 50px;
            border: none;
            box-shadow: 0 4px 15px rgba(78, 168, 222, 0.4);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-masuk:hover {
            background-color: #56cfe1;
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(86, 207, 225, 0.6);
        }

        /* Hero Image/Illustration */
        .hero-img-container {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-img {
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.25));
            border-radius: 12px;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Bottom Logos Row */
        .logos-section {
            background-color: #ffffff;
            padding: 2.5rem 0;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.03);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            margin-top: -3rem;
            position: relative;
            z-index: 10;
            border-radius: 16px 16px 0 0;
        }

        .logo-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .logo-item i {
            font-size: 2.25rem;
            margin-bottom: 0.5rem;
            color: #adb5bd;
            transition: color 0.3s ease;
        }

        .logo-item:hover {
            color: #1e3c72;
            transform: translateY(-3px);
        }

        .logo-item:hover i {
            color: #2a5298;
        }

        .logo-text {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
        }
        
        /* Mobile adjustments */
        @media (max-width: 991.98px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-section {
                padding-bottom: 5rem;
                text-align: center;
            }
            .hero-img-container {
                margin-top: 3rem;
            }
            .logos-section {
                margin-top: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="container">
            <!-- Navbar inside Hero -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-transparent landing-nav mb-5">
                <div class="container-fluid px-0">
                    <a class="navbar-brand d-flex align-items-center" href="#">
                        <i class="fas fa-clipboard-check me-2 fs-3 text-info"></i>
                        <span class="fw-bold fs-4 tracking-tight">Sistem Absensi</span>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link active" href="#">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#fitur">Fitur</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#kontak">Kontak Kami</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Hero Body -->
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Sistem Absensi</h1>
                    <p class="hero-subtitle">
                        Selamat datang di pembelajaran & absensi e-Learning terpadu Universitas Tangsel Raya. Nikmati kemudahan pengelolaan jadwal kuliah, UTS/UAS, tugas kuliah, dan monitoring kehadiran real-time secara terintegrasi.
                    </p>
                    <a href="{{ route('auth.login') }}" class="btn-masuk">
                        <i class="fas fa-sign-in-alt me-2"></i> Masuk
                    </a>
                </div>
                <div class="col-lg-6 hero-img-container">
                    <img src="{{ asset('images/landing_hero.png') }}" alt="Ilustrasi Absensi Digital" class="hero-img">
                </div>
            </div>
        </div>
    </header>

    <!-- Bottom Logos Row -->
    <section class="logos-section">
        <div class="container">
            <div class="row justify-content-center align-items-center g-4">
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="https://www.uniranks.com/ranking/indonesia" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="logo-item">
                            <i class="fas fa-award"></i>
                            <span class="logo-text">Akreditasi</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="https://kemdiktisaintek.go.id/en" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="logo-item">
                            <i class="fas fa-microscope"></i>
                            <span class="logo-text">DiktiSaintek</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="https://courses.maths.ox.ac.uk/" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="logo-item">
                            <i class="fas fa-globe"></i>
                            <span class="logo-text">Kampus Online</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Optional Features Section to map the links -->
    <section id="fitur" class="py-5 mt-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Fitur Utama Platform</h2>
                <p class="text-muted">Kemudahan akses akademik bagi seluruh Civitas Akademika</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 p-4 border-0 shadow-sm text-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-4 mx-auto" style="width: 70px; height: 70px;">
                            <i class="fas fa-user-check fs-3"></i>
                        </div>
                        <h4 class="fw-semibold">Absensi Real-Time</h4>
                        <p class="text-muted mb-0">Catat kehadiran mahasiswa dengan mudah secara manual melalui verifikasi lokasi di portal mahasiswa.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4 border-0 shadow-sm text-center">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mb-4 mx-auto" style="width: 70px; height: 70px;">
                            <i class="fas fa-calendar-alt fs-3"></i>
                        </div>
                        <h4 class="fw-semibold">Jadwal Perkuliahan</h4>
                        <p class="text-muted mb-0">Informasi jadwal kelas harian, jam mengajar dosen, dan alokasi ruangan ter-update setiap saat.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4 border-0 shadow-sm text-center">
                        <div class="rounded-circle bg-info bg-opacity-10 text-info d-inline-flex align-items-center justify-content-center mb-4 mx-auto" style="width: 70px; height: 70px;">
                            <i class="fas fa-file-invoice-dollar fs-3"></i>
                        </div>
                        <h4 class="fw-semibold">Monitoring Ujian & Nilai</h4>
                        <p class="text-muted mb-0">Lihat jadwal UTS/UAS terbaru, rekap nilai ujian, serta lakukan pengulangan mata kuliah langsung dari sistem.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="kontak" class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3 text-info"><i class="fas fa-university me-2"></i>Univ Tangsel Raya</h5>
                    <p class="text-white-50">Sistem informasi manajemen kehadiran mahasiswa terintegrasi untuk meningkatkan efisiensi dan transparansi kegiatan perkuliahan.</p>
                </div>
                <div class="col-lg-4 text-lg-center">
                    <h5 class="fw-bold mb-3">Hubungi Kami</h5>
                    <p class="text-white-50 mb-1"><i class="fas fa-map-marker-alt me-2 text-info"></i> Jl. Suryakencana No.1, Pamulang</p>
                    <p class="text-white-50 mb-1"><i class="fas fa-envelope me-2"></i> support@kampus.ac.id</p>
                    <p class="text-white-50 mb-3"><i class="fas fa-phone me-2"></i> +62 21-12345678</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <h5 class="fw-bold mb-3 text-start text-lg-end"><i class="fas fa-map-marked-alt me-2 text-info"></i>Lokasi Kampus</h5>
                    <div class="d-flex justify-content-lg-end">
                        <div id="public-map" class="border border-secondary rounded shadow-sm" style="width: 100%; max-width: 300px; height: 130px; z-index: 1;"></div>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center text-white-50">
                <small>&copy; 2026 Sistem Absensi Mahasiswa. All Rights Reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS & Welcome Map Initializer -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const publicMapEl = document.getElementById('public-map');
            if (publicMapEl) {
                const campusLat = {{ env('CAMPUS_LATITUDE', -6.3428) }};
                const campusLng = {{ env('CAMPUS_LONGITUDE', 106.7383) }};
                
                const publicMap = L.map('public-map', {
                    zoomControl: true,
                    attributionControl: false
                }).setView([campusLat, campusLng], 15);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(publicMap);
                
                L.marker([campusLat, campusLng]).addTo(publicMap)
                    .bindPopup('<b>Kampus Utama Univ Tangsel Raya</b>')
                    .openPopup();
            }
        });
    </script>
</body>
</html>
