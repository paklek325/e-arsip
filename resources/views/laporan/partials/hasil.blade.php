@php
use Carbon\Carbon;

Carbon::setLocale('id');

$filterJenis = strtolower(request('jenis') ?? '');
if (empty($filterJenis)) $filterJenis = null;

$totalSuratMasukBulan  = $totalSuratMasukBulan  ?? 0;
$totalSuratKeluarBulan = $totalSuratKeluarBulan ?? 0;
$data_laporan          = $data_laporan          ?? collect();

$tipe_rekap = $tipe_rekap ?? (request('tipe')  ?? 'Tahun');
$tahun      = $tahun      ?? (request('tahun') ?? date('Y'));
$bulan      = $bulan      ?? null;
$bulan_nama = $bulan_nama ?? null;

$tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
@endphp

<style>
/* ====== VARIABEL – LIGHT MODE ====== */
:root {
    --clr-masuk:            #0369a1;
    --clr-masuk-bg:         #dbeafe;
    --clr-keluar:           #047857;
    --clr-keluar-bg:        #d1fae5;
    --clr-total:            #4338ca;
    --clr-total-bg:         #ede9fe;
    --clr-header-tahunan:   #1e3a5f;
    --clr-header-bulanan:   #1a4731;
    --clr-thead-tahunan:    #c8d8f0;
    --clr-thead-bulanan:    #bbf7d0;
    /* teks & surface */
    --clr-text:             #1e293b;
    --clr-text-muted:       #374151;
    --clr-surface:          #ffffff;
    --clr-surface-alt:      #f1f5f9;
    --clr-border:           #94a3b8;
    --clr-border-light:     #cbd5e1;
    --clr-row-even:         #f0f4fa;
    --clr-row-empty-bg:     #e5e7eb;
    --clr-row-empty-text:   #4b5563;
    --clr-stat-label:       #1f2937;
    --clr-print-date:       #374151;
    --clr-badge-zero-bg:    #e2e8f0;
    --clr-badge-zero-text:  #475569;
    --clr-badge-zero-bdr:   #94a3b8;
}

/* ====== VARIABEL – DARK MODE (hanya via class .dark, bukan otomatis OS) ====== */
.dark {
    --clr-masuk:            #38bdf8;
    --clr-masuk-bg:         #0c2233;
    --clr-keluar:           #34d399;
    --clr-keluar-bg:        #062018;
    --clr-total:            #a78bfa;
    --clr-total-bg:         #1a1035;
    --clr-header-tahunan:   #1e3460;
    --clr-header-bulanan:   #1a4731;
    --clr-thead-tahunan:    #253a6e;
    --clr-thead-bulanan:    #14532d;
    --clr-text:             #e2e8f0;
    --clr-text-muted:       #94a3b8;
    --clr-surface:          #1e2d45;
    --clr-surface-alt:      #172034;
    --clr-border:           #2e4366;
    --clr-border-light:     #2e4366;
    --clr-row-even:         #172034;
    --clr-row-empty-bg:     #1a2b42;
    --clr-row-empty-text:   #64748b;
    --clr-stat-label:       #94a3b8;
    --clr-print-date:       #64748b;
    --clr-badge-zero-bg:    #1e2d45;
    --clr-badge-zero-text:  #64748b;
    --clr-badge-zero-bdr:   #2e4366;
}

/* ====== LAYOUT KARTU ====== */
.card { position: relative; }
.print-date-top {
    position: absolute;
    top: 6px; right: 12px;
    font-size: 11px; color: var(--clr-print-date);
}

/* ====== HEADER BAR ====== */
.header-bar {
    border-radius: 8px 8px 0 0;
    padding: .7rem 1rem .6rem;
    margin-bottom: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .5rem;
}
.laporan-tahunan .header-bar { background: var(--clr-header-tahunan); }
.laporan-bulanan .header-bar { background: var(--clr-header-bulanan); }

/* dark mode: header lebih gelap dan dalam */
.dark .laporan-tahunan .header-bar { background: #152644; }

.judul-print { text-align: center; flex: 1; }
.judul-print h4 { margin: 0; padding: 0; font-size: 18px; font-weight: 700; color: #fff !important; }
.judul-print h5 { margin: 0; padding: 0; font-size: 13px; font-weight: 400; color: rgba(255,255,255,.9) !important; }

.badge-tipe {
    font-size: 10px; font-weight: 700;
    letter-spacing: .06em; text-transform: uppercase;
    padding: 3px 9px; border-radius: 20px; white-space: nowrap;
    background: rgba(255,255,255,.18);
    color: #fff;
    border: 1px solid rgba(255,255,255,.35);
}

/* tombol kembali di header */
.btn-kembali-header {
    display: none;
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.35);
    color: #fff;
    border-radius: 6px;
    padding: 3px 10px;
    font-size: 12px;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s;
}
.btn-kembali-header:hover { background: rgba(255,255,255,.3); color: #fff; }

/* ====== TABEL ====== */
.table-wrapper {
    padding: 0;
    margin-top: 0;
}
.table-wrapper table {
    width: 100%;
    border-collapse: collapse !important;
    table-layout: auto;
    margin: 0;
}

/* kolom No sempit */
.table-wrapper table th.col-no,
.table-wrapper table td.col-no {
    width: 42px !important;
    min-width: 42px !important;
    max-width: 42px !important;
    text-align: center !important;
}

.table-wrapper table th,
.table-wrapper table td {
    border: 1px solid var(--clr-border-light) !important;
    padding: .4rem .65rem !important;
    vertical-align: middle !important;
    color: var(--clr-text) !important;
    text-align: center !important;
}

.table-wrapper table thead th {
    padding-top: .45rem !important;
    padding-bottom: .45rem !important;
    font-size: 12px;
    letter-spacing: .04em;
    text-transform: uppercase;
    border-bottom: 2px solid rgba(255,255,255,.2) !important;
    color: #fff !important;
    font-weight: 700 !important;
}

/* thead background navy per tipe — sama dengan header bar */
.laporan-tahunan .table-wrapper thead.table-primary th {
    background: #26446e !important;
}
.laporan-bulanan .table-wrapper thead.table-primary th {
    background: #1e5438 !important;
}

/* dark mode: sedikit lebih gelap */
.dark .laporan-tahunan .table-wrapper thead.table-primary th {
    background: #1c3358 !important;
    color: #fff !important;
    border-bottom-color: rgba(255,255,255,.15) !important;
}
.dark .laporan-bulanan .table-wrapper thead.table-primary th {
    background: #163d28 !important;
    color: #fff !important;
    border-bottom-color: rgba(255,255,255,.15) !important;
}

/* zebra ringan pada body */
.table-wrapper tbody tr:nth-child(even) td { background: var(--clr-row-even); }
.table-wrapper tbody tr:nth-child(odd)  td { background: var(--clr-surface); }
.table-wrapper tbody tr.row-empty td {
    background: var(--clr-row-empty-bg) !important;
    color: var(--clr-row-empty-text) !important;
}

/* ====== BADGE ====== */
.badge-masuk, .badge-keluar, .badge-total, .badge-zero {
    display: inline-block;
    border-radius: 999px;
    padding: 3px 16px;
    font-size: 11.5px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background .13s, color .13s;
}
.badge-masuk  { background: var(--clr-masuk-bg);  color: var(--clr-masuk);  border: 1.5px solid var(--clr-masuk); }
.badge-keluar { background: var(--clr-keluar-bg); color: var(--clr-keluar); border: 1.5px solid var(--clr-keluar); }
.badge-total  { background: var(--clr-total-bg);  color: var(--clr-total);  border: 1.5px solid var(--clr-total); }
.badge-zero   {
    background: var(--clr-badge-zero-bg);
    color: var(--clr-badge-zero-text);
    border: 1.5px solid var(--clr-badge-zero-bdr);
    cursor: default;
    font-weight: 600;
}

.badge-masuk:hover  { background: var(--clr-masuk);  color: #fff; }
.badge-keluar:hover { background: var(--clr-keluar); color: #fff; }
.badge-total:hover  { background: var(--clr-total);  color: #fff; }

/* ====== TFOOT ====== */
.tfoot-total td {
    vertical-align: middle !important;
    font-size: 12px;
    font-weight: 700;
    padding: .5rem .65rem !important;
    border-top: 2px solid var(--clr-border) !important;
}
.laporan-tahunan .tfoot-total td {
    background: var(--clr-header-tahunan) !important;
    color: #fff !important;
    border-color: #2a4e7a !important;
}
.laporan-bulanan .tfoot-total td {
    background: var(--clr-header-bulanan) !important;
    color: #fff !important;
    border-color: #22613e !important;
}
/* dark mode tfoot — paksa tetap putih */
.dark .laporan-tahunan .tfoot-total td,
.dark .laporan-bulanan  .tfoot-total td {
    color: #fff !important;
}
/* badge solid di tfoot — paksa putih dengan !important */
.tfoot-total .badge-masuk,
.tfoot-total a.badge-masuk  { background: var(--clr-masuk)  !important; color: #fff !important; border-color: var(--clr-masuk)  !important; }
.tfoot-total .badge-keluar,
.tfoot-total a.badge-keluar { background: var(--clr-keluar) !important; color: #fff !important; border-color: var(--clr-keluar) !important; }
.tfoot-total .badge-total,
.tfoot-total a.badge-total  { background: var(--clr-total)  !important; color: #fff !important; border-color: var(--clr-total)  !important; }
.tfoot-total .badge-zero    { background: rgba(255,255,255,.15) !important; color: rgba(255,255,255,.85) !important; border-color: rgba(255,255,255,.3) !important; }
.tfoot-total .badge-masuk:hover,
.tfoot-total a.badge-masuk:hover  { filter: brightness(1.2); color: #fff !important; }
.tfoot-total .badge-keluar:hover,
.tfoot-total a.badge-keluar:hover { filter: brightness(1.2); color: #fff !important; }
.tfoot-total .badge-total:hover,
.tfoot-total a.badge-total:hover  { filter: brightness(1.2); color: #fff !important; }

/* pastikan semua <a> di dalam tfoot tidak kena warna link Bootstrap */
.tfoot-total td a { color: #fff !important; text-decoration: none !important; }

/* ====== SUB JUDUL SECTION (mis. "Detail Daftar Surat") ====== */
.section-sub {
    font-size: 13.5px;
    font-weight: 700;
    margin: 14px 1rem 6px;
    padding-left: 8px;
    border-left: 3px solid var(--clr-header-tahunan);
    color: var(--clr-text);
}
.laporan-bulanan .section-sub { border-left-color: var(--clr-header-bulanan); }

/* ====== JENIS SURAT (teks warna, dipakai di tabel detail) ====== */
.txt-masuk  { color: var(--clr-masuk);  font-weight: 700; }
.txt-keluar { color: var(--clr-keluar); font-weight: 700; }

/* ====== TABEL DETAIL SURAT ====== */
.table-wrapper.detail-surat table td,
.table-wrapper.detail-surat table th { text-align: center; }
.table-wrapper.detail-surat table td.text-left { text-align: left !important; }
.detail-surat-empty {
    text-align: center !important;
    color: var(--clr-row-empty-text);
    padding: 1.1rem .65rem !important;
}

/* ====== PAGINATION (detail surat) ====== */
.detail-pagination-wrap {
    padding: .65rem 1rem .9rem;
    display: flex;
    justify-content: center;
}
.detail-pagination-wrap .pagination { margin: 0; }

/* ====== STAT CARDS (bulanan) ====== */
.stat-cards {
    display: flex; gap: .75rem; flex-wrap: wrap;
    padding: .75rem 1rem .5rem;
}
.stat-card {
    flex: 1; min-width: 130px;
    border-radius: 8px; padding: .6rem .9rem;
    text-align: center;
}
.stat-card.masuk  { background: var(--clr-masuk-bg);  border: 1px solid var(--clr-masuk); }
.stat-card.keluar { background: var(--clr-keluar-bg); border: 1px solid var(--clr-keluar); }
.stat-card.total  { background: var(--clr-total-bg);  border: 1px solid var(--clr-total); }
.stat-card .stat-num  { font-size: 26px; font-weight: 700; line-height: 1.1; }
.stat-card.masuk  .stat-num { color: var(--clr-masuk); }
.stat-card.keluar .stat-num { color: var(--clr-keluar); }
.stat-card.total  .stat-num { color: var(--clr-total); }
.stat-card .stat-label {
    font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--clr-stat-label); margin-top: 2px;
}

/* ====== PRINT ====== */
@media print {
    @page { size: 330mm 210mm; margin: 0; }
    html, body { margin: 0 !important; padding: 0 !important; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }

    /* force light-mode values saat print agar hasil cetak konsisten */
    :root {
        --clr-masuk:         #0369a1;
        --clr-masuk-bg:      #dbeafe;
        --clr-keluar:        #047857;
        --clr-keluar-bg:     #d1fae5;
        --clr-total:         #4338ca;
        --clr-total-bg:      #ede9fe;
        --clr-thead-tahunan: #c8d8f0;
        --clr-thead-bulanan: #bbf7d0;
        --clr-text:          #1e293b;
        --clr-surface:       #ffffff;
        --clr-row-even:      #f0f4fa;
        --clr-row-empty-bg:  #e5e7eb;
        --clr-row-empty-text:#4b5563;
        --clr-stat-label:    #1f2937;
        --clr-border:        #94a3b8;
        --clr-border-light:  #cbd5e1;
        --clr-badge-zero-bg:    #e2e8f0;
        --clr-badge-zero-text:  #475569;
        --clr-badge-zero-bdr:   #94a3b8;
    }

    .laporan-tahunan,
    .laporan-bulanan { padding: 8mm 10mm; }

    .card { box-shadow: none !important; border: none !important; margin: 0 !important; }
    .card-body { padding: 0 !important; }
    .header-bar { border-radius: 0 !important; }
    .laporan-tahunan .header-bar { background: var(--clr-header-tahunan) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .laporan-bulanan .header-bar { background: var(--clr-header-bulanan) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    .judul-print h4 { font-size: 14px !important; }
    .judul-print h5 { font-size: 12px !important; }

    .table-wrapper { width: 100% !important; }
    .table-wrapper table { width: 100% !important; table-layout: fixed !important; }
    .table-wrapper table th, .table-wrapper table td { white-space: nowrap; font-size: 10px !important; padding: .3rem .5rem !important; }
    .table-wrapper table th.col-no, .table-wrapper table td.col-no { width: 30px !important; max-width: 30px !important; }
    .table-responsive { overflow: visible !important; }

    .laporan-tahunan .table-wrapper thead.table-primary th { background: var(--clr-thead-tahunan) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .laporan-bulanan .table-wrapper thead.table-primary th { background: var(--clr-thead-bulanan) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .laporan-tahunan .tfoot-total td { background: var(--clr-header-tahunan) !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .laporan-bulanan .tfoot-total td { background: var(--clr-header-bulanan) !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    .badge-masuk, .badge-keluar, .badge-total, .badge-zero,
    .tfoot-total .badge-masuk, .tfoot-total .badge-keluar, .tfoot-total .badge-total {
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .table-wrapper a { text-decoration: none !important; }

    /* zebra print */
    .table-wrapper tbody tr:nth-child(even) td { background: #f0f4ff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .table-wrapper tbody tr:nth-child(odd)  td { background: #ffffff  !important; }

    .print-date-top { top: 3mm; right: 10mm; font-size: 9px !important; color: #000 !important; position: fixed; }
    .stat-cards { padding: .4rem .6rem .3rem !important; gap: .4rem !important; }
    .stat-card { padding: .35rem .5rem !important; }
    .stat-card .stat-num { font-size: 16px !important; }
    .stat-card .stat-label { font-size: 8px !important; }

    .laporan-tahunan { max-height: 200mm; overflow: hidden !important; page-break-inside: avoid !important; }
    .laporan-tahunan table { page-break-inside: avoid !important; }
    .laporan-bulanan { page-break-inside: auto; }
    tr, td, th { page-break-inside: avoid; }

    .section-sub { margin: 10px 6mm 4px; font-size: 11px !important; }
    .detail-pagination-wrap, .no-print-pagination { display: none !important; }
}
</style>

{{-- Script: tombol kembali yang bekerja di new tab --}}
<script>
(function () {
    function initKembali() {
        var btns = document.querySelectorAll('.btn-kembali-header');
        if (!btns.length) return;

        /* referrer valid = sama origin */
        var ref = document.referrer;
        var sameOrigin = ref && (new URL(ref).origin === window.location.origin);

        btns.forEach(function (btn) {
            btn.style.display = 'inline-block';
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                if (sameOrigin) {
                    history.back();
                } else {
                    /* new tab tanpa referrer: tutup tab ini */
                    window.close();
                    /* fallback kalau window.close() diblokir: ke halaman induk */
                    setTimeout(function () {
                        window.location.href = btn.dataset.fallback || '/laporan';
                    }, 300);
                }
            });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKembali);
    } else {
        initKembali();
    }
})();
</script>

{{-- ===================================================================== --}}
{{--  BAGIAN 1: REKAPITULASI TAHUNAN                                       --}}
{{-- ===================================================================== --}}
@if($tipe_rekap == 'Tahun' || ($tipe_rekap == 'Bulan' && empty($bulan)))

@php
    $tmTotal = 0; $tkTotal = 0;
    foreach ($data_laporan as $r) {
        $tmTotal += ($r->total_masuk  ?? 0);
        $tkTotal += ($r->total_keluar ?? 0);
    }
    $urlTotalMasuk  = route('surat.masuk',  ['tahun' => $tahun]);
    $urlTotalKeluar = route('surat.keluar', ['tahun' => $tahun]);
    $urlTotalSemua  = route('surat.index',  ['tahun' => $tahun]);
@endphp

<div class="laporan-tahunan">
    <div class="card shadow-sm mb-3">

        <div class="print-date-top no-print">
            <small>Tanggal cetak: {{ $tanggalCetak }}</small>
        </div>

        <div class="card-body table-responsive p-0">

            <div class="header-bar">
                <div class="flex-grow-1 judul-print">
                    <h4>Laporan Rekapitulasi Surat</h4>
                    <h5>Tahun {{ $tahun }}</h5>
                </div>
                <span class="badge-tipe no-print">📅 Tahunan</span>
                {{-- data-fallback = halaman induk jika new tab --}}
                <a class="btn-kembali-header no-print"
                   href="#"
                   data-fallback="{{ url('/laporan') }}"
                   title="Kembali">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="table-wrapper">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="col-no">No</th>
                            <th>Bulan</th>
                            <th>Surat Masuk</th>
                            <th>Surat Keluar</th>
                            <th>Total Surat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $tm = 0; $tk = 0; @endphp

                        @foreach($data_laporan as $row)
                            @php
                                $masuk  = $row->total_masuk  ?? 0;
                                $keluar = $row->total_keluar ?? 0;
                                $tot    = $masuk + $keluar;
                                $tm    += $masuk;
                                $tk    += $keluar;

                                $urlMasuk  = route('surat.masuk',  ['bulan' => $row->bulan, 'tahun' => $tahun]);
                                $urlKeluar = route('surat.keluar', ['bulan' => $row->bulan, 'tahun' => $tahun]);
                                $urlSemua  = route('surat.index',  ['bulan' => $row->bulan, 'tahun' => $tahun]);
                            @endphp

                            <tr @if($tot == 0) class="row-empty" @endif>
                                <td class="col-no">{{ $loop->iteration }}</td>
                                <td>{{ $row->bulan_nama }}</td>

                                <td>
                                    @if($masuk > 0)
                                        <a href="{{ $urlMasuk }}" class="badge-masuk">{{ $masuk }} Surat</a>
                                    @else
                                        <span class="badge-zero">0</span>
                                    @endif
                                </td>

                                <td>
                                    @if($keluar > 0)
                                        <a href="{{ $urlKeluar }}" class="badge-keluar">{{ $keluar }} Surat</a>
                                    @else
                                        <span class="badge-zero">0</span>
                                    @endif
                                </td>

                                <td>
                                    @if($tot > 0)
                                        <a href="{{ $urlSemua }}" class="badge-total">{{ $tot }} Surat</a>
                                    @else
                                        <span class="badge-zero">0</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="tfoot-total">
                            <td colspan="2"><strong>TOTAL TAHUN {{ $tahun }}</strong></td>

                            <td>
                                @if($tmTotal > 0)
                                    <a href="{{ $urlTotalMasuk }}" class="badge-masuk">{{ $tmTotal }} Surat</a>
                                @else
                                    <span class="badge-zero">0</span>
                                @endif
                            </td>

                            <td>
                                @if($tkTotal > 0)
                                    <a href="{{ $urlTotalKeluar }}" class="badge-keluar">{{ $tkTotal }} Surat</a>
                                @else
                                    <span class="badge-zero">0</span>
                                @endif
                            </td>

                            <td>
                                @if(($tmTotal + $tkTotal) > 0)
                                    <a href="{{ $urlTotalSemua }}" class="badge-total">{{ $tmTotal + $tkTotal }} Surat</a>
                                @else
                                    <span class="badge-zero">0</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- ===================================================================== --}}
{{--  BAGIAN 2: DETAIL BULAN                                               --}}
{{-- ===================================================================== --}}
@else

@php
    $monthlyTotalMasuk  = $totalSuratMasukBulan  ?? 0;
    $monthlyTotalKeluar = $totalSuratKeluarBulan ?? 0;
    $totalBulan         = $monthlyTotalMasuk + $monthlyTotalKeluar;

    $urlMasuk  = route('surat.masuk',  ['bulan' => $bulan, 'tahun' => $tahun]);
    $urlKeluar = route('surat.keluar', ['bulan' => $bulan, 'tahun' => $tahun]);
    $urlSemua  = route('surat.index',  ['bulan' => $bulan, 'tahun' => $tahun]);
@endphp

<div class="laporan-bulanan">
    <div class="card shadow-sm">

        <div class="print-date-top no-print">
            <small>Tanggal cetak: {{ $tanggalCetak }}</small>
        </div>

        <div class="card-body p-0">

            <div class="header-bar">
                <div class="flex-grow-1 judul-print">
                    <h4>Laporan Rekapitulasi Surat</h4>
                    <h5>{{ $bulan_nama }} {{ $tahun }}</h5>
                </div>
                <span class="badge-tipe no-print">🗓 Bulanan</span>
                <a class="btn-kembali-header no-print"
                   href="#"
                   data-fallback="{{ url('/laporan') }}"
                   title="Kembali">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="stat-cards no-print">
                <div class="stat-card masuk">
                    <div class="stat-num">{{ $monthlyTotalMasuk }}</div>
                    <div class="stat-label">Surat Masuk</div>
                </div>
                <div class="stat-card keluar">
                    <div class="stat-num">{{ $monthlyTotalKeluar }}</div>
                    <div class="stat-label">Surat Keluar</div>
                </div>
                <div class="stat-card total">
                    <div class="stat-num">{{ $totalBulan }}</div>
                    <div class="stat-label">Total Surat</div>
                </div>
            </div>

            <div class="table-wrapper table-responsive mb-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="col-no">No</th>
                            <th>Bulan</th>
                            <th>Surat Masuk</th>
                            <th>Surat Keluar</th>
                            <th>Total Surat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="col-no">1</td>
                            <td><strong>{{ $bulan_nama }}</strong></td>

                            <td>
                                @if($monthlyTotalMasuk > 0)
                                    <a href="{{ $urlMasuk }}" class="badge-masuk">{{ $monthlyTotalMasuk }} Surat</a>
                                @else
                                    <span class="badge-zero">0</span>
                                @endif
                            </td>

                            <td>
                                @if($monthlyTotalKeluar > 0)
                                    <a href="{{ $urlKeluar }}" class="badge-keluar">{{ $monthlyTotalKeluar }} Surat</a>
                                @else
                                    <span class="badge-zero">0</span>
                                @endif
                            </td>

                            <td>
                                @if($totalBulan > 0)
                                    <a href="{{ $urlSemua }}" class="badge-total">{{ $totalBulan }} Surat</a>
                                @else
                                    <span class="badge-zero">0</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr class="tfoot-total">
                            <td colspan="2"><strong>TOTAL BULAN {{ strtoupper($bulan_nama ?? '') }}</strong></td>

                            <td>
                                @if($monthlyTotalMasuk > 0)
                                    <a href="{{ $urlMasuk }}" class="badge-masuk">{{ $monthlyTotalMasuk }} Surat</a>
                                @else
                                    <span class="badge-zero">0</span>
                                @endif
                            </td>

                            <td>
                                @if($monthlyTotalKeluar > 0)
                                    <a href="{{ $urlKeluar }}" class="badge-keluar">{{ $monthlyTotalKeluar }} Surat</a>
                                @else
                                    <span class="badge-zero">0</span>
                                @endif
                            </td>

                            <td>
                                @if($totalBulan > 0)
                                    <a href="{{ $urlSemua }}" class="badge-total">{{ $totalBulan }} Surat</a>
                                @else
                                    <span class="badge-zero">0</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ============================================================= --}}
            {{--  Detail Daftar Surat                                          --}}
            {{-- ============================================================= --}}
            <div class="section-sub">Detail Daftar Surat</div>

            <div class="table-wrapper detail-surat table-responsive mb-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="col-no">No</th>
                            <th>Jenis</th>
                            <th>No Surat</th>
                            <th style="min-width:180px;">Perihal</th>
                            <th>Instansi</th>
                            <th>Tgl Surat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $noDetail = ($surat instanceof \Illuminate\Pagination\AbstractPaginator) ? (($surat->currentPage() - 1) * $surat->perPage()) + 1 : 1; @endphp

                        @forelse(($surat ?? []) as $item)
                            <tr>
                                <td class="col-no">{{ $noDetail++ }}</td>
                                <td class="{{ strtolower($item->jenis_surat) === 'masuk' ? 'txt-masuk' : 'txt-keluar' }}">
                                    {{ ucfirst($item->jenis_surat) }}
                                </td>
                                <td>{{ $item->no_surat }}</td>
                                <td class="text-left">{{ $item->perihal ?: '-' }}</td>
                                <td>{{ $item->instansi ?: '-' }}</td>
                                <td>{{ $item->tanggal_surat ? Carbon::parse($item->tanggal_surat)->translatedFormat('d M Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="detail-surat-empty">Tidak ada data surat untuk bulan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($surat instanceof \Illuminate\Pagination\AbstractPaginator && $surat->hasPages())
                <div class="detail-pagination-wrap no-print">
                    {{ $surat->links() }}
                </div>
            @endif

        </div>
    </div>
</div>

@endif




