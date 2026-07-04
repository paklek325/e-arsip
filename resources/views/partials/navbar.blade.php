<style>
    /* =========================================
       NAVBAR THEME TOGGLE — Sun/Moon Switch
       Diperbaiki: ukuran, posisi icon, dan
       transisi agar halus di semua state.
    ========================================= */

    /* === WRAPPER LABEL === */
    header.pc-header .theme-toggle-label,
    .theme-toggle-label {
        all: revert;
        box-sizing: border-box !important;
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        width: 52px !important;
        height: 28px !important;
        min-width: 52px !important;
        max-width: 52px !important;
        padding: 0 !important;
        margin: 0 !important;
        cursor: pointer !important;
        line-height: 0 !important;
        vertical-align: middle !important;
    }

    /* === HIDDEN CHECKBOX === */
    header.pc-header .theme-toggle-label input[type="checkbox"] {
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        opacity: 0 !important;
        cursor: pointer !important;
        z-index: 3 !important;
        appearance: none !important;
    }

    /* === TRACK (background pill) === */
    header.pc-header .theme-toggle-label .tt-track {
        box-sizing: border-box !important;
        position: absolute !important;
        top: 0 !important; left: 0 !important;
        right: 0 !important; bottom: 0 !important;
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 999px !important;
        overflow: hidden !important;
        /* Light mode: warm white */
        background: #f0f4ff !important;
        border: 1.5px solid #c7d2fe !important;
        box-shadow: inset 0 1px 3px rgba(0,0,0,.08), 0 1px 3px rgba(0,0,0,.06) !important;
        transition: background .35s ease, border-color .35s ease, box-shadow .35s ease !important;
        z-index: 1 !important;
        display: block !important;
    }

    /* === THUMB (sliding circle) === */
    /*
       Track: 52px wide, border 1.5px each side → inner = 49px
       Thumb: 20px, gap from edge: 3px
       Left at rest  : 3px
       Left at active: 49 - 20 - 3 = 26px  (stays fully inside track)
    */
    header.pc-header .theme-toggle-label .tt-thumb {
        box-sizing: border-box !important;
        position: absolute !important;
        top: 50% !important;
        left: 4px !important;
        transform: translateY(-50%) !important;
        width: 20px !important;
        height: 20px !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 50% !important;
        background: #fff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 1px 5px rgba(0,0,0,.22), 0 0 0 1px rgba(0,0,0,.04) !important;
        transition: left .35s cubic-bezier(.4,0,.2,1), background .35s ease, box-shadow .35s ease, transform .35s ease !important;
        z-index: 2 !important;
        overflow: hidden !important;
    }

    /* === ICONS inside thumb === */
    header.pc-header .theme-toggle-label .tt-thumb i {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) scale(1) !important;
        font-size: .72rem !important;
        line-height: 1 !important;
        margin: 0 !important;
        padding: 0 !important;
        transition: opacity .25s ease, transform .25s ease !important;
    }

    /* Sun — visible in light mode */
    header.pc-header .theme-toggle-label .tt-icon-sun {
        color: #f59e0b !important;
        opacity: 1 !important;
        transform: translate(-50%, -50%) scale(1) rotate(0deg) !important;
    }

    /* Moon — hidden in light mode */
    header.pc-header .theme-toggle-label .tt-icon-moon {
        color: #a5b4fc !important;
        opacity: 0 !important;
        transform: translate(-50%, -50%) scale(.6) rotate(-30deg) !important;
    }

    /* ── DARK STATE (checkbox checked) ── */
    header.pc-header .theme-toggle-label input[type="checkbox"]:checked ~ .tt-track {
        background: #1e2d45 !important;
        border-color: #334155 !important;
        box-shadow: inset 0 1px 4px rgba(0,0,0,.4), 0 1px 4px rgba(0,0,0,.3) !important;
    }

    header.pc-header .theme-toggle-label input[type="checkbox"]:checked ~ .tt-thumb {
        left: 28px !important;
        transform: translateY(-50%) !important;
        background: #263550 !important;
        box-shadow: 0 1px 5px rgba(0,0,0,.45), 0 0 8px rgba(129,140,248,.35) !important;
    }

    /* Sun hides, Moon shows */
    header.pc-header .theme-toggle-label input[type="checkbox"]:checked ~ .tt-thumb .tt-icon-sun {
        opacity: 0 !important;
        transform: translate(-50%, -50%) scale(.6) rotate(30deg) !important;
    }
    header.pc-header .theme-toggle-label input[type="checkbox"]:checked ~ .tt-thumb .tt-icon-moon {
        opacity: 1 !important;
        color: #a5b4fc !important;
        transform: translate(-50%, -50%) scale(1) rotate(0deg) !important;
    }

    /* ── DATA-THEME FALLBACK (untuk sinkron dengan JS toggle) ── */
    [data-theme="dark"] header.pc-header .theme-toggle-label .tt-track {
        background: #1e2d45 !important;
        border-color: #334155 !important;
        box-shadow: inset 0 1px 4px rgba(0,0,0,.4), 0 1px 4px rgba(0,0,0,.3) !important;
    }
    [data-theme="dark"] header.pc-header .theme-toggle-label .tt-thumb {
        left: 28px !important;
        transform: translateY(-50%) !important;
        background: #263550 !important;
        box-shadow: 0 1px 5px rgba(0,0,0,.45), 0 0 8px rgba(129,140,248,.35) !important;
    }
    [data-theme="dark"] header.pc-header .theme-toggle-label .tt-icon-sun {
        opacity: 0 !important;
        transform: translate(-50%, -50%) scale(.6) rotate(30deg) !important;
    }
    [data-theme="dark"] header.pc-header .theme-toggle-label .tt-icon-moon {
        opacity: 1 !important;
        color: #a5b4fc !important;
        transform: translate(-50%, -50%) scale(1) rotate(0deg) !important;
    }

    /* ── HOVER glow on track ── */
    header.pc-header .theme-toggle-label:hover .tt-track {
        border-color: #818cf8 !important;
        box-shadow: inset 0 1px 3px rgba(0,0,0,.08), 0 0 0 3px rgba(129,140,248,.18) !important;
    }
    [data-theme="dark"] header.pc-header .theme-toggle-label:hover .tt-track {
        border-color: #6366f1 !important;
        box-shadow: inset 0 1px 4px rgba(0,0,0,.4), 0 0 0 3px rgba(99,102,241,.25) !important;
    }

    /* ── FOCUS ring for accessibility ── */
    header.pc-header .theme-toggle-label input[type="checkbox"]:focus-visible ~ .tt-track {
        outline: 2px solid #6366f1 !important;
        outline-offset: 2px !important;
    }
</style>

<header class="pc-header shadow-sm">
    <div class="d-flex align-items-center gap-2">
        <button id="sidebar-hide" type="button" aria-label="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
        <a href="{{ url('/') }}" class="brand-text text-decoration-none">
            📂 E-<span>Arsip</span>
        </a>
    </div>

    <ul class="list-unstyled d-flex align-items-center mb-0 gap-3">
        <li class="d-flex align-items-center">
            <label class="theme-toggle-label" for="theme-toggle-cb" title="Ganti tema" aria-label="Toggle dark mode">
                <input type="checkbox" id="theme-toggle-cb">
                <span class="tt-track"></span>
                <span class="tt-thumb">
                    <i class="bi bi-sun-fill   tt-icon-sun"></i>
                    <i class="bi bi-moon-stars-fill tt-icon-moon"></i>
                </span>
            </label>
        </li>
        @auth
            <li class="d-flex align-items-center gap-2">
                @php
                    $authUser = auth()->user();
                    $fotoUrl  = $authUser->foto
                        ? asset('assets/foto_admin/' . $authUser->foto)
                        : asset('assets/img/default_staf.png');
                @endphp
                <img src="{{ $fotoUrl }}"
                     onerror="this.src='{{ asset('assets/img/default_staf.png') }}'"
                     alt="Foto {{ $authUser->name }}"
                     style="width:34px;height:34px;object-fit:cover;object-position:center;
                            border-radius:50%;border:2px solid rgba(99,102,241,.45);
                            box-shadow:0 1px 4px rgba(0,0,0,.25);flex-shrink:0;">
                <span class="fw-semibold text-nowrap d-none d-sm-inline"
                      style="font-size:.875rem;">
                    Hi, {{ $authUser->name }}
                </span>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}" onsubmit="try{sessionStorage.removeItem('eac_user');sessionStorage.removeItem('eac_history');sessionStorage.removeItem('eac_messages_html');sessionStorage.removeItem('eac_panel_open');}catch(_){}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Logout</span>
                    </button>
                </form>
            </li>
        @endauth
    </ul>
</header>




