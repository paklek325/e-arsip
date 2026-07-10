@extends('layouts.app')

@section('title', 'Manajemen Kode Surat')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i>Home</a>
    </li>
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Kode Surat</li>
</ol>
@endsection

@section('content')
<div class="py-2" id="page-kode">

  {{-- ===============================
      🏷️ HEADER + TOMBOL RESET & TAMBAH
  ================================ --}}
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-upc fs-5 text-primary"></i>
      <h5 class="fw-bold text-primary mb-0">Master Kode Surat</h5>
    </div>

    <div class="d-flex align-items-center gap-2">
      {{-- 🔄 Tombol Reset (ikon) --}}
      <button type="button" class="btn btn-icon-reset" id="btnReset" title="Reset Filter">
        <i class="bi bi-arrow-counterclockwise"></i>
      </button>

      {{-- ➕ Tombol Tambah Kode --}}
      <button type="button"
              class="btn btn-primary"
              id="btnTambah"
              data-bs-toggle="modal"
              data-bs-target="#addKodeModal">
        <i class="bi bi-plus-circle"></i> Tambah Kode
      </button>
    </div>
  </div>

  {{-- ===============================
      🔍 FILTER
  ================================ --}}
  <div class="kode-filter-search mb-3">
    @include('kode.partials.filter')
  </div>

  {{-- ===============================
      📋 TABEL DATA
  ================================ --}}
  <div id="tableContainer">
    @include('kode.partials.table', ['data' => $data])
  </div>

  {{-- ===============================
      📦 MODALS (Tambah/Edit/Hapus/Detail)
  ================================ --}}
  @include('kode.partials.modal')
</div>
@endsection

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/kode.css') }}?v={{ file_exists(public_path('css/kode.css')) ? filemtime(public_path('css/kode.css')) : '1' }}">
@endpush
@push('page-js')
  @vite(['resources/js/kode.js'])
@endpush