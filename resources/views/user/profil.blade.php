@extends('layouts.app')

@section('title', 'Profil Saya')

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i>Home</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Profil Saya</li>
    </ol>
@endsection

@section('content')
    <div class="py-2 profil-page-wrapper">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-circle fs-5 text-primary"></i>
                <h5 class="fw-bold text-primary mb-0">Profil Saya</h5>
            </div>
        </div>

        {{-- Alert sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" id="formProfil">
            @csrf
            @method('PUT')

            <div class="row g-4">

                {{--  Kiri: Foto  --}}
                <div class="col-md-4">
                    <div class="card h-100 text-center">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">

                            {{-- Foto preview --}}
                            <div class="position-relative mb-3 profil-photo-wrapper">
                                @php
                                    $hasFotoProfil = $user->foto && $user->foto !== 'default_staf.png';
                                    $fotoProfilUrl = $hasFotoProfil ? asset('storage/foto_admin/' . $user->foto) : asset('assets/img/default_staf.png');
                                @endphp
                                <img id="profilFotoPreview"
                                    src="{{ $fotoProfilUrl }}"
                                    onerror="this.onerror=null; this.src='{{ asset('assets/img/default_staf.png') }}';" alt="Foto Profil"
                                    class="rounded-circle border border-3 profil-photo">
                                {{-- Tombol ganti foto --}}
                                <label for="fotoInput"
                                    class="position-absolute bottom-0 end-0 btn btn-sm btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center profil-photo-btn"
                                    title="Ganti Foto">
                                    <i class="bi bi-camera-fill profil-camera-icon"></i>
                                </label>
                            </div>

                            <input type="file" id="fotoInput" name="foto" accept="image/jpeg,image/png,image/jpg"
                                class="d-none @error('foto') is-invalid @enderror">
                            @error('foto')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            <h6 class="fw-bold mb-1 mt-2">{{ $user->name }}</h6>
                            <span class="badge rounded-pill badge-role-gradient">
                                {{ $user->role->name ?? '-' }}
                            </span>

                            <small class="text-muted mt-3 profil-format-text">
                                Format JPG/PNG, maks. 4MB
                            </small>

                        </div>
                    </div>
                </div>

                {{--  Kanan: Form  --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-pencil-square text-primary me-2"></i>Edit Informasi
                            </h6>
                        </div>
                        <div class="card-body">

                            {{-- Nama --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="profil_name">
                                    Nama <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="profil_name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="profil_email">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" id="profil_email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Role (readonly) --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="profil_role">Role</label>
                                <input type="text" id="profil_role" class="form-control bg-body-secondary"
                                    value="{{ $user->role->name ?? '-' }}" readonly>
                                <div class="form-text">Role tidak dapat diubah sendiri.</div>
                            </div>

                            {{-- Password --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="profil_password">
                                    Password Baru
                                </label>
                                <div class="input-group">
                                    <input type="password" id="profil_password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Kosongkan jika tidak ingin mengubah">
                                    <button type="button" class="btn btn-outline-secondary" id="toggleProfilPassword"
                                        tabindex="-1">
                                        <i class="bi bi-eye-slash" id="toggleProfilPasswordIcon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimal 3 karakter. Kosongkan jika tidak ingin mengubah.</div>
                            </div>

                        </div>
                        <div class="card-footer d-flex justify-content-end gap-2">
                            <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        {{-- Modal Crop Foto --}}
        <div class="modal fade" id="cropProfilModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-semibold">
                            <i class="bi bi-crop me-2 text-primary"></i>Sesuaikan Foto
                        </h6>
                        <button type="button" class="btn-close" id="cancelCropProfil"></button>
                    </div>
                    <div class="modal-body text-center p-3">
                        <div class="profil-crop-container">
                            <img id="cropProfilImage" src="" alt="Crop" class="profil-crop-img">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" id="cancelCropProfil2">Batal</button>
                        <button type="button" class="btn btn-primary btn-sm" id="cropProfilConfirm">
                            <i class="bi bi-check-lg me-1"></i>Gunakan Foto Ini
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/user.css') }}?v={{ file_exists(public_path('css/user.css')) ? filemtime(public_path('css/user.css')) : '1' }}">
@endpush
@push('page-js')
  @vite(['resources/js/user.js'])
@endpush
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <style>
        /* Crop box lingkaran */
        #cropProfilModal .cropper-view-box,
        #cropProfilModal .cropper-face {
            border-radius: 50%;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
        (function() {
            'use strict';

            const fotoInput = document.getElementById('fotoInput');
            const preview = document.getElementById('profilFotoPreview');
            const cropImage = document.getElementById('cropProfilImage');
            const cropModalEl = document.getElementById('cropProfilModal');
            const cropModal = new bootstrap.Modal(cropModalEl);
            const confirmBtn = document.getElementById('cropProfilConfirm');
            const defaultSrc = '{{ asset('assets/img/default_staf.png') }}';

            let cropper = null;
            let croppedBlob = null;

            // Buka crop modal saat file dipilih
            fotoInput?.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    cropImage.src = ev.target.result;
                    cropModal.show();
                };
                reader.readAsDataURL(file);
            });

            // Init cropper saat modal terbuka
            cropModalEl?.addEventListener('shown.bs.modal', () => {
                cropper?.destroy();
                cropper = new Cropper(cropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.85,
                    restore: false,
                    guides: false,
                    center: true,
                    highlight: false,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                });
            });

            // Konfirmasi crop â†’ konversi ke Blob â†’ ganti file input
            confirmBtn?.addEventListener('click', () => {
                if (!cropper) return;
                cropper.getCroppedCanvas({
                    width: 400,
                    height: 400
                }).toBlob((blob) => {
                    croppedBlob = blob;
                    const url = URL.createObjectURL(blob);
                    preview.src = url;

                    // Ganti file di input dengan blob hasil crop
                    const dt = new DataTransfer();
                    dt.items.add(new File([blob], 'profil-foto.jpg', {
                        type: 'image/jpeg'
                    }));
                    fotoInput.files = dt.files;

                    cropModal.hide();
                }, 'image/jpeg', 0.92);
            });

            // Batal crop â†’ reset file input
            ['cancelCropProfil', 'cancelCropProfil2'].forEach(id => {
                document.getElementById(id)?.addEventListener('click', () => {
                    fotoInput.value = '';
                    cropModal.hide();
                });
            });

            // Destroy cropper saat modal ditutup
            cropModalEl?.addEventListener('hidden.bs.modal', () => {
                cropper?.destroy();
                cropper = null;
            });

            // Toggle password visibility
            const toggleBtn = document.getElementById('toggleProfilPassword');
            const toggleIcon = document.getElementById('toggleProfilPasswordIcon');
            const passInput = document.getElementById('profil_password');

            toggleBtn?.addEventListener('click', () => {
                const isPassword = passInput.type === 'password';
                passInput.type = isPassword ? 'text' : 'password';
                toggleIcon.className = isPassword ? 'bi bi-eye' : 'bi bi-eye-slash';
            });

            // Auto-dismiss alert sukses setelah 4 detik
            setTimeout(() => {
                document.querySelector('.alert-success')?.remove();
            }, 4000);

        })();
    </script>
@endpush
