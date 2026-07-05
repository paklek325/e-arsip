<nav class="pc-sidebar" aria-label="Sidebar Navigation">
    <div class="navbar-wrapper">

        {{-- ===========================
             🔷 HEADER SIDEBAR
        ============================ --}}
        <div class="m-header">
    <div class="d-flex align-items-center justify-content-center gap-2">

        <a href="https://sekolah.data.kemendikdasmen.go.id/profil-sekolah/76CF0A52-C2E3-4834-A4AD-E5ED87898CE3"
           target="_blank"
           rel="noopener noreferrer">
            <img src="{{ asset('template/dist/images/logo-1.png') }}"
                 alt="Logo SMA Babussalam"
                 class="logo logo-lg"
                 width="40" height="40"
                 loading="eager"
                 fetchpriority="high"
                 decoding="sync">
        </a>

        <a href="{{ route('dashboard.index') }}" class="b-brand">
            <span class="brand-text">
                E-<span>Arsip</span>
            </span>
        </a>

    </div>
</div>

        {{-- ===========================
             📋 MENU NAVIGASI
        ============================ --}}
        <div class="navbar-content">
            <ul class="pc-navbar list-unstyled px-2 mt-1 mb-1">

                {{-- SECTION: MAIN MENU --}}
                <li class="pc-item pc-caption">
                    <i class="bi bi-grid" aria-hidden="true"></i>
                    <span>Main Menu</span>
                </li>

                @php
                    $isDashboard    = request()->routeIs('dashboard.*');
                    $isPeserta      = request()->routeIs('peserta-didik.*');
                    $isSurat        = request()->routeIs('surat.index');
                    $isMasuk        = request()->routeIs('surat.masuk');
                    $isKeluar       = request()->routeIs('surat.keluar');
                    $isLaporan      = request()->is('laporan*');
                    $isUser         = request()->routeIs('user.*');
                @endphp

                <li @class(['pc-item', 'active' => $isDashboard])>
                    <a href="{{ route('dashboard.index') }}"
                       @class(['pc-link d-flex align-items-center gap-2', 'active' => $isDashboard])
                       @if($isDashboard) aria-current="page" @endif>
                        <i class="bi bi-house-door" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- SECTION: MASTER --}}
                <li class="pc-item pc-caption mt-1">
                    <i class="bi bi-archive" aria-hidden="true"></i>
                    <span>Master</span>
                </li>

                <li @class(['pc-item', 'active' => $isPeserta])>
                    <a href="{{ route('peserta-didik.index') }}"
                       @class(['pc-link d-flex align-items-center gap-2', 'active' => $isPeserta])
                       @if($isPeserta) aria-current="page" @endif>
                        <i class="bi bi-people" aria-hidden="true"></i>
                        <span>Peserta Didik</span>
                    </a>
                </li>

                <li @class(['pc-item', 'active' => $isSurat])>
                    <a href="{{ route('surat.index') }}"
                       @class(['pc-link d-flex align-items-center gap-2', 'active' => $isSurat])
                       @if($isSurat) aria-current="page" @endif>
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <span>Surat</span>
                    </a>
                </li>

                <li @class(['pc-item', 'active' => $isMasuk])>
                    <a href="{{ route('surat.masuk') }}"
                       @class(['pc-link d-flex align-items-center gap-2', 'active' => $isMasuk])
                       @if($isMasuk) aria-current="page" @endif>
                        <i class="bi bi-box-arrow-in-down" aria-hidden="true"></i>
                        <span>Surat Masuk</span>
                    </a>
                </li>

                <li @class(['pc-item', 'active' => $isKeluar])>
                    <a href="{{ route('surat.keluar') }}"
                       @class(['pc-link d-flex align-items-center gap-2', 'active' => $isKeluar])
                       @if($isKeluar) aria-current="page" @endif>
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                        <span>Surat Keluar</span>
                    </a>
                </li>

                {{-- SECTION: LAPORAN --}}
                <li class="pc-item pc-caption mt-1">
                    <i class="bi bi-bar-chart" aria-hidden="true"></i>
                    <span>Laporan</span>
                </li>

                <li @class(['pc-item', 'active' => $isLaporan])>
                    <a href="{{ route('laporan.filter') }}"
                       @class(['pc-link d-flex align-items-center gap-2', 'active' => $isLaporan])
                       @if($isLaporan) aria-current="page" @endif>
                        <i class="bi bi-clipboard-data" aria-hidden="true"></i>
                        <span>Rekap</span>
                    </a>
                </li>

                {{-- SECTION: ADMIN (ROLE 1) --}}
                @auth
                    @if(strtolower(auth()->user()->role?->name ?? '') === 'kepala staf')
                        <li class="pc-item pc-caption mt-1">
                            <i class="bi bi-shield-lock" aria-hidden="true"></i>
                            <span>Admin</span>
                        </li>

                        <li @class(['pc-item', 'active' => $isUser])>
                            <a href="{{ route('user.index') }}"
                               @class(['pc-link d-flex align-items-center gap-2', 'active' => $isUser])
                               @if($isUser) aria-current="page" @endif>
                                <i class="bi bi-person" aria-hidden="true"></i>
                                <span>User</span>
                            </a>
                        </li>
                    @endif
                @endauth

            </ul>
        </div>
    </div>
</nav>