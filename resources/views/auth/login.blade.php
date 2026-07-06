@extends('layouts.app')

@section('content')
<style>
    body {
        background: #1e3c72 !important; /* Solid dark blue matching the screenshot vibe */
    }
    
    .login-container {
        min-height: 80vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem 0;
    }

    .login-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border: none;
        width: 100%;
        max-width: 440px;
        padding: 2.5rem;
    }

    .form-label {
        font-weight: 700;
        font-size: 0.85rem;
        color: #7a829a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control-custom {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.2s ease;
        background-color: #f8fafc;
        width: 100%;
        display: block;
    }

    .form-control-custom:focus {
        border-color: #8b5cf6; /* Purple border on focus */
        background-color: #ffffff;
        outline: none;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
    }

    .password-wrapper {
        position: relative;
        width: 100%;
    }

    .password-toggle {
        position: absolute;
        right: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #8b5cf6;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        padding: 0;
        z-index: 10;
    }

    .password-toggle:hover {
        color: #6d28d9;
    }

    .captcha-box {
        background-color: #f1f5f9;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-weight: 600;
        font-size: 1.05rem;
        color: #1e293b;
        text-align: left;
        margin-bottom: 0.75rem;
    }

    .lupa-password-link {
        color: #8b5cf6;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        float: right;
        margin-bottom: 1.5rem;
        transition: color 0.2s ease;
    }

    .lupa-password-link:hover {
        color: #6d28d9;
        text-decoration: underline;
    }

    .btn-masuk-custom {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 1.1rem;
        padding: 0.85rem;
        border: none;
        border-radius: 12px;
        width: 100%;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        transition: all 0.2s ease;
        cursor: pointer;
        display: block;
        margin-top: 1rem;
    }

    .btn-masuk-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
        color: #ffffff;
    }

    .btn-masuk-custom:active {
        transform: translateY(0);
    }
</style>

<div class="login-container">
    <div class="login-card">
        <!-- Error Alerts -->
        @if ($errors->any())
            <div class="alert alert-danger p-3 mb-4 rounded-3" style="font-size: 0.9rem;">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('auth.login.submit') }}" method="POST">
            @csrf

            <!-- NIP/NIM Field -->
            <div class="mb-3">
                <label for="username" class="form-label">NIP / NIM</label>
                <input type="text" name="username" id="username" class="form-control-custom" 
                       placeholder="Masukkan NIP Dosen / NIM Mahasiswa" value="{{ old('username') }}" required autofocus>
            </div>

            <!-- Password Field -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="form-control-custom" 
                           placeholder="Masukkan password" required>
                    <button type="button" class="password-toggle" id="btnTogglePassword">Tampilkan</button>
                </div>
            </div>

            <!-- Captcha Field -->
            <div class="mb-3">
                <label class="form-label">Verifikasi</label>
                <div class="captcha-box">
                    Berapa hasil dari {{ $num1 }} + {{ $num2 }}?
                </div>
                <input type="number" name="captcha_answer" class="form-control-custom" placeholder="Jawaban" required>
            </div>

            <a href="#" class="lupa-password-link" onclick="alert('Silakan hubungi admin akademik untuk menyetel ulang kata sandi Anda.')">Lupa Password?</a>
            
            <button type="submit" class="btn-masuk-custom">
                Masuk
            </button>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted small">Belum punya akun? <a href="{{ route('auth.register') }}" class="fw-bold" style="color: #8b5cf6; text-decoration: none;">Daftar di sini</a></span>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('btnTogglePassword');

        toggleBtn.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'Sembunyikan';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'Tampilkan';
            }
        });
    });
</script>
@endsection
