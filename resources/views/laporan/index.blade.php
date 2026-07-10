@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi Surat')

@section('breadcrumbs')
<ol class="breadcrumb no-print">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i>Home</a>
    </li>
    <li class="breadcrumb-item">Arsip</li>
    <li class="breadcrumb-item active" aria-current="page">Laporan Rekap</li>
</ol>
@endsection

@section('content')
<div class="py-2" id="page-laporan">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 laporan-page-header no-print">

        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-bar-graph-fill fs-5 icon-circle"></i>
            <h5 class="fw-bold mb-0">Laporan Rekapitulasi Surat</h5>
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
                            <svg class="icon-pdf me-2" width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" fill="#dc3545"/>
                                <path d="M14 2v5a1 1 0 0 0 1 1h5" fill="#ffffff" opacity=".35"/>
                                <text x="12" y="17.5" font-family="Arial, Helvetica, sans-serif" font-size="6.5" font-weight="700" fill="#ffffff" text-anchor="middle">PDF</text>
                            </svg> PDF
                        </a>
                    </li>
                    {{--
                    <li>
                        <a href="#" class="dropdown-item download-link" data-format="excel">
                            <i class="bi bi-file-earmark-excel text-success me-2"></i> Excel
                        </a>
                    </li>
                    --}}
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
        <div class="col-lg-4 no-print" id="col_filter">
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
        <div class="col-lg-8" id="col_hasil">
            <div class="card card-laporan card-hasil">
                <div class="card-header d-flex justify-content-between align-items-center no-print">
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
                                <i class="bi bi-file-earmark-text"></i>
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

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}?v={{ file_exists(public_path('css/laporan.css')) ? filemtime(public_path('css/laporan.css')) : '1' }}">
<style>
/* =====================================================================
   PATCH: responsive mobile table + responsive A4/F4 print (potrait/landscape)
   ===================================================================== */

/* ---------- MOBILE (layar HP) ---------- */
@media (max-width: 576px) {
    #page-laporan .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    #page-laporan .table-wrapper table { font-size: .74rem; }

    /* Lebar kolom No sudah diatur terpusat di laporan.css (.table-wrapper
       th.col-no / td.col-no) supaya tidak ada aturan ganda yang bentrok. */

    #page-laporan .table-wrapper th,
    #page-laporan .table-wrapper td {
        padding: 5px 6px;
        white-space: nowrap;
    }

    #page-laporan .lap-col-perihal {
        white-space: normal;
        min-width: 140px;
    }

    #page-laporan .badge-masuk,
    #page-laporan .badge-keluar,
    #page-laporan .badge-total,
    #page-laporan .badge-zero {
        padding: 2px 8px;
        font-size: .68rem;
    }

    #page-laporan .stat-cards { flex-wrap: wrap; gap: 6px; }
    #page-laporan .stat-card { flex: 1 1 30%; padding: 8px 4px; }
    #page-laporan .stat-num { font-size: 1.1rem; }
    #page-laporan .header-bar { flex-wrap: wrap; row-gap: 6px; }
}

/* ---------- CETAK (A4/F4 - potrait & landscape, responsif) ---------- */
@media print {
    @page {
        margin: 12mm 10mm;
        /* Sengaja TIDAK menetapkan `size` di sini, supaya ukuran kertas
           (A4/F4/dll) dan orientasi (potret/lanskap) sepenuhnya mengikuti
           pilihan user di dialog cetak browser/OS — baik di desktop
           maupun mobile — bukan dipaksa oleh CSS. */
    }

    .no-print,
    #col_filter, .card-filter,
    #btn_print, #download_group,
    .btn-kembali-header, .badge-tipe,
    .detail-pagination-wrap { display: none !important; }

    /* kartu hasil melebar penuh saat filter disembunyikan */
    #col_hasil { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
    .row.g-3 { display: block !important; }
    #col_hasil .card-hasil, #col_hasil .card-body { padding: 0 !important; }

    /* jangan biarkan wrapper men-scroll konten, biar browser yang paginasi otomatis
       baik saat kertas dipilih potrait maupun landscape */
    .table-wrapper,
    .table-responsive {
        overflow: visible !important;
        width: 100% !important;
    }

    table { width: 100% !important; table-layout: auto; }
    th, td { word-break: break-word; }

    tr, .stat-card, .card { page-break-inside: avoid; }
    thead { display: table-header-group; }
    tfoot { display: table-row-group; }

    .card, .card-hasil, .card-laporan {
        border: none !important;
        box-shadow: none !important;
    }

    th.col-no, td.col-no { width: 28px !important; }
}
</style>
@endpush
@push('page-js')
  @vite(['resources/js/laporan.js'])
@endpush