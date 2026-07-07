<!doctype html>
<html
    style="color-scheme: light !important;"
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:x="urn:schemas-microsoft-com:office:excel"
    xmlns:w="urn:schemas-microsoft-com:office:word"
>
<head>
    <meta charset="utf-8">
    {{-- Paksa browser render light mode, tidak ikut OS dark mode --}}
    <meta name="color-scheme" content="light only">
    <title>Laporan Surat</title>

    <style>
        /* ========== VARIABEL – LIGHT MODE (disamakan dengan hasil_blade.php) ========== */
        :root {
            color-scheme: light;
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
            --clr-text:             #1e293b;
            --clr-text-body:        #1f2937;
            --clr-surface:          #ffffff;
            --clr-row-even:         #f0f4fa;
            --clr-row-empty-bg:     #e5e7eb;
            --clr-row-empty-text:   #4b5563;
            --clr-border:           #94a3b8;
            --clr-border-light:     #cbd5e1;
            --clr-badge-zero-bg:    #e2e8f0;
            --clr-badge-zero-text:  #475569;
            --clr-badge-zero-bdr:   #94a3b8;
            --clr-print-date:       #374151;
        }

        /* ========== LAYOUT HALAMAN ========== */
        @page {
            margin-top: 20mm; margin-bottom: 18mm;
            margin-left: 15mm; margin-right: 15mm;
        }
        @page Section1 {
            size: 33cm 21cm; /* F4 landscape */
            mso-page-orientation: landscape;
            margin: 2cm 1.5cm 2cm 1.5cm;
        }
        div.Section1 { page: Section1; }

        html, body { margin: 0; padding: 0; background: #ffffff; color: #1f2937; color-scheme: light; }
        body { font-family: Arial, sans-serif; font-size: 10.5pt; }
        .Section1 { padding: 8mm 10mm; }

        /* ========== TANGGAL CETAK ========== */
        .print-date-top {
            text-align: right;
            font-size: 9.5pt;
            color: #374151;
            margin-bottom: 4px;
        }

        /* ========== TABEL ========== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 9px;
            text-align: center;
            vertical-align: middle;
            color: #1e293b;
        }
        thead th {
            font-weight: bold;
            font-size: 9.5pt;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #fff !important;
            border-bottom: 2px solid rgba(255,255,255,.2) !important;
        }
        .thead-tahunan th { background: #26446e !important; }
        .thead-bulanan  th { background: #1e5438 !important; }

        th.col-no, td.col-no { width: 36px; }

        /* zebra */
        tbody tr:nth-child(even) td { background: #f0f4fa; }
        tbody tr:nth-child(odd)  td { background: #ffffff; }
        tbody tr.row-empty td { background: #e5e7eb !important; color: #4b5563 !important; }

        /* tfoot */
        .tfoot-tahunan td { background: #1e3a5f; color: #fff !important; font-weight: bold; font-size: 9.5pt; border-color: #2a4e7a; padding: 7px 9px; }
        .tfoot-bulanan  td { background: #1a4731; color: #fff !important; font-weight: bold; font-size: 9.5pt; border-color: #22613e; padding: 7px 9px; }

        /* ========== BADGE BULAT (sama seperti hasil_blade.php) ========== */
        .badge-masuk, .badge-keluar, .badge-total, .badge-zero {
            display: inline-block;
            border-radius: 999px;
            padding: 3px 16px;
            font-size: 9.5pt;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }
        .badge-masuk  { background: #dbeafe;  color: #0369a1;  border: 1.5px solid #0369a1; }
        .badge-keluar { background: #d1fae5; color: #047857; border: 1.5px solid #047857; }
        .badge-total  { background: #ede9fe;  color: #4338ca;  border: 1.5px solid #4338ca; }
        .badge-zero   {
            background: #e2e8f0;
            color: #475569;
            border: 1.5px solid #94a3b8;
            font-weight: 600;
        }

        /* badge solid putih di tfoot */
        .tfoot-tahunan .badge-masuk,  .tfoot-bulanan .badge-masuk  { background: #0369a1  !important; color: #fff !important; border-color: #0369a1  !important; }
        .tfoot-tahunan .badge-keluar, .tfoot-bulanan .badge-keluar { background: #047857 !important; color: #fff !important; border-color: #047857 !important; }
        .tfoot-tahunan .badge-total,  .tfoot-bulanan .badge-total  { background: #4338ca  !important; color: #fff !important; border-color: #4338ca  !important; }
        .tfoot-tahunan .badge-zero,   .tfoot-bulanan .badge-zero   { background: rgba(255,255,255,.15) !important; color: rgba(255,255,255,.85) !important; border-color: rgba(255,255,255,.3) !important; }

        /* Section sub-title */
        .section-sub {
            font-size: 10.5pt; font-weight: bold;
            margin-top: 14px; margin-bottom: 4px;
            border-left: 3px solid #1e3a5f;
            padding-left: 7px;
            color: #1e293b;
        }
        .section-sub.bulanan { border-left-color: #1a4731; }

        /* jenis surat di detail (bukan badge, cukup teks warna) */
        .txt-masuk  { color: #0369a1;  font-weight: 700; }
        .txt-keluar { color: #047857; font-weight: 700; }

        /* ========== HEADER BAR + TOMBOL KEMBALI ==========
           Catatan: dipakai table-layout (bukan flexbox) karena flexbox terbukti
           menyebabkan elemen saling bertumpuk/overlap saat halaman di-scale
           oleh print engine browser (terjadi pada ukuran kertas custom F4). */
        table.export-header-bar {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
            table-layout: fixed;
        }
        table.export-header-bar td {
            border: none !important;
            padding: .7rem 1rem .6rem;
            vertical-align: middle;
        }
        table.export-header-bar.tahunan td { background: #1e3a5f; }
        table.export-header-bar.bulanan td { background: #1a4731; }

        .export-header-bar .col-badge  { width: 140px; text-align: left; }
        .export-header-bar .col-tombol { width: 110px; text-align: right; }

        .export-header-bar .judul-wrap { text-align: center; }
        .export-header-bar .judul-wrap .title-main {
            font-size: 14pt; font-weight: bold; color: #fff !important;
            letter-spacing: .02em;
            margin: 0; padding: 0;
        }
        .export-header-bar .judul-wrap .title-sub {
            font-size: 11pt; color: rgba(255,255,255,.9) !important;
            margin: 0; padding: 0;
        }

        .btn-tutup-tab {
            display: inline-block;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.35);
            color: #fff !important;
            border-radius: 6px;
            padding: 5px 14px;
            font-size: 12px; font-weight: 600;
            text-decoration: none; cursor: pointer;
            white-space: nowrap;
            transition: background .15s;
        }
        .btn-tutup-tab:hover { background: rgba(255,255,255,.28); }

        .badge-tipe-export {
            display: inline-block;
            font-size: 10px; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase;
            padding: 3px 9px; border-radius: 20px;
            background: rgba(255,255,255,.18);
            color: #fff; border: 1px solid rgba(255,255,255,.35);
            white-space: nowrap;
        }

        /* tabel langsung menyatu di bawah header bar, tanpa jarak/border radius ganda */
        .table-block { margin-bottom: 14px; }
        .table-block table { margin-top: 0; }
        .table-block table.export-header-bar { margin-top: 0; }

        /* ========== PRINT EXACT COLOR ========== */
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        thead { display: table-header-group; }
        tfoot { display: table-row-group; }

        @media print {
            /* Tombol "Kembali" tidak relevan di PDF/print, tetap disembunyikan */
            .export-header-bar .btn-tutup-tab { display: none !important; }

            /* Badge tipe export (icon 📅 Rekap Tahunan / 🗓 Rekap Bulanan)
               DITAMPILKAN saat print/PDF, tidak lagi disembunyikan */
            .export-header-bar .badge-tipe-export {
                display: inline-block !important;
                background: rgba(255,255,255,.18) !important;
                color: #fff !important;
                border-color: rgba(255,255,255,.35) !important;
            }

            .export-header-bar .judul-wrap .title-main { color: #fff !important; }
            .export-header-bar .judul-wrap .title-sub  { color: rgba(255,255,255,.9) !important; }

            /* paksa background gelap tetap solid, jangan dipudarkan browser print engine */
            table.export-header-bar.tahunan td { background: #1e3a5f !important; }
            table.export-header-bar.bulanan td { background: #1a4731 !important; }

            .thead-tahunan th { background: #26446e !important; color: #fff !important; }
            .thead-bulanan  th { background: #1e5438 !important; color: #fff !important; }

            .tfoot-tahunan td { background: #1e3a5f !important; color: #fff !important; }
            .tfoot-bulanan  td { background: #1a4731 !important; color: #fff !important; }

            .badge-masuk  { background: #dbeafe !important; color: #0369a1 !important; border-color: #0369a1 !important; }
            .badge-keluar { background: #d1fae5 !important; color: #047857 !important; border-color: #047857 !important; }
            .badge-total  { background: #ede9fe !important; color: #4338ca !important; border-color: #4338ca !important; }
            .badge-zero   { background: #e2e8f0 !important; color: #475569 !important; border-color: #94a3b8 !important; }

            .tfoot-tahunan .badge-masuk,  .tfoot-bulanan .badge-masuk  { background: #0369a1 !important; color: #fff !important; border-color: #0369a1 !important; }
            .tfoot-tahunan .badge-keluar, .tfoot-bulanan .badge-keluar { background: #047857 !important; color: #fff !important; border-color: #047857 !important; }
            .tfoot-tahunan .badge-total,  .tfoot-bulanan .badge-total  { background: #4338ca !important; color: #fff !important; border-color: #4338ca !important; }
            .tfoot-tahunan .badge-zero,   .tfoot-bulanan .badge-zero   { background: rgba(255,255,255,.15) !important; color: rgba(255,255,255,.85) !important; border-color: rgba(255,255,255,.3) !important; }

            tbody tr:nth-child(even) td { background: #f0f4fa !important; }
            tbody tr:nth-child(odd)  td { background: #ffffff !important; }
            tbody tr.row-empty td { background: #e5e7eb !important; color: #4b5563 !important; }
        }

        /* Excel compat */
        .xl-text   { mso-number-format: "\@"; }
        .xl-number { mso-number-format: "0"; }
    </style>
    <script>
    function tutupTabKembali() {
        /* Coba fokus ke tab yang membuka halaman ini (opener) */
        if (window.opener && !window.opener.closed) {
            window.opener.focus();
        }
        /* Tutup tab ini */
        window.close();
        /* Fallback: kalau window.close() diblokir browser (bukan dibuka via JS),
           arahkan ke halaman laporan */
        setTimeout(function () {
            window.location.href = '/laporan';
        }, 400);
    }
    </script>
</head>
<body>
<div class="Section1">

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $tipe_rekap = $tipe_rekap ?? (request('tipe')  ?? 'Tahun');
    $tahun      = $tahun      ?? (request('tahun') ?? date('Y'));
    $bulan      = $bulan      ?? request('bulan');
    $bulan_nama = $bulan_nama ?? null;

    $filterJenis = isset($filterJenis)
        ? strtolower($filterJenis)
        : (request('jenis') ? strtolower(request('jenis')) : null);
    if ($filterJenis === '') $filterJenis = null;

    $totalSuratMasukBulan  = $totalSuratMasukBulan  ?? 0;
    $totalSuratKeluarBulan = $totalSuratKeluarBulan ?? 0;

    $isTahunan = ($tipe_rekap === 'Tahun' || ($tipe_rekap === 'Bulan' && empty($bulan)));

    $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
@endphp

<div class="print-date-top">Tanggal cetak: {{ $tanggalCetak }}</div>

{{-- ====================================================== --}}
{{-- REKAP TAHUNAN                                          --}}
{{-- ====================================================== --}}
@if($isTahunan)

<div class="table-block">
    <table class="export-header-bar tahunan">
        <tr>
            <td class="col-badge"><span class="badge-tipe-export">📅 Rekap Tahunan</span></td>
            <td class="judul-wrap">
                <div class="title-main">Laporan Rekapitulasi Surat</div>
                <div class="title-sub">Tahun {{ $tahun }}</div>
            </td>
            <td class="col-tombol">
                <button class="btn-tutup-tab" onclick="tutupTabKembali()">← Kembali</button>
            </td>
        </tr>
    </table>

    <table>
        <thead class="thead-tahunan">
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

            @foreach(($data_laporan ?? []) as $row)
                @php
                    $masuk  = $row->total_masuk  ?? 0;
                    $keluar = $row->total_keluar ?? 0;
                    $tot    = $masuk + $keluar;
                    $tm += $masuk; $tk += $keluar;
                @endphp
                <tr @if($tot == 0) class="row-empty" @endif>
                    <td class="col-no xl-number">{{ $loop->iteration }}</td>
                    <td class="xl-text">{{ $row->bulan_nama }}</td>
                    <td class="xl-number">
                        @if($masuk > 0)
                            <span class="badge-masuk">{{ $masuk }} Surat</span>
                        @else
                            <span class="badge-zero">0</span>
                        @endif
                    </td>
                    <td class="xl-number">
                        @if($keluar > 0)
                            <span class="badge-keluar">{{ $keluar }} Surat</span>
                        @else
                            <span class="badge-zero">0</span>
                        @endif
                    </td>
                    <td class="xl-number">
                        @if($tot > 0)
                            <span class="badge-total">{{ $tot }} Surat</span>
                        @else
                            <span class="badge-zero">0</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="tfoot-tahunan">
                <td colspan="2" class="xl-text">TOTAL TAHUN {{ $tahun }}</td>
                <td class="xl-number">
                    @if($tm > 0)
                        <span class="badge-masuk">{{ $tm }} Surat</span>
                    @else
                        <span class="badge-zero">0</span>
                    @endif
                </td>
                <td class="xl-number">
                    @if($tk > 0)
                        <span class="badge-keluar">{{ $tk }} Surat</span>
                    @else
                        <span class="badge-zero">0</span>
                    @endif
                </td>
                <td class="xl-number">
                    @if(($tm + $tk) > 0)
                        <span class="badge-total">{{ $tm + $tk }} Surat</span>
                    @else
                        <span class="badge-zero">0</span>
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- ====================================================== --}}
{{-- REKAP BULANAN                                          --}}
{{-- ====================================================== --}}
@else

@php
    $monthlyTotalMasuk  = $totalSuratMasukBulan;
    $monthlyTotalKeluar = $totalSuratKeluarBulan;
    $totalBulan         = $monthlyTotalMasuk + $monthlyTotalKeluar;
@endphp

<div class="table-block">
    <table class="export-header-bar bulanan">
        <tr>
            <td class="col-badge"><span class="badge-tipe-export">🗓 Rekap Bulanan</span></td>
            <td class="judul-wrap">
                <div class="title-main">Laporan Rekapitulasi Surat</div>
                <div class="title-sub">{{ $bulan_nama }} {{ $tahun }}</div>
            </td>
            <td class="col-tombol">
                <button class="btn-tutup-tab" onclick="tutupTabKembali()">← Kembali</button>
            </td>
        </tr>
    </table>

    {{-- Ringkasan --}}
    <div class="section-sub bulanan">Ringkasan Bulan {{ $bulan_nama }}</div>
    <table>
        <thead class="thead-bulanan">
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
                <td class="col-no xl-number">1</td>
                <td class="xl-text"><strong>{{ $bulan_nama }}</strong></td>
                <td class="xl-number">
                    @if($monthlyTotalMasuk > 0)
                        <span class="badge-masuk">{{ $monthlyTotalMasuk }} Surat</span>
                    @else
                        <span class="badge-zero">0</span>
                    @endif
                </td>
                <td class="xl-number">
                    @if($monthlyTotalKeluar > 0)
                        <span class="badge-keluar">{{ $monthlyTotalKeluar }} Surat</span>
                    @else
                        <span class="badge-zero">0</span>
                    @endif
                </td>
                <td class="xl-number">
                    @if($totalBulan > 0)
                        <span class="badge-total">{{ $totalBulan }} Surat</span>
                    @else
                        <span class="badge-zero">0</span>
                    @endif
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="tfoot-bulanan">
                <td colspan="2" class="xl-text">TOTAL BULAN {{ strtoupper($bulan_nama ?? '') }}</td>
                <td class="xl-number">
                    @if($monthlyTotalMasuk > 0)
                        <span class="badge-masuk">{{ $monthlyTotalMasuk }} Surat</span>
                    @else
                        <span class="badge-zero">0</span>
                    @endif
                </td>
                <td class="xl-number">
                    @if($monthlyTotalKeluar > 0)
                        <span class="badge-keluar">{{ $monthlyTotalKeluar }} Surat</span>
                    @else
                        <span class="badge-zero">0</span>
                    @endif
                </td>
                <td class="xl-number">
                    @if($totalBulan > 0)
                        <span class="badge-total">{{ $totalBulan }} Surat</span>
                    @else
                        <span class="badge-zero">0</span>
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Detail daftar surat --}}
    <div class="section-sub bulanan">Detail Daftar Surat</div>
    <table>
        <thead class="thead-bulanan">
            <tr>
                <th class="col-no">No</th>
                <th>No Surat</th>
                <th>Kode</th>
                <th>Jenis</th>
                <th>Perihal</th>
                <th>Instansi</th>
                <th>Pengirim</th>
                <th>Penerima</th>
                <th>Tgl Surat</th>
                <th>Tgl Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse(($surat ?? []) as $item)
                <tr>
                    <td class="col-no xl-number">{{ $no++ }}</td>
                    <td class="xl-text">{{ $item->no_surat }}</td>
                    <td class="xl-text">{{ $item->kode_surat }}</td>
                    <td class="xl-text {{ strtolower($item->jenis_surat) === 'masuk' ? 'txt-masuk' : 'txt-keluar' }}">
                        {{ ucfirst($item->jenis_surat) }}
                    </td>
                    <td class="xl-text" style="text-align:left;">{{ Str::limit($item->perihal, 80) }}</td>
                    <td class="xl-text">{{ $item->instansi ?? '-' }}</td>
                    <td class="xl-text">{{ $item->pengirim }}</td>
                    <td class="xl-text">{{ $item->penerima }}</td>
                    <td class="xl-text">{{ $item->tanggal_surat ? Carbon::parse($item->tanggal_surat)->translatedFormat('d F Y') : '-' }}</td>
                    <td class="xl-text">{{ $item->created_at   ? Carbon::parse($item->created_at)->translatedFormat('d F Y')   : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="10" style="color:#aaa;">Tidak ada data surat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endif

</div>{{-- .Section1 --}}
</body>
</html>