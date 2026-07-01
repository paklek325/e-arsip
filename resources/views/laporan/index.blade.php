@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi Surat')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i> Home</a>
    </li>
    <li class="breadcrumb-item">Arsip</li>
    @if(request()->routeIs('laporan.generate'))
        <li class="breadcrumb-item">
            <a href="{{ route('laporan.filter') }}">Laporan Rekap</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Generate</li>
    @else
        <li class="breadcrumb-item active" aria-current="page">Laporan Rekap</li>
    @endif
</ol>
@endsection

@section('content')

{{-- ========================================================
     STYLE: disamakan dengan tema export.blade.php /
     export_excel.blade.php / hasil_blade.php
     (navy #1e3a5f untuk tahunan, hijau #1a4731 untuk bulanan,
     badge bulat untuk angka, dst)
======================================================== --}}
<style>
    :root {
        --clr-masuk:          #0369a1;
        --clr-masuk-bg:       #dbeafe;
        --clr-keluar:         #047857;
        --clr-keluar-bg:      #d1fae5;
        --clr-total:          #4338ca;
        --clr-total-bg:       #ede9fe;
        --clr-header-tahunan: #1e3a5f;
        --clr-header-bulanan: #1a4731;
    }

    /* ===== Header halaman ===== */
    .laporan-page-header {
        background: linear-gradient(135deg, var(--clr-header-tahunan), #2a4d7a);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        color: #fff;
        margin-bottom: 1.5rem;
    }
    .laporan-page-header h4 {
        color: #fff !important;
        margin-bottom: .25rem;
    }
    .laporan-page-header small {
        color: rgba(255,255,255,.85) !important;
    }
    .laporan-page-header .icon-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px; height: 42px;
        border-radius: 10px;
        background: rgba(255,255,255,.15);
        margin-right: .6rem;
        font-size: 1.2rem;
    }

    /* ===== Tombol Cetak & Download ===== */
    #btn_print {
        border-radius: 8px;
        border-color: rgba(255,255,255,.5);
        color: #fff;
        background: rgba(255,255,255,.12);
        font-weight: 600;
        transition: background .15s;
    }
    #btn_print:hover {
        background: rgba(255,255,255,.25);
        color: #fff;
    }

    #download_group .btn-primary {
        background: var(--clr-total);
        border-color: var(--clr-total);
        border-radius: 8px;
        font-weight: 600;
    }
    #download_group .btn-primary:hover,
    #download_group .btn-primary:focus {
        background: #372fa3;
        border-color: #372fa3;
    }

    .download-link.dropdown-item {
        font-weight: 500;
        padding: .55rem 1rem;
    }
    .download-link.dropdown-item:hover {
        background: var(--clr-total-bg);
    }

    /* ===== Card filter & hasil ===== */
    .card-laporan {
        border-radius: 12px;
        overflow: hidden;
    }
    .card-laporan .card-header {
        border-bottom: 1px solid #e5e7eb;
        padding: .9rem 1.1rem;
    }
    .card-laporan .card-header h6 {
        color: var(--clr-header-tahunan);
        font-weight: 700;
    }
    .card-laporan .card-header h6 i {
        color: var(--clr-total);
    }

    /* aksen kiri pada card hasil, senada dengan section-sub di export */
    .card-hasil {
        border-left: 4px solid var(--clr-header-tahunan);
    }
    .card-filter {
        border-left: 4px solid var(--clr-total);
    }

    /* ===== Empty state ===== */
    .laporan-empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }
    .laporan-empty-state i {
        color: var(--clr-masuk);
        opacity: .6;
    }
    .laporan-empty-state h6 {
        color: var(--clr-header-tahunan);
        font-weight: 700;
        margin-top: .75rem;
    }
</style>

<div class="container-fluid">

{{-- Header --}}
<div class="laporan-page-header d-flex flex-wrap justify-content-between align-items-center">

    <div class="d-flex align-items-center">
        <span class="icon-circle">
            <i class="bi bi-file-earmark-bar-graph-fill"></i>
        </span>
        <div>
            <h4 class="fw-bold mb-1">
                Laporan Rekapitulasi Surat
            </h4>
            <small>
                Rekap surat masuk dan surat keluar berdasarkan periode &middot; E-Arsip SMA Babussalam
            </small>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3 mt-md-0">

        <button id="btn_print"
                type="button"
                class="btn btn-outline-light"
                style="display:none;">
            <i class="bi bi-printer-fill me-1"></i>
            Cetak
        </button>

        <div class="btn-group"
             id="download_group"
             style="display:none;">

            <button type="button"
                    class="btn btn-primary dropdown-toggle"
                    data-bs-toggle="dropdown">

                <i class="bi bi-download me-1"></i>
                Download
            </button>

            <ul class="dropdown-menu dropdown-menu-end">

                <li>
                    <a href="#"
                       class="dropdown-item download-link"
                       data-format="pdf">
                        <i class="bi bi-filetype-pdf text-danger me-2"></i>
                        PDF
                    </a>
                </li>

                <li>
                    <a href="#"
                       class="dropdown-item download-link"
                       data-format="excel">
                        <i class="bi bi-file-earmark-excel text-success me-2"></i>
                        Excel
                    </a>
                </li>

                <li>
                    <a href="#"
                       class="dropdown-item download-link"
                       data-format="word">
                        <i class="bi bi-file-earmark-word text-primary me-2"></i>
                        Word
                    </a>
                </li>

            </ul>

        </div>

    </div>

</div>

<div class="row g-4">

    {{-- Sidebar Filter --}}
    <div class="col-lg-4">

        <div class="card card-laporan card-filter shadow-sm border-0">

            <div class="card-header bg-transparent">
                <h6 class="mb-0">
                    <i class="bi bi-funnel-fill me-2"></i>
                    Filter Laporan
                </h6>
            </div>

            <div class="card-body">

                @include('laporan.partials.filter')

            </div>

        </div>

    </div>

    {{-- Hasil --}}
    <div class="col-lg-8">

        <div class="card card-laporan card-hasil shadow-sm border-0">

            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">

                <h6 class="mb-0">
                    <i class="bi bi-table me-2"></i>
                    Hasil Rekapitulasi
                </h6>

                <small class="text-muted">
                    E-Arsip SMA Babussalam
                </small>

            </div>

            <div class="card-body">

                <div id="laporan_hasil_container">

                    @if(isset($data_laporan))

                        @include('laporan.partials.hasil', [
                            'data_laporan' => $data_laporan,
                            'tipe_rekap' => $tipe_rekap ?? null,
                            'tahun' => $tahun ?? null,
                            'bulan' => $bulan ?? null,
                            'bulan_nama' => $bulan_nama ?? null
                        ])

                    @else

                        <div class="laporan-empty-state">

                            <i class="bi bi-file-earmark-text" style="font-size: 3rem;"></i>

                            <h6>
                                Belum Ada Data Laporan
                            </h6>

                            <p class="text-muted mb-0">
                                Pilih filter terlebih dahulu untuk menampilkan laporan.
                            </p>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

</div>
@endsection

@push('scripts')



@endpush




