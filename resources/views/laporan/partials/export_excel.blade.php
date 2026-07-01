<!doctype html>
<html
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:x="urn:schemas-microsoft-com:office:excel"
>
<head>
    <meta charset="utf-8">
    <title>Laporan Surat</title>

    <style>
        /* ==========================================================
           CATATAN PENTING — Khusus Excel/PhpSpreadsheet:
           Engine Excel TIDAK mendukung CSS custom properties (var(--x)),
           rgba(), border-radius, atau box-shadow. Semua warna di bawah
           ditulis hex langsung (statis) agar benar-benar tampil saat
           dibuka di Excel — tidak hanya di preview browser.
           ========================================================== */

        html, body {
            margin: 0; padding: 0;
            font-family: Arial, sans-serif;
            font-size: 10pt;
            background: #ffffff;
            color: #1f2937;
        }

        /* ========== HEADER BAR + TOMBOL KEMBALI ========== */
        table.export-header-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        table.export-header-bar td {
            border: none;
            padding: 8px 10px;
            vertical-align: middle;
        }
        table.export-header-bar.tahunan td { background: #1e3a5f; }
        table.export-header-bar.bulanan td { background: #1a4731; }

        .export-header-bar .col-badge  { width: 120px; text-align: left; }
        .export-header-bar .col-tombol { width: 90px; text-align: right; }

        .export-header-bar .judul-wrap { text-align: center; }
        .export-header-bar .judul-wrap .title-main {
            font-size: 13pt; font-weight: bold; color: #ffffff;
            letter-spacing: .02em;
        }
        .export-header-bar .judul-wrap .title-sub {
            font-size: 10.5pt; color: #ffffff;
        }

        .btn-tutup-tab {
            display: inline-block;
            background: #2e4d72;
            border: 1pt solid #5b7aa3;
            color: #ffffff;
            padding: 3px 10px;
            font-size: 9pt; font-weight: 600;
            white-space: nowrap;
        }

        .badge-tipe-export {
            display: inline-block;
            font-size: 8.5pt; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase;
            padding: 2px 8px;
            background: #2e4d72;
            color: #ffffff; border: 1pt solid #5b7aa3;
            white-space: nowrap;
        }
        .bulanan .badge-tipe-export { background: #245238; border-color: #4a7a5e; }

        /* ========== TABEL ========== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        th, td {
            border: 0.5pt solid #cbd5e1;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            white-space: normal;
            word-wrap: break-word;
            mso-wrap: 1;
            color: #1f2937;
        }
        th {
            font-weight: bold; font-size: 9pt;
            text-transform: uppercase; letter-spacing: .03em;
            color: #ffffff;
        }

        th.col-no, td.col-no {
            width: 28px;
            mso-width-source: userset;
        }

        /* thead warna per tipe (navy/hijau solid) */
        .thead-tahunan th { background: #26446e; }
        .thead-bulanan  th { background: #1e5438; }

        /* zebra */
        .row-even td { background: #f1f5f9; }
        .row-empty td { background: #e5e7eb; color: #4b5563; }
        tr:not(.row-even):not(.row-empty) td { background: #ffffff; }

        /* tfoot */
        .tfoot-tahunan td { background: #1e3a5f; color: #ffffff; font-weight: bold; font-size: 9pt; border-color: #2a4e7a; }
        .tfoot-bulanan  td { background: #1a4731; color: #ffffff; font-weight: bold; font-size: 9pt; border-color: #22613e; }

        /* ========== BADGE (Excel tidak dukung border-radius, jadi tampil sebagai kotak warna) ========== */
        .badge-masuk, .badge-keluar, .badge-total, .badge-zero {
            display: inline-block;
            padding: 2px 14px;
            font-size: 9pt;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-masuk  { background: #dbeafe; color: #0369a1; border: 1.5pt solid #0369a1; }
        .badge-keluar { background: #d1fae5; color: #047857; border: 1.5pt solid #047857; }
        .badge-total  { background: #ede9fe; color: #4338ca; border: 1.5pt solid #4338ca; }
        .badge-zero   {
            background: #e2e8f0;
            color: #475569;
            border: 1.5pt solid #94a3b8;
            font-weight: 600;
        }

        /* badge solid putih di tfoot */
        .tfoot-tahunan .badge-masuk,  .tfoot-bulanan .badge-masuk  { background: #0369a1; color: #ffffff; border-color: #0369a1; }
        .tfoot-tahunan .badge-keluar, .tfoot-bulanan .badge-keluar { background: #047857; color: #ffffff; border-color: #047857; }
        .tfoot-tahunan .badge-total,  .tfoot-bulanan .badge-total  { background: #4338ca; color: #ffffff; border-color: #4338ca; }
        .tfoot-tahunan .badge-zero,   .tfoot-bulanan .badge-zero   { background: #5c7798; color: #e5e9ef; border-color: #8aa0bd; }

        .section-sub {
            font-size: 10pt; font-weight: bold;
            margin-top: 12px; margin-bottom: 3px;
            border-left: 3pt solid #1e3a5f;
            padding-left: 6px;
            color: #111827;
        }
        .section-sub.bulanan { border-left-color: #1a4731; }

        /* jenis surat di detail (bukan badge, cukup teks warna) */
        .txt-masuk  { color: #0369a1; font-weight: 700; }
        .txt-keluar { color: #047857; font-weight: 700; }

        .nowrap { white-space: nowrap !important; mso-wrap: 0; }
        .xl-text   { mso-number-format: "\@"; }
        .xl-number { mso-number-format: "0"; }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $tipe_rekap = $tipe_rekap ?? 'Tahun';
    $tahun      = $tahun      ?? date('Y');
    $bulan      = $bulan      ?? null;
    $bulan_nama = $bulan_nama ?? null;

    $filterJenis = isset($filterJenis)
        ? strtolower($filterJenis)
        : (request('jenis') ? strtolower(request('jenis')) : null);
    if ($filterJenis === '') $filterJenis = null;

    $totalSuratMasukBulan  = $totalSuratMasukBulan  ?? 0;
    $totalSuratKeluarBulan = $totalSuratKeluarBulan ?? 0;

    $isTahunan = ($tipe_rekap == 'Tahun' || ($tipe_rekap == 'Bulan' && empty($bulan)));
@endphp

{{-- ====================================================== --}}
{{-- REKAP TAHUNAN                                          --}}
{{-- ====================================================== --}}
@if($isTahunan)

<table class="export-header-bar tahunan">
    <tr>
        <td class="col-badge"><span class="badge-tipe-export">📅 Rekap Tahunan</span></td>
        <td class="judul-wrap">
            <div class="title-main">Laporan Rekapitulasi Surat</div>
            <div class="title-sub">Tahun {{ $tahun }}</div>
        </td>
        <td class="col-tombol"><span class="btn-tutup-tab">← Kembali</span></td>
    </tr>
</table>

<table>
    <thead class="thead-tahunan">
        <tr>
            <th class="col-no nowrap">No</th>
            <th class="nowrap">Bulan</th>
            <th class="nowrap">Surat Masuk</th>
            <th class="nowrap">Surat Keluar</th>
            <th class="nowrap">Total Surat</th>
        </tr>
    </thead>
    <tbody>
        @php $tm = 0; $tk = 0; $i = 0; @endphp

        @foreach(($data_laporan ?? []) as $row)
            @php
                $masuk  = $row->total_masuk  ?? 0;
                $keluar = $row->total_keluar ?? 0;
                $tot    = $masuk + $keluar;
                $tm += $masuk; $tk += $keluar;
                $i++;
            @endphp
            <tr class="{{ $tot == 0 ? 'row-empty' : ($i % 2 == 0 ? 'row-even' : '') }}">
                <td class="col-no xl-number">{{ $i }}</td>
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
            <td colspan="2" class="xl-text nowrap">TOTAL TAHUN {{ $tahun }}</td>
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

{{-- ====================================================== --}}
{{-- REKAP BULANAN                                          --}}
{{-- ====================================================== --}}
@else

@php
    $monthlyTotalMasuk  = $totalSuratMasukBulan;
    $monthlyTotalKeluar = $totalSuratKeluarBulan;
    $totalBulan         = $monthlyTotalMasuk + $monthlyTotalKeluar;
@endphp

<table class="export-header-bar bulanan">
    <tr>
        <td class="col-badge"><span class="badge-tipe-export">🗓 Rekap Bulanan</span></td>
        <td class="judul-wrap">
            <div class="title-main">Laporan Rekapitulasi Surat</div>
            <div class="title-sub">{{ $bulan_nama }} {{ $tahun }}</div>
        </td>
        <td class="col-tombol"><span class="btn-tutup-tab">← Kembali</span></td>
    </tr>
</table>

{{-- Ringkasan --}}
<div class="section-sub bulanan">Ringkasan Bulan {{ $bulan_nama }}</div>
<table>
    <thead class="thead-bulanan">
        <tr>
            <th class="col-no nowrap">No</th>
            <th class="nowrap">Bulan</th>
            <th class="nowrap">Surat Masuk</th>
            <th class="nowrap">Surat Keluar</th>
            <th class="nowrap">Total Surat</th>
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
            <td colspan="2" class="xl-text nowrap">TOTAL BULAN {{ strtoupper($bulan_nama ?? '') }}</td>
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

{{-- Detail --}}
<div class="section-sub bulanan">Detail Daftar Surat</div>
<table>
    <thead class="thead-bulanan">
        <tr>
            <th class="col-no nowrap">No</th>
            <th class="nowrap">No Surat</th>
            <th class="nowrap">Kode</th>
            <th class="nowrap">Jenis</th>
            <th>Perihal</th>
            <th class="nowrap">Instansi</th>
            <th class="nowrap">Pengirim</th>
            <th class="nowrap">Penerima</th>
            <th class="nowrap">Tgl Surat</th>
            <th class="nowrap">Tgl Dibuat</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; $j = 0; @endphp
        @forelse(($surat ?? []) as $item)
            @php $j++; @endphp
            <tr class="{{ $j % 2 == 0 ? 'row-even' : '' }}">
                <td class="col-no xl-number">{{ $no++ }}</td>
                <td class="xl-text">{{ $item->no_surat }}</td>
                <td class="xl-text">{{ $item->kode_surat }}</td>
                <td class="xl-text {{ strtolower($item->jenis_surat) === 'masuk' ? 'txt-masuk' : 'txt-keluar' }}">
                    {{ ucfirst($item->jenis_surat) }}
                </td>
                <td class="xl-text" style="text-align:left;">{{ $item->perihal }}</td>
                <td class="xl-text">{{ $item->instansi ?? '-' }}</td>
                <td class="xl-text">{{ $item->pengirim }}</td>
                <td class="xl-text">{{ $item->penerima }}</td>
                <td class="xl-text nowrap">{{ $item->tanggal_surat ? Carbon::parse($item->tanggal_surat)->format('d-M-y') : '-' }}</td>
                <td class="xl-text nowrap">{{ $item->created_at   ? Carbon::parse($item->created_at)->format('d-M-y')   : '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="10" style="color:#aaa;">Tidak ada data surat.</td></tr>
        @endforelse
    </tbody>
</table>

@endif

</body>
</html>




