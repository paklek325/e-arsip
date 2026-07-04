@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi Surat')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i> Home</a>
    </li>
    <li class="breadcrumb-item">Arsip</li>
    <li class="breadcrumb-item active" aria-current="page">Laporan Rekap</li>
</ol>
@endsection

@section('content')
<div class="container-fluid py-3" id="page-laporan">

    {{-- Page Header --}}
    <div class="laporan-page-header d-flex flex-wrap justify-content-between align-items-center gap-2">

        <div class="d-flex align-items-center gap-2">
            <span class="icon-circle">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
            </span>
            <div>
                <h5 class="fw-bold mb-0">Laporan Rekapitulasi Surat</h5>
                <small>Rekap surat masuk &amp; keluar berdasarkan periode &middot; E-Arsip SMA Babussalam</small>
            </div>
        </div>

        <div class="d-flex gap-2 flex-shrink-0">
            <button id="btn_print" type="button" class="btn btn-outline-light btn-sm" style="display:none;">
                <i class="bi bi-printer-fill me-1"></i> Cetak
            </button>
            <div class="btn-group" id="download_group" style="display:none;">
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Download
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a href="#" class="dropdown-item download-link" data-format="pdf">
                            <i class="bi bi-filetype-pdf text-danger me-2"></i> PDF
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item download-link" data-format="excel">
                            <i class="bi bi-file-earmark-excel text-success me-2"></i> Excel
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item download-link" data-format="word">
                            <i class="bi bi-file-earmark-word text-primary me-2"></i> Word
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>

    <div class="row g-3">

        {{-- Sidebar Filter --}}
        <div class="col-lg-4">
            <div class="card card-laporan card-filter">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-funnel-fill me-2 text-primary"></i>Filter Laporan
                    </h6>
                </div>
                <div class="card-body">
                    @include('laporan.partials.filter')
                </div>
            </div>
        </div>

        {{-- Hasil --}}
        <div class="col-lg-8">
            <div class="card card-laporan card-hasil">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-table me-2 text-primary"></i>Hasil Rekapitulasi
                    </h6>
                    <small class="text-muted">E-Arsip SMA Babussalam</small>
                </div>
                <div class="card-body p-0">
                    <div id="laporan_hasil_container">

                        @if(isset($data_laporan))
                            @include('laporan.partials.hasil', [
                                'data_laporan'  => $data_laporan,
                                'tipe_rekap'    => $tipe_rekap    ?? null,
                                'tahun'         => $tahun         ?? null,
                                'bulan'         => $bulan         ?? null,
                                'bulan_nama'    => $bulan_nama    ?? null,
                            ])
                        @else
                            <div class="laporan-empty-state">
                                <i class="bi bi-file-earmark-text" style="font-size:3rem;"></i>
                                <h6>Belum Ada Data Laporan</h6>
                                <p class="text-muted mb-0">Pilih filter terlebih dahulu untuk menampilkan laporan.</p>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
