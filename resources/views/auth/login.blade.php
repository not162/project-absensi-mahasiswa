@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h4 class="mb-0 text-center py-3">
                        <i class="fas fa-clipboard-check me-2"></i> Sistem Absensi
                    </h4>
                </div>
                <div class="card-body p-4">
                    <h5 class="text-center mb-4">Login</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('auth.login') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i> Email
                            </label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i> Password
                            </label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                   required>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Ingat saya
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center mb-3">
                        <p class="mb-0">Belum punya akun? <a href="{{ route('auth.register') }}" class="text-decoration-none fw-bold">Daftar di sini</a></p>
                    </div>

                    <div class="alert alert-info mb-0">
                        <small>
                            <strong>Demo Credentials:</strong><br>
                            Email: <code>admin@example.com</code><br>
                            Password: <code>password</code><br><br>
                            Atau gunakan email mahasiswa: <code>budi@example.com</code>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
