@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Edit Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nim" class="form-label">NIM (Nomor Induk Mahasiswa)</label>
                            <input type="text" name="nim" id="nim" 
                                   class="form-control @error('nim') is-invalid @enderror" 
                                   value="{{ old('nim', $user->nim) }}" required>
                            @error('nim')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">No. Telepon</label>
                            <input type="tel" name="phone" id="phone" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Program Studi</label>
                            <select name="department_id" id="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- TAMBAHAN: Pilihan Kelas --}}
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Semester & Kelas</label>
                            <select name="class_id" id="class_id" class="form-control @error('class_id') is-invalid @enderror">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasList->groupBy('semester') as $semester => $kelas)
                                    <optgroup label="Semester {{ $semester }}">
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}" {{ old('class_id', $user->class_id) == $k->id ? 'selected' : '' }}>
                                                Kelas {{ $k->nomor_kelas }} - {{ $k->department->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('class_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-muted">(Kosongkan jika tidak ingin mengubah)</span></label>
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror">
                            @error('password_confirmation')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection