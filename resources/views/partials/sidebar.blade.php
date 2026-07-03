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
                 class="logo logo-lg">
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

                <li @class(['pc-item', 'active' => request()->routeIs('dashboard.*')])>
                    <a href="{{ route('dashboard.index') }}" class="pc-link d-flex align-items-center gap-2" @if(request()->routeIs('dashboard.*')) aria-current="page" @endif>
                        <i class="bi bi-house-door" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- SECTION: MASTER --}}
                <li class="pc-item pc-caption mt-1">
                    <i class="bi bi-archive" aria-hidden="true"></i>
                    <span>Master</span>
                </li>

                <li @class(['pc-item', 'active' => request()->routeIs('peserta-didik.*')])>
                    <a href="{{ route('peserta-didik.index') }}" class="pc-link d-flex align-items-center gap-2" @if(request()->routeIs('peserta-didik.*')) aria-current="page" @endif>
                        <i class="bi bi-people" aria-hidden="true"></i>
                        <span>Peserta Didik</span>
                    </a>
                </li>

                <li @class(['pc-item', 'active' => request()->routeIs('surat.index')])>
                    <a href="{{ route('surat.index') }}" class="pc-link d-flex align-items-center gap-2" @if(request()->routeIs('surat.index')) aria-current="page" @endif>
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <span>Surat</span>
                    </a>
                </li>

                <li @class(['pc-item', 'active' => request()->routeIs('surat.masuk')])>
                    <a href="{{ route('surat.masuk') }}" class="pc-link d-flex align-items-center gap-2" @if(request()->routeIs('surat.masuk')) aria-current="page" @endif>
                        <i class="bi bi-box-arrow-in-down" aria-hidden="true"></i>
                        <span>Surat Masuk</span>
                    </a>
                </li>

                <li @class(['pc-item', 'active' => request()->routeIs('surat.keluar')])>
                    <a href="{{ route('surat.keluar') }}" class="pc-link d-flex align-items-center gap-2" @if(request()->routeIs('surat.keluar')) aria-current="page" @endif>
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                        <span>Surat Keluar</span>
                    </a>
                </li>

                {{-- SECTION: LAPORAN --}}
                <li class="pc-item pc-caption mt-1">
                    <i class="bi bi-bar-chart" aria-hidden="true"></i>
                    <span>Laporan</span>
                </li>

                <li @class(['pc-item', 'active' => request()->is('laporan*')])>
                    <a href="{{ route('laporan.filter') }}" class="pc-link d-flex align-items-center gap-2" @if(request()->is('laporan*')) aria-current="page" @endif>
                        <i class="bi bi-clipboard-data" aria-hidden="true"></i>
                        <span>Rekap</span>
                    </a>
                </li>

                {{-- SECTION: ADMIN (ROLE 1) --}}
                @auth
                    @if(auth()->user()->id_role == 1)
                        <li class="pc-item pc-caption mt-1">
                            <i class="bi bi-shield-lock" aria-hidden="true"></i>
                            <span>Admin</span>
                        </li>

                        <li @class(['pc-item', 'active' => request()->routeIs('user.*')])>
                            <a href="{{ route('user.index') }}" class="pc-link d-flex align-items-center gap-2" @if(request()->routeIs('user.*')) aria-current="page" @endif>
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