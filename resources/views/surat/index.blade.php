@extends('layouts.app')

@php
    $lockJenis = $lockJenis ?? null;
    $judulSurat = match ($lockJenis) {
        'Masuk' => 'Surat Masuk',
        'Keluar' => 'Surat Keluar',
        default => 'Master Surat',
    };
@endphp

@section('title', $judulSurat)

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i>Home</a>
        </li>
        <li class="breadcrumb-item">Master</li>
        <li class="breadcrumb-item active" aria-current="page">{{ $judulSurat }}</li>
    </ol>
@endsection

@section('content')
    <div id="page-surat">

        {{-- Header --}}
        <div class="pt-2 d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-envelope-fill text-primary"></i>
                    <h5 class="fw-bold text-primary mb-0">{{ $judulSurat }}</h5>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @unless ($lockJenis)
                    <button type="button" id="resetBtn" class="btn btn-light shadow-sm" title="Reset Semua Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                @endunless
                <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal"
                    data-bs-target="#addSuratModal">
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
