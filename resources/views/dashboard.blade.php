@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i>Home</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
</ol>
@endsection

@section('content')

    {{-- ====== Header ====== --}}
    <div class="pt-2 d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-house-door-fill fs-5 text-primary"></i>
            <h5 class="fw-bold text-primary mb-0">Dashboard</h5>
        </div>
    </div>

    @php
        $currentRole = strtolower(auth()->user()->role?->name ?? '');
        $isKepala    = $currentRole === 'kepala staf';
        $isStaf      = $currentRole === 'staf';
    @endphp

    {{-- ====== Cards ====== --}}
    @php
        // Palet warna yang sama dipakai untuk badge rombel & donut chart Peserta Didik,
        // supaya warnanya konsisten di seluruh dashboard.
        $donutPalette = ['#ffc107','#fd7e14','#0dcaf0','#6f42c1','#20c997','#dc3545','#0d6efd'];

        $cards = [];

        if ($isKepala) {
            $cards[] = [
                'label'  => 'Users',
                'type'   => 'stat',
                'count'  => $totalUser ?? 0,
                'color'  => 'primary',
                'icon'   => 'bi bi-people-fill',
                'route'  => route('user.index'),
                'kepala' => $totalKepala ?? 0,
                'staf'   => $totalStaf ?? 0,
            ];
        }

        $cards[] = [
            'label'        => 'Surat',
            'type'         => 'stat',
            'count'        => $totalSurat ?? 0,
            'color'        => 'success',
            'icon'         => 'bi bi-envelope-fill',
            'route'        => route('surat.index'),
            'route_masuk'  => route('surat.masuk'),
            'route_keluar' => route('surat.keluar'),
            'masuk'        => $totalSuratMasukAll ?? 0,
            'keluar'       => $totalSuratKeluarAll ?? 0,
        ];

        $cards[] = [
            'label'         => 'Peserta Didik',
            'type'          => 'stat',
            'count'         => $totalPesertaDidikAll ?? 0,
            'color'         => 'warning',
            'icon'          => 'bi bi-people-fill',
            'route'         => route('peserta-didik.index'),
            'per_rombel'    => $peserta_didikPerRombelAll ?? [],
            'rombel_gender' => $peserta_didikPerRombelGenderAll ?? [],
            'laki'          => $peserta_didikLakiAll ?? 0,
            'perempuan'     => $peserta_didikPerempuanAll ?? 0,
        ];

        $cards[] = [
            'label' => 'Kode',
            'type'  => 'stat',
            'count' => $totalKode ?? 0,
            'color' => 'danger',
            'icon'  => 'bi bi-upc',
            'route' => route('kode.index'),
        ];

        $cards[] = [
            'label' => 'Rekap',
            'type'  => 'menu',
            'color' => 'info',
            'icon'  => 'bi bi-bar-chart-fill',
            'route' => route('laporan.filter'),
        ];

        $maxCount = collect($cards)->where('type', 'stat')->max('count');
        $maxCount = $maxCount > 0 ? $maxCount : 1;
    @endphp

    <div class="row g-2 mb-4 {{ $isKepala ? 'row-cols-xl-5' : 'row-cols-xl-4' }} row-cols-md-3 row-cols-2">

        @foreach ($cards as $card)
            @php
                $progressWidth = isset($card['count']) && $card['count'] > 0
                    ? max(round(($card['count'] / $maxCount) * 100), 5)
                    : 0;
            @endphp

            <div class="col fade-up">
                <div class="card dashboard-card bg-{{ $card['color'] }}-light h-100 clickable-card"
                     data-url="{{ $card['route'] }}">

                    <div class="card-body text-center">

                        <div class="icon-wrapper mx-auto">
                            <i class="{{ $card['icon'] }} icon-animated text-{{ $card['color'] }}"></i>
                        </div>

                        <h6 class="dashboard-label">{{ $card['label'] }}</h6>

                        {{-- CARD STATISTIK --}}
                        @if($card['type'] === 'stat')

                            <div class="dashboard-number" data-count="{{ $card['count'] }}">0</div>

                            <div class="card-sub">
                                @if($card['label'] === 'Users')
                                    <div class="small text-muted dashboard-card-sub-text">
                                        <i class="bi bi-person-badge me-1"></i>Kepala Staf &amp; Staf
                                    </div>

                                @elseif($card['label'] === 'Surat')
                                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-1">
                                        <a href="{{ $card['route_masuk'] }}"
                                           class="badge-surat-link badge-surat-masuk"
                                           onclick="event.stopPropagation()">
                                            <i class="bi bi-envelope-arrow-down-fill me-1"></i>
                                            Masuk <strong>{{ $card['masuk'] }}</strong>
                                        </a>
                                        <a href="{{ $card['route_keluar'] }}"
                                           class="badge-surat-link badge-surat-keluar"
                                           onclick="event.stopPropagation()">
                                            <i class="bi bi-envelope-arrow-up-fill me-1"></i>
                                            Keluar <strong>{{ $card['keluar'] }}</strong>
                                        </a>
                                    </div>

                                @elseif($card['label'] === 'Peserta Didik')
                                    <div class="d-flex flex-wrap justify-content-center gap-1">
                                        @forelse($card['per_rombel'] as $rombel => $jumlah)
                                            @php
                                                $jWarna = $donutPalette[$loop->index % count($donutPalette)];
                                                $rg     = $card['rombel_gender'][$rombel] ?? ['laki' => 0, 'perempuan' => 0];
                                            @endphp
                                            <span class="badge rombel-badge"
                                                  style="background-color: {{ $jWarna }} !important; color:#fff !important;">
                                                Rombel {{ $rombel }}: {{ $jumlah }} (L:{{ $rg['laki'] }} P:{{ $rg['perempuan'] }})
                                            </span>
                                        @empty
                                            <span class="text-muted fst-italic small">Belum ada data</span>
                                        @endforelse
                                    </div>
                                    <div class="d-flex justify-content-center gap-1 mt-1">
                                        <span class="badge badge-gender-laki d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-gender-male"></i> L: {{ $card['laki'] }}
                                        </span>
                                        <span class="badge badge-gender-perempuan d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-gender-female"></i> P: {{ $card['perempuan'] }}
                                        </span>
                                    </div>

                                @elseif($card['label'] === 'Kode')
                                    <div class="small text-muted dashboard-card-sub-text">
                                        <i class="bi bi-archive me-1"></i>Kode Arsip <br> Surat Masuk &amp; Surat Keluar
                                    </div>

                                @else
                                    {{-- spacer agar tinggi card-sub seragam --}}
                                @endif
                            </div>

                            <div class="progress w-100 dashboard-progress">
                                <div class="progress-bar bg-{{ $card['color'] }}" style="width:{{ $progressWidth }}%"></div>
                            </div>

                        {{-- CARD REKAP --}}
                        @elseif($card['type'] === 'menu')

                            <div class="card-sub d-flex flex-column align-items-center justify-content-center">
                                <p class="small text-muted mb-2 px-1 text-center dashboard-rekap-text">
                                    Kelola, filter dan cetak laporan arsip
                                </p>
                                <span class="badge badge-info">
                                    <i class="bi bi-file-text me-1"></i>Buka Laporan
                                </span>
                            </div>

                        @endif

                    </div>
                </div>
            </div>
        @endforeach

    </div>

    {{-- ====== Charts Section ====== --}}
    @php
        // Semua tahun (agregat, dipakai sebagai data default chart Peserta Didik)
        $rombelArrAll    = collect($peserta_didikPerRombelAll ?? [])->toArray();
        $rombelLabelsAll = array_keys($rombelArrAll);
        $rombelDataAll   = array_values($rombelArrAll);

        // Jumlah rombel terbanyak di antara semua tahun angkatan (untuk palet warna donut)
        $maxRombelCount = count($rombelLabelsAll);
        foreach (($peserta_didikPerTahun ?? []) as $tahunData) {
            $maxRombelCount = max($maxRombelCount, count($tahunData['rombel'] ?? []));
        }

        $donutColors = [];
        $maxR = max($maxRombelCount, 1);
        for ($i = 0; $i < $maxR; $i++) {
            $donutColors[] = $donutPalette[$i % count($donutPalette)];
        }
    @endphp

    <div class="row g-3 mb-4">

        {{-- Bar Chart: Surat Masuk vs Keluar --}}
        <div class="col-lg-5 col-12 fade-up">
            <div class="card chart-card shadow-sm border-0 h-100 d-flex flex-column">
                <div class="card-header bg-transparent border-bottom px-3 pt-3 pb-2">
                    <div class="chart-header-title">
                        <i class="bi bi-bar-chart-line-fill text-success fs-5"></i>
                        <h6 class="fw-bold mb-0 text-success">Statistik Surat</h6>
                    </div>
                    <div class="chart-header-controls">
                        <span class="badge badge-success" id="badgeTotalSurat">
                            Total: {{ ($totalSuratMasukAll ?? 0) + ($totalSuratKeluarAll ?? 0) }}
                        </span>
                        <div class="d-flex align-items-center gap-1 ms-auto">
                            <label for="filterTahunSurat" class="small text-muted mb-0">Tahun</label>
                            <input type="number" id="filterTahunSurat" class="form-control form-control-sm"
                                   min="{{ now()->year - 10 }}"
                                   max="{{ now()->year }}"
                                   placeholder="Semua">
                        </div>
                    </div>
                </div>
                <div class="card-body p-3 flex-grow-1 chart-body">
                    <canvas id="chartSurat"></canvas>
                    <div id="chartSuratEmpty" style="display:none; position:absolute; inset:0;
                         align-items:center; justify-content:center; flex-direction:column;
                         gap:8px; color:var(--bs-secondary,#6c757d); font-size:.88rem; pointer-events:none;">
                        <i class="bi bi-inbox fs-2 d-block text-center mb-1"></i>
                        Belum ada data untuk tahun ini
                    </div>
                </div>
            </div>
        </div>

        {{-- Donut Chart: Peserta Didik per Rombel --}}
        <div class="col-lg-7 col-12 fade-up">
            <div class="card chart-card shadow-sm border-0 h-100 d-flex flex-column">
                <div class="card-header bg-transparent border-bottom px-3 pt-3 pb-2">
                    <div class="chart-header-title">
                        <i class="bi bi-pie-chart-fill text-warning fs-5"></i>
                        <h6 class="fw-bold mb-0 text-warning">Statistik Peserta Didik</h6>
                    </div>
                    <div class="chart-header-controls">
                        <span class="badge badge-warning text-nowrap" id="badgeTotalPesertaDidik">
                            Total: {{ $totalPesertaDidikAll ?? 0 }} peserta_didik
                        </span>
                        <span class="badge badge-gender-laki d-inline-flex align-items-center gap-1" id="badgePesertaDidikLaki">
                            <i class="bi bi-gender-male"></i>
                            L: <span id="textPesertaDidikLaki">{{ $peserta_didikLakiAll ?? 0 }}</span>
                        </span>
                        <span class="badge badge-gender-perempuan d-inline-flex align-items-center gap-1" id="badgePesertaDidikPerempuan">
                            <i class="bi bi-gender-female"></i>
                            P: <span id="textPesertaDidikPerempuan">{{ $peserta_didikPerempuanAll ?? 0 }}</span>
                        </span>
                        <div class="d-flex align-items-center gap-1 ms-auto">
                            <label for="filterTahunPesertaDidik" class="small text-muted mb-0">Tahun</label>
                            <input type="number" id="filterTahunPesertaDidik" class="form-control form-control-sm"
                                   min="{{ now()->year - 10 }}"
                                   max="{{ now()->year }}"
                                   placeholder="Semua">
                        </div>
                    </div>
                </div>
                <div class="card-body p-3 flex-grow-1 d-flex align-items-center justify-content-center chart-body-donut">
                    <canvas id="chartPesertaDidik" class="chart-canvas-donut"></canvas>
                </div>
            </div>
        </div>

        {{-- Line Chart: Tren Surat Bulanan --}}
        <div class="col-12 fade-up">
            <div class="card chart-card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom px-3 pt-3 pb-2">
                    <div class="chart-header-title">
                        <i class="bi bi-graph-up-arrow text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0 text-primary">
                            Tren Surat Bulanan
                            <span class="text-muted fw-normal chart-year-label">({{ now()->year }})</span>
                        </h6>
                    </div>
                </div>
                <div class="card-body p-3 chart-body-line">
                    <canvas id="chartTren"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- ====== Tabel Users (hanya Kepala Staf) ====== --}}
    @if ($isKepala)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center gap-2 py-3">
                <i class="bi bi-people-fill text-primary fs-5"></i>
                <h6 class="fw-bold mb-0 text-primary">Daftar Pengguna</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 users-table">
                    <thead>
                        <tr>
                            <th class="text-center col-no">No</th>
                            <th class="col-user">User</th>
                            <th class="col-email">Email</th>
                            <th class="text-center col-role">Role</th>
                            <th class="text-center col-date">Dibuat pada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers ?? [] as $user)
                            <tr>
                                <td class="text-center fw-semibold text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->foto ? asset('storage/foto_admin/' . $user->foto) : asset('assets/img/default_staf.png') }}"
                                             onerror="this.onerror=null;this.src='{{ asset('assets/img/default_staf.png') }}'"
                                             class="rounded-circle me-3 border shadow-sm users-table-avatar"
                                             loading="lazy" decoding="async"
                                             width="35" height="35"
                                             alt="{{ $user->name }}">
                                        <span class="fw-semibold text-truncate users-table-name">
                                            {{ $user->name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-muted text-truncate users-table-email">
                                    {{ $user->email }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">
                                        {{ $user->role->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center text-muted small">
                                    {{ $user->created_at ? $user->created_at->format('d M Y H:i') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-exclamation-circle me-2"></i>Tidak ada pengguna.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
@endpush

@push('page-js')
  @vite(['resources/js/dashboard.js'])
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Dark mode detection ──────────────────────────────────────────
    // Deteksi [data-theme="dark"] pada <html> atau <body>
    function isDark() {
        return document.documentElement.getAttribute('data-theme') === 'dark'
            || document.body.getAttribute('data-theme') === 'dark';
    }

    // Warna dinamis berdasarkan mode
    function chartColors() {
        const dark = isDark();
        return {
            textColor    : dark ? '#cbd5e1' : '#6c757d',
            gridColor    : dark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.05)',
            borderColor  : dark ? '#334155' : '#e5e7eb',
            tooltipBg    : dark ? '#1e293b' : '#fff',
            tooltipText  : dark ? '#f1f5f9' : '#1f2937',
            tooltipBorder: dark ? '#334155' : '#e5e7eb',
        };
    }

    // Warna khusus untuk keterangan/legend donut Peserta Didik (IPA, IPS, & persentase)
    // Dibuat putih penuh saat dark mode agar lebih kontras dan mudah dibaca.
    function legendColor() {
        return isDark() ? '#ffffff' : '#6c757d';
    }

    // ── Global defaults ──────────────────────────────────────────────
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.font.size   = 12;

    function applyGlobalDefaults() {
        const c = chartColors();
        Chart.defaults.color = c.textColor;
    }
    applyGlobalDefaults();

    // ── Helper: shared tooltip style ────────────────────────────────
    function tooltipPlugin() {
        const c = chartColors();
        return {
            backgroundColor : c.tooltipBg,
            titleColor      : c.tooltipText,
            bodyColor       : c.tooltipText,
            borderColor     : c.tooltipBorder,
            borderWidth     : 1,
            padding         : 10,
            cornerRadius    : 8,
            displayColors   : true,
        };
    }

    // ── Helper: shared scale style ───────────────────────────────────
    function scaleY(extra = {}) {
        const c = chartColors();
        return {
            beginAtZero: true,
            suggestedMax: 0,
            ticks: {
                precision: 0,
                stepSize: 1,
                color: c.textColor,
                callback: function(value) {
                    // Sembunyikan tick jika max data = 0 dan nilai bukan 0
                    if (this.chart.data.datasets.every(ds => ds.data.every(v => v === 0)) && value !== 0) {
                        return null;
                    }
                    return value;
                }
            },
            grid : { color: c.gridColor, borderDash: [4,4] },
            border: { dash: [4,4], color: 'transparent' },
            ...extra
        };
    }
    function scaleX(extra = {}) {
        const c = chartColors();
        return {
            ticks: { color: c.textColor },
            grid : { display: false },
            ...extra
        };
    }

    // ════════════════════════════════════════════════════════════════
    // 1. BAR CHART — Surat Masuk vs Keluar
    // ════════════════════════════════════════════════════════════════
    const ctxSurat = document.getElementById('chartSurat');
    let chartSurat = null;

    // Data dari PHP
    const dataSurat = {
        tahun_ini: {
            masuk : {{ $totalSuratMasuk ?? 0 }},
            keluar: {{ $totalSuratKeluar ?? 0 }},
        },
        semua_tahun: {
            masuk : {{ $totalSuratMasukAll ?? 0 }},
            keluar: {{ $totalSuratKeluarAll ?? 0 }},
        }
    };

    if (ctxSurat) {
        chartSurat = new Chart(ctxSurat, {
            type: 'bar',
            data: {
                labels: ['Surat Masuk', 'Surat Keluar'],
                datasets: [{
                    label: 'Jumlah Surat',
                    data: [dataSurat.semua_tahun.masuk, dataSurat.semua_tahun.keluar],
                    backgroundColor: [
                        'rgba(25,135,84,0.75)',
                        'rgba(13,202,240,0.75)'
                    ],
                    borderColor: [
                        'rgba(25,135,84,1)',
                        'rgba(13,202,240,1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 10,
                    borderSkipped: false,
                    barThickness: 60,
                    maxBarThickness: 80,
                }]
            },
            options: {
                responsive           : true,
                maintainAspectRatio  : false,   // biar chart isi tinggi container
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipPlugin(),
                        callbacks: { label: ctx => ` ${ctx.parsed.y} surat` }
                    }
                },
                scales: {
                    y: scaleY(),
                    x: {
                        ...scaleX(),
                        ticks: {
                            color: chartColors().textColor,
                            font: { size: 13, weight: '600' },
                        }
                    }
                }
            }
        });
    }

    // Data surat per-tahun (dari PHP, untuk spinner)
    const dataSuratPerTahun = @json($trenSuratPerTahun ?? []);

    const filterTahunSurat = document.getElementById('filterTahunSurat');
    const badgeTotalSurat  = document.getElementById('badgeTotalSurat');

    function updateSuratChart() {
        if (!chartSurat) return;
        const tahunVal  = filterTahunSurat ? filterTahunSurat.value.trim() : '';
        const emptyEl   = document.getElementById('chartSuratEmpty');
        const canvasEl  = document.getElementById('chartSurat');

        let masuk, keluar, labelBadge, isEmpty = false;

        if (tahunVal === '') {
            // Kosong = semua tahun
            masuk      = dataSurat.semua_tahun.masuk;
            keluar     = dataSurat.semua_tahun.keluar;
            labelBadge = 'Total: ' + (masuk + keluar);
        } else if (dataSuratPerTahun[tahunVal]) {
            // Tahun ada datanya
            masuk      = dataSuratPerTahun[tahunVal].masuk  || 0;
            keluar     = dataSuratPerTahun[tahunVal].keluar || 0;
            labelBadge = `Total: ${masuk + keluar} (${tahunVal})`;
        } else {
            // Tahun tidak ada data
            masuk      = 0;
            keluar     = 0;
            labelBadge = `Tidak ada data (${tahunVal})`;
            isEmpty    = true;
        }

        // Toggle empty state overlay
        if (emptyEl) emptyEl.style.display = isEmpty ? 'flex' : 'none';
        if (canvasEl) canvasEl.style.opacity = isEmpty ? '0' : '1';

        chartSurat.data.datasets[0].data = [masuk, keluar];
        chartSurat.update();
        if (badgeTotalSurat) {
            badgeTotalSurat.textContent = labelBadge;
        }
    }

    if (filterTahunSurat) {
        filterTahunSurat.addEventListener('input', updateSuratChart);
    }

    // ════════════════════════════════════════════════════════════════
    // 2. DONUT CHART — Peserta Didik per Rombel
    // ════════════════════════════════════════════════════════════════
    const ctxPesertaDidik = document.getElementById('chartPesertaDidik');
    let chartPesertaDidik = null;

    // Data dari PHP — peserta_didik dikelompokkan per tahun angkatan
    const peserta_didikPerTahun = @json($peserta_didikPerTahun ?? []);

    const dataPesertaDidikSemua = {
        labels   : @json($rombelLabelsAll),
        data     : @json($rombelDataAll),
        total    : {{ $totalPesertaDidikAll ?? 0 }},
        laki     : {{ $peserta_didikLakiAll ?? 0 }},
        perempuan: {{ $peserta_didikPerempuanAll ?? 0 }},
        rombelGender: @json($peserta_didikPerRombelGenderAll ?? []),
    };

    const donutColors   = @json($donutColors);
    // Warna khusus untuk gender, dipakai konsisten di seluruh tampilan
    const genderColors  = { laki: '#3b82f6', perempuan: '#ec4899' };

    function donutBorderColor() {
        return isDark() ? '#1e293b' : '#ffffff';
    }

    // Ambil data sesuai tahun
    // null/kosong = semua tahun; tahun diisi tapi kosong = return data kosong
    function getPesertaDidikData(tahun) {
        if (tahun === null || tahun === undefined || tahun === '') {
            // Tidak ada filter — tampilkan semua tahun
            return { ...dataPesertaDidikSemua, labelTahun: '' };
        }
        const key = String(tahun);
        if (peserta_didikPerTahun[key]) {
            const t = peserta_didikPerTahun[key];
            return {
                labels    : Object.keys(t.rombel),
                data      : Object.values(t.rombel),
                total     : t.total,
                laki      : t.laki,
                perempuan : t.perempuan,
                rombelGender: t.rombelGender || {},
                labelTahun: ` ${tahun}`,
            };
        }
        // Tahun ada di input tapi tidak ada di data — tampilkan kosong
        return {
            labels    : [],
            data      : [],
            total     : 0,
            laki      : 0,
            perempuan : 0,
            rombelGender: {},
            labelTahun: ` ${tahun}`,
            kosong    : true,
        };
    }

    let pesertaDidikGenderMap = {};

    function renderPesertaDidikChart(tahun) {
        const d      = getPesertaDidikData(tahun);
        const colors = donutColors.slice(0, d.labels.length);
        const isEmpty = d.data.length === 0 || d.data.every(v => v === 0);
        pesertaDidikGenderMap = d.rombelGender || {};

        const badgePesertaDidik = document.getElementById('badgeTotalPesertaDidik');
        if (badgePesertaDidik) {
            if (d.kosong) {
                badgePesertaDidik.textContent = `Tidak ada data${d.labelTahun ? ' • Angk. ' + d.labelTahun.trim() : ''}`;
            } else if (d.labelTahun) {
                badgePesertaDidik.textContent = `Total: ${d.total} Peserta Didik • Angk. ${d.labelTahun.trim()}`;
            } else {
                badgePesertaDidik.textContent = `Total: ${d.total} Peserta Didik (Semua)`;
            }
        }

        const elLaki      = document.getElementById('textPesertaDidikLaki');
        const elPerempuan = document.getElementById('textPesertaDidikPerempuan');
        if (elLaki)      elLaki.textContent      = d.laki;
        if (elPerempuan) elPerempuan.textContent = d.perempuan;

        // Tampilkan pesan kosong jika tidak ada data
        let emptyMsg = document.getElementById('chartPesertaDidikEmpty');
        if (!emptyMsg) {
            emptyMsg = document.createElement('div');
            emptyMsg.id = 'chartPesertaDidikEmpty';
            emptyMsg.style.cssText = [
                'display:none', 'position:absolute', 'inset:0',
                'align-items:center', 'justify-content:center',
                'flex-direction:column', 'gap:8px',
                'color:var(--bs-secondary,#6c757d)', 'font-size:.88rem',
                'pointer-events:none'
            ].join(';');
            emptyMsg.innerHTML = '<i class="bi bi-inbox fs-2 d-block text-center mb-1"></i>Belum ada data untuk tahun ini';
            ctxPesertaDidik.parentNode.style.position = 'relative';
            ctxPesertaDidik.parentNode.appendChild(emptyMsg);
        }

        if (isEmpty) {
            ctxPesertaDidik.style.display  = 'none';
            emptyMsg.style.display  = 'flex';
        } else {
            ctxPesertaDidik.style.display  = '';
            emptyMsg.style.display  = 'none';
        }

        if (chartPesertaDidik) {
            chartPesertaDidik.data.labels = d.labels || [];
            if (chartPesertaDidik.data.datasets && chartPesertaDidik.data.datasets[0]) {
                chartPesertaDidik.data.datasets[0].data = isEmpty ? [1] : d.data;
                chartPesertaDidik.data.datasets[0].backgroundColor = isEmpty
                    ? [isDark() ? '#27324b' : '#e5e7eb']
                    : colors;
                chartPesertaDidik.data.datasets[0].hoverOffset = isEmpty ? 0 : 12;
            }
            chartPesertaDidik.update();
        } else {
            chartPesertaDidik = new Chart(ctxPesertaDidik, {
                type: 'doughnut',
                data: {
                    labels: isEmpty ? [] : d.labels,
                    datasets: [{
                        data           : isEmpty ? [1] : d.data,
                        backgroundColor: isEmpty ? [isDark() ? '#27324b' : '#e5e7eb'] : colors,
                        borderWidth    : 3,
                        borderColor    : donutBorderColor(),
                        hoverOffset    : isEmpty ? 0 : 12,
                    }]
                },
                options: {
                    responsive        : true,
                    maintainAspectRatio: true,
                    cutout            : '62%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding       : 16,
                                usePointStyle : true,
                                pointStyle    : 'circle',
                                font          : { size: 12 },
                                color         : legendColor(),
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const genderMap = pesertaDidikGenderMap || {};
                                return (data.labels || []).map((label, i) => {
                                    const dataset = data.datasets[0];
                                    if (!dataset || !dataset.data) return {};
                                    const val  = dataset.data[i] || 0;
                                    const pct  = total > 0 ? Math.round((val / total) * 100) : 0;
                                    const g    = genderMap[label];
                                    const genderText = g ? ` (L:${g.laki} P:${g.perempuan})` : '';
                                    return {
                                        text       : `Rombel ${label}: ${val}${genderText} - ${pct}%`,
                                        fillStyle  : dataset.backgroundColor?.[i] || '#e5e7eb',
                                        strokeStyle: dataset.backgroundColor?.[i] || '#e5e7eb',
                                        lineWidth  : 0,
                                        pointStyle : 'circle',
                                        fontColor  : legendColor(),
                                        index      : i,
                                        hidden     : false,
                                    };
                                });
                                }
                            }
                        },
                        tooltip: {
                            ...tooltipPlugin(),
                            callbacks: {
                                label: ctx => {
                                    if (!ctx.dataset || !ctx.dataset.data) return '';
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct   = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                    const g     = (pesertaDidikGenderMap || {})[ctx.label];
                                    const genderText = g ? ` (L:${g.laki} P:${g.perempuan})` : '';
                                    return ` Rombel ${ctx.label}: ${ctx.parsed} peserta didik${genderText} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    if (ctxPesertaDidik) {
        renderPesertaDidikChart(null);
    }

    // Input spinner filter tahun Peserta Didik
    const filterTahunPesertaDidik = document.getElementById('filterTahunPesertaDidik');
    if (filterTahunPesertaDidik) {
        filterTahunPesertaDidik.addEventListener('input', function () {
            const tahun = this.value ? parseInt(this.value, 10) : null;
            renderPesertaDidikChart(tahun);
        });
    }

    // ════════════════════════════════════════════════════════════════
    // 3. LINE CHART — Tren Surat Bulanan
    // ════════════════════════════════════════════════════════════════
    const ctxTren = document.getElementById('chartTren');
    let chartTren = null;

    if (ctxTren) {
        const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

        chartTren = new Chart(ctxTren, {
            type: 'line',
            data: {
                labels: bulan,
                datasets: [
                    {
                        label           : 'Surat Masuk',
                        data            : @json($trenSuratMasuk ?? array_fill(0, 12, 0)),
                        borderColor     : 'rgba(25,135,84,1)',
                        backgroundColor : 'rgba(25,135,84,0.1)',
                        borderWidth     : 2.5,
                        pointRadius     : 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: isDark() ? '#1e293b' : '#fff',
                        pointBorderColor: 'rgba(25,135,84,1)',
                        pointBorderWidth: 2,
                        tension         : 0.4,
                        fill            : true,
                    },
                    {
                        label           : 'Surat Keluar',
                        data            : @json($trenSuratKeluar ?? array_fill(0, 12, 0)),
                        borderColor     : 'rgba(13,202,240,1)',
                        backgroundColor : 'rgba(13,202,240,0.1)',
                        borderWidth     : 2.5,
                        pointRadius     : 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: isDark() ? '#1e293b' : '#fff',
                        pointBorderColor: 'rgba(13,202,240,1)',
                        pointBorderWidth: 2,
                        tension         : 0.4,
                        fill            : true,
                    }
                ]
            },
            options: {
                responsive        : true,
                maintainAspectRatio: true,
                interaction       : { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position : 'top',
                        align    : 'end',
                        labels   : {
                            usePointStyle: true,
                            pointStyle   : 'circle',
                            font         : { size: 12 },
                            color        : chartColors().textColor,
                            padding      : 16,
                        }
                    },
                    tooltip: {
                        ...tooltipPlugin(),
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} surat`
                        }
                    }
                },
                scales: {
                    y: scaleY(),
                    x: scaleX()
                }
            }
        });
    }

    // ════════════════════════════════════════════════════════════════
    // DARK MODE OBSERVER — update semua chart saat tema berubah
    // ════════════════════════════════════════════════════════════════
    function updateChartsForTheme() {
        const c = chartColors();

        // Update global color
        Chart.defaults.color = c.textColor;

        // Fungsi update scale warna
        function refreshScales(chart) {
            if (!chart) return;
            ['x','y'].forEach(axis => {
                if (chart.options.scales?.[axis]) {
                    chart.options.scales[axis].ticks.color = c.textColor;
                    if (axis === 'y') {
                        chart.options.scales[axis].grid.color = c.gridColor;
                    }
                }
            });
        }

        // Update tooltip & legend warna semua chart
        [chartSurat, chartTren].forEach(chart => {
            if (!chart) return;
            refreshScales(chart);
            if (chart.options.plugins?.tooltip) {
                Object.assign(chart.options.plugins.tooltip, tooltipPlugin());
            }
            chart.update('none'); // update tanpa animasi
        });

        // Donut: border warna + legend
        if (chartPesertaDidik) {
            chartPesertaDidik.data.datasets[0].borderColor = donutBorderColor();
            if (chartPesertaDidik.options.plugins?.legend?.labels) {
                chartPesertaDidik.options.plugins.legend.labels.color = legendColor();
                // generateLabels perlu tahu warna baru — trigger via callback override
                const origGen = chartPesertaDidik.options.plugins.legend.labels.generateLabels;
                if (!origGen._isWrapped) {
                    chartPesertaDidik.options.plugins.legend.labels.generateLabels = function(chart) {
                        const items = origGen.call(this, chart);
                        const col = legendColor();
                        if (items && Array.isArray(items)) {
                            items.forEach(item => { item.fontColor = col; });
                        }
                        return items || [];
                    };
                    chartPesertaDidik.options.plugins.legend.labels.generateLabels._isWrapped = true;
                }
            }
            if (chartPesertaDidik.options.plugins?.tooltip) {
                Object.assign(chartPesertaDidik.options.plugins.tooltip, tooltipPlugin());
            }
            chartPesertaDidik.update('none');
        }

        // Line: titik warna latar
        if (chartTren) {
            const ptColor = isDark() ? '#1e293b' : '#fff';
            chartTren.data.datasets.forEach(ds => {
                ds.pointBackgroundColor = ptColor;
            });
            if (chartTren.options.plugins?.legend?.labels) {
                chartTren.options.plugins.legend.labels.color = c.textColor;
            }
            chartTren.update('none');
        }
    }

    // Observe perubahan atribut data-theme pada <html> dan <body>
    const themeObserver = new MutationObserver(updateChartsForTheme);
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme', 'class'] });
    themeObserver.observe(document.body,            { attributes: true, attributeFilter: ['data-theme', 'class'] });

});
</script>
@endpush