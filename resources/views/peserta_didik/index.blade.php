@extends('layouts.app')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i> Home</a>
    </li>
    <li class="breadcrumb-item">Arsip</li>
    <li class="breadcrumb-item active" aria-current="page">Peserta Didik</li>
</ol>
@endsection

@section('title', '📚 Master Peserta Didik')
@section('favicon', asset('template/dist/images/surat-icon.png'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    {{-- Kiri: Hanya Judul --}}
    <div class="d-flex align-items-center gap-2">
        <h4 class="fw-bold text-primary mb-0">📚 Master Peserta Didik</h4>
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
@endsection

@push('scripts')
@endpush

@push('styles')
<style>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush




