@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:600px">
    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="fw-bold mb-0">Edit Profil</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="delete_photo" id="deletePhotoInput" value="0">

                <div class="text-center mb-4">
                    <div id="avatarContainer" class="position-relative mx-auto mb-2" style="width:100px;height:100px;">
                        <img id="profileImagePreview" src="{{ $user->photo ? asset('storage/'.$user->photo) : '' }}" 
                             class="rounded-circle {{ $user->photo ? '' : 'd-none' }}"
                             style="width:100px;height:100px;object-fit:cover;">
                        <div id="profileInitialPreview" class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto {{ $user->photo ? 'd-none' : '' }}"
                             style="width:100px;height:100px">
                            <span class="text-white fw-bold" id="initialLetter" style="font-size:40px">{{ strtoupper(substr($user->name,0,1)) }}</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <label class="btn btn-sm btn-outline-secondary mb-0 cursor-pointer">
                            <i class="fas fa-camera me-1"></i> Ganti Foto
                            <input type="file" name="photo" id="photoInput" accept="image/png, image/jpeg, image/jpg" class="d-none">
                        </label>
                        <button type="button" id="btnDeletePhoto" class="btn btn-sm btn-outline-danger {{ $user->photo ? '' : 'd-none' }}">
                            <i class="fas fa-trash me-1"></i> Hapus Foto
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor HP</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="08xx...">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const photoInput = document.getElementById('photoInput');
        const profileImagePreview = document.getElementById('profileImagePreview');
        const profileInitialPreview = document.getElementById('profileInitialPreview');
        const deletePhotoInput = document.getElementById('deletePhotoInput');
        const btnDeletePhoto = document.getElementById('btnDeletePhoto');

        // Real-time Preview on File Selection
        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check if file is image and size is less than 2MB
                if (!file.type.startsWith('image/')) {
                    alert('Berkas harus berupa gambar (png, jpg, jpeg)!');
                    this.value = '';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran berkas tidak boleh melebihi 2MB!');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                    profileImagePreview.classList.remove('d-none');
                    profileInitialPreview.classList.add('d-none');
                    deletePhotoInput.value = '0';
                    btnDeletePhoto.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        // Hapus Foto Handler
        btnDeletePhoto.addEventListener('click', function() {
            profileImagePreview.classList.add('d-none');
            profileImagePreview.src = '';
            profileInitialPreview.classList.remove('d-none');
            photoInput.value = '';
            deletePhotoInput.value = '1';
            btnDeletePhoto.classList.add('d-none');
        });
    });
</script>
@endpush
@endsection
