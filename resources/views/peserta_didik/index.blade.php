@extends('layouts.app')
@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i>Home</a>
    </li>
    <li class="breadcrumb-item">Arsip</li>
    <li class="breadcrumb-item active" aria-current="page">Peserta Didik</li>
</ol>
@endsection
@section('title', 'Master Peserta Didik')
@section('favicon', asset('template/dist/images/surat-icon.png'))
@section('content')
<div class="py-2" id="page-peserta-didik">
    <div class="d-flex justify-content-between align-items-center mb-3">
        {{-- Kiri: Judul --}}
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-people-fill fs-5 text-primary"></i>
            <h5 class="fw-bold text-primary mb-0">Master Peserta Didik</h5>
        </div>
        {{-- Kanan: Tombol Reset Filter dan Tombol Tambah Peserta Didik --}}
        <div class="d-flex justify-content-end align-items-center gap-2">
            {{-- Tombol Reset Filter (ikon saja) --}}
            <button type="button" id="resetBtn" class="btn btn-light" data-bs-toggle="tooltip" title="Reset semua filter">
                <i class="bi bi-arrow-counterclockwise fs-5"></i>
            </button>
            {{-- Tombol Tambah Peserta Didik --}}
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahPesertaDidik" id="btnTambahPesertaDidik">
                <i class="bi bi-plus-lg me-1"></i> Tambah Peserta Didik
            </button>
        </div>
    </div>
    {{-- Filter --}}
    @include('peserta_didik.partials.filter')
    {{-- Table --}}
    <div id="wrapper-table-peserta_didik" class="animate-fade-in" data-base-url="{{ route('peserta-didik.index') }}">
        @include('peserta_didik.partials.table')
    </div>
    {{-- Modal --}}
    @include('peserta_didik.partials.modal')
</div>{{-- /py-2 --}}
@endsection
@push('scripts')
@endpush