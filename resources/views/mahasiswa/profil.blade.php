@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:600px">
    <h2 class="fw-bold mb-4"><i class="fas fa-user-circle text-primary me-2"></i>Profil Saya</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body text-center py-4">
            {{-- Foto --}}
            @if($user->photo)
                <img src="{{ asset('storage/'.$user->photo) }}" class="rounded-circle mb-3"
                     style="width:120px;height:120px;object-fit:cover;">
            @else
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:120px;height:120px">
                    <span class="text-white fw-bold" style="font-size:48px">{{ strtoupper(substr($user->name,0,1)) }}</span>
                </div>
            @endif
            <h4 class="fw-bold">{{ $user->name }}</h4>
            <span class="badge bg-primary px-3 py-2 mb-2">
                @if($user->role === 'admin')
                    Administrator
                @elseif($user->role === 'dosen')
                    Dosen Pengajar
                @else
                    Mahasiswa
                @endif
            </span>
            <p class="text-muted mb-0">
                @if($user->role === 'user')
                    NIM: {{ $user->nim ?? '-' }}
                @elseif($user->role === 'dosen')
                    NIP: {{ $user->kode_dosen ?? '-' }}
                @else
                    ID Admin: #{{ $user->id }}
                @endif
            </p>
        </div>
        <div class="card-body border-top">
            <table class="table table-borderless mb-0">
                @if($user->role === 'user')
                    <tr>
                        <td class="text-muted fw-semibold" width="40%">Program Studi</td>
                        <td>{{ $user->department->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Semester</td>
                        <td>{{ $user->kelas->semester ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Kelas</td>
                        <td>{{ $user->kelas ? 'Kelas '.$user->kelas->nomor_kelas : '-' }}</td>
                    </tr>
                @elseif($user->role === 'dosen')
                    <tr>
                        <td class="text-muted fw-semibold" width="40%">NIP Dosen</td>
                        <td>{{ $user->kode_dosen ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Mata Kuliah Diampu</td>
                        <td>
                            @if($user->coursesTaught && count($user->coursesTaught) > 0)
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($user->coursesTaught as $course)
                                        <span class="badge bg-secondary">{{ $course->nama_matkul }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">Belum ditugaskan</span>
                            @endif
                        </td>
                    </tr>
                @elseif($user->role === 'admin')
                    <tr>
                        <td class="text-muted fw-semibold" width="40%">Hak Akses</td>
                        <td><span class="text-danger fw-bold"><i class="fas fa-shield-alt me-1"></i> Full Access (Super-Admin)</span></td>
                    </tr>
                @endif
                <tr>
                    <td class="text-muted fw-semibold">Email</td>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <td class="text-muted fw-semibold">Nomor HP</td>
                    <td>{{ $user->phone ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="card-footer d-flex gap-2">
            <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit Profil
            </a>
            <a href="{{ route('profile.password') }}" class="btn btn-outline-secondary">
                <i class="fas fa-key me-1"></i> Ubah Password
            </a>
        </div>
    </div>
</div>
@endsection
