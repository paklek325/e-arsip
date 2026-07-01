@extends('layouts.app')

@section('title', 'Manajemen Kode Surat')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i> Home</a>
    </li>
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Kode Surat</li>
</ol>
@endsection

@section('content')
<div class="container-fluid py-3" id="page-kode">
  {{-- ===============================
      🔍 FILTER + TOMBOL TAMBAH
  ================================ --}}
  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    
    {{-- 🔍 Form Filter --}}
    <div class="flex-grow-1" style="min-width: 280px;">
      @include('kode.partials.filter')
    </div>

    {{-- ➕ Tombol Tambah Kode --}}
    <div>
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





