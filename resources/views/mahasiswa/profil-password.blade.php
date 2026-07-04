@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:500px">
    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="fw-bold mb-0">Ubah Password</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('profile.updatePassword') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password Lama</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password Baru</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-key me-1"></i> Ubah Password
                    </button>
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
