@extends('layouts.app')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i> Home</a>
    </li>
    <li class="breadcrumb-item">Arsip</li>
    <li class="breadcrumb-item active" aria-current="page">Surat</li>
</ol>
@endsection

@section('content')
<div id="page-surat">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold text-primary mb-0">
                <i class="bi bi-envelope-fill me-2"></i>Master Surat
            </h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="resetBtn"
                    class="btn btn-light shadow-sm" title="Reset Semua Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
            <button class="btn btn-primary d-flex align-items-center gap-2"
                    data-bs-toggle="modal" data-bs-target="#addSuratModal">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Surat</span>
            </button>
        </div>
    </div>

    {{-- Filter --}}
    @include('surat.partials.filter')

    {{-- Table --}}
    <div id="tableContainer" class="animate-fade-in">
        @include('surat.partials.table')
    </div>

    {{-- Modal --}}
    @include('surat.partials.modal')

</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/surat.css') }}">
@endpush




