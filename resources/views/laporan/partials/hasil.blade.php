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

        <div class="print-date-top">
            <small>Tanggal cetak: {{ $tanggalCetak }}</small>
        </div>

        <div class="card-body p-0">

            <div class="header-bar">
                <div class="flex-grow-1 judul-print">
                    <h4>Laporan Rekapitulasi Surat</h4>
                    <h5>Tahun {{ $tahun }}</h5>
                </div>
                <span class="badge-tipe no-print">📅 Tahunan</span>
                <a class="btn-kembali-header no-print"
                   href="#"
                   data-fallback="{{ url('/laporan') }}"
                   title="Kembali">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="stat-cards">
                <div class="stat-card masuk">
                    <div class="stat-num">{{ $tmTotal }}</div>
                    <div class="stat-label">Surat Masuk</div>
                </div>
                <div class="stat-card keluar">
                    <div class="stat-num">{{ $tkTotal }}</div>
                    <div class="stat-label">Surat Keluar</div>
                </div>
                <div class="stat-card total">
                    <div class="stat-num">{{ $tmTotal + $tkTotal }}</div>
                    <div class="stat-label">Total Surat</div>
                </div>
            </div>

            <div class="section-sub">Rekapitulasi Tahunan </div>

            <div class="table-wrapper table-responsive">
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
                        @foreach($data_laporan as $row)
                            @php
                                $masuk  = $row->total_masuk  ?? 0;
                                $keluar = $row->total_keluar ?? 0;
                                $tot    = $masuk + $keluar;

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

        <div class="print-date-top">
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

            <div class="stat-cards">
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

            {{-- Detail Daftar Surat --}}
            <div class="section-sub">Detail Daftar Surat</div>

            <div class="table-wrapper detail-surat table-responsive mb-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="col-no">No</th>
                            <th>Jenis</th>
                            <th>No Surat</th>
                            <th class="lap-col-perihal">Perihal</th>
                            <th>Instansi</th>
                            <th>Tgl Surat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $noDetail = ($surat instanceof \Illuminate\Pagination\AbstractPaginator)
                                ? (($surat->currentPage() - 1) * $surat->perPage()) + 1
                                : 1;
                        @endphp

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