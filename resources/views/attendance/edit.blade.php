@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Edit Absensi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.update', $attendance) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Pengguna</label>
                            <input type="text" class="form-control" value="{{ $attendance->user->name }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" value="{{ $attendance->attendance_date }}" disabled>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time_in" class="form-label">Jam Masuk</label>
                                    <input type="time" name="time_in" id="time_in" 
                                           class="form-control @error('time_in') is-invalid @enderror" 
                                           value="{{ old('time_in', $attendance->time_in) }}">
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
                                           value="{{ old('time_out', $attendance->time_out) }}">
                                    @error('time_out')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="present" {{ $attendance->status == 'present' ? 'selected' : '' }}>Hadir</option>
                                <option value="late" {{ $attendance->status == 'late' ? 'selected' : '' }}>Terlambat</option>
                                <option value="absent" {{ $attendance->status == 'absent' ? 'selected' : '' }}>Absen</option>
                                <option value="sick" {{ $attendance->status == 'sick' ? 'selected' : '' }}>Sakit</option>
                                <option value="permission" {{ $attendance->status == 'permission' ? 'selected' : '' }}>Izin</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $attendance->notes) }}</textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
