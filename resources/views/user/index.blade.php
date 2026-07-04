@extends('layouts.app')
@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i> Home</a>
    </li>
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">User</li>
</ol>
@endsection
@section('content')
<div class="container-fluid py-3" id="page-user">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h5 class="fw-bold mb-0" style="color:var(--text-color);">
        <i class="bi bi-people-fill text-primary me-2"></i>Master User
      </h5>
      <small class="text-muted">Kelola data pengguna aplikasi</small>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-1"
            data-bs-toggle="modal" data-bs-target="#addUserModal" id="addUserBtn">
      <i class="bi bi-plus-lg"></i>
      <span>Tambah User</span>
    </button>
  </div>
  {{-- 🔍 Filter --}}
  @include('user.partials.filter')
  {{-- 📋 Table --}}
  <div id="tableContainer" class="text-center py-5 text-muted">
    <div class="spinner-border text-primary mb-2" role="status"></div>
    <p class="mt-2">Memuat data user...</p>
  </div>
  {{-- 📦 Modal --}}
  @include('user.partials.modal')
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
/* Crop box berbentuk lingkaran */
#cropFotoModal .cropper-view-box,
#cropFotoModal .cropper-face { border-radius: 50%; }
</style>
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
@endpush




