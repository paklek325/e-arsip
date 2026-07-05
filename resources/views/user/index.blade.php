@extends('layouts.app')
@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i>Home</a>
    </li>
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">User</li>
</ol>
@endsection
@section('content')
<div class="py-2" id="page-user">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-people-fill fs-5 text-primary"></i>
      <h5 class="fw-bold text-primary mb-0">Master User</h5>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-1"
            data-bs-toggle="modal" data-bs-target="#addUserModal" id="addUserBtn">
      <i class="bi bi-plus-lg"></i>
      <span>Tambah User</span>
    </button>
  </div>
  {{-- ðŸ” Filter --}}
  @include('user.partials.filter')
  {{-- ðŸ“‹ Table --}}
  <div id="tableContainer" class="text-center py-5 text-muted">
    <div class="spinner-border text-primary mb-2" role="status"></div>
    <p class="mt-2">Memuat data user...</p>
  </div>
  {{-- ðŸ“¦ Modal --}}
  @include('user.partials.modal')
</div>
@endsection
@push('page-css')
<link rel="stylesheet" href="{{ asset('css/user.css') }}?v={{ filemtime(public_path('css/user.css')) }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
@endpush
@push('page-js')
  @vite(['resources/js/user.js'])
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
@endpush




