@extends('layouts.app')
@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i>Home</a>
    </li>
    <li class="breadcrumb-item">Master</li>
    <li class="breadcrumb-item active" aria-current="page">Peserta Didik</li>
</ol>
@endsection
@section('title', 'Master Peserta Didik')
@section('favicon', asset('template/dist/images/surat-icon.png'))
@section('content')
<div class="py-2" id="page-peserta-didik">
    {{-- Header (Seragam dengan Master Surat) --}}
    <div class="pt-2 d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-people-fill fs-5 text-primary"></i>
            <h5 class="fw-bold text-primary mb-0">Master Peserta Didik</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="resetBtn" class="btn btn-light shadow-sm" title="Reset Semua Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahPesertaDidik" id="btnTambahPesertaDidik">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah</span>
            </button>
        </div>
    </div>
    {{-- Filter --}}
    @include('peserta_didik.partials.filter')
    {{-- Table --}}
    <div id="tableContainer" data-base-url="{{ route('peserta-didik.index') }}">
        @include('peserta_didik.partials.table')
    </div>
    {{-- Modal --}}
    @push('modals')
        @include('peserta_didik.partials.modal')
    @endpush
</div>{{-- /py-2 --}}
@endsection
@push('page-css')
<link rel="stylesheet" href="{{ asset('css/peserta-didik.css') }}?v={{ file_exists(public_path('css/peserta-didik.css')) ? filemtime(public_path('css/peserta-didik.css')) : '1' }}">
@endpush
@push('page-js')
  @vite(['resources/js/peserta-didik.js'])
@endpush