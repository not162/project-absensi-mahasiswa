@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Input Absensi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Mahasiswa</label>
                            <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Mahasiswa --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->nim }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="attendance_date" class="form-label">Tanggal</label>
                            <input type="date" name="attendance_date" id="attendance_date" 
                                   class="form-control @error('attendance_date') is-invalid @enderror" 
                                   value="{{ old('attendance_date', date('Y-m-d')) }}" required>
                            @error('attendance_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time_in" class="form-label">Jam Masuk</label>
                                    <input type="time" name="time_in" id="time_in" 
                                           class="form-control @error('time_in') is-invalid @enderror" 
                                           value="{{ old('time_in') }}">
                                    @error('time_in')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time_out" class="form-label">Jam Keluar</label>
                                    <input type="time" name="time_out" id="time_out" 
                                           class="form-control @error('time_out') is-invalid @enderror" 
                                           value="{{ old('time_out') }}">
                                    @error('time_out')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                                <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                                <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absen</option>
                                <option value="sick" {{ old('status') == 'sick' ? 'selected' : '' }}>Sakit</option>
                                <option value="permission" {{ old('status') == 'permission' ? 'selected' : '' }}>Izin</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
