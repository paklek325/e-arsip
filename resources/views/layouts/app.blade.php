<!doctype html>
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="base-url" content="{{ request()->getSchemeAndHttpHost() }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- Cegah browser cache halaman — penting agar chat widget selalu fresh --}}
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>@yield('title', 'E-Arsip')</title>

  <script>
    (function () {
      var THEME_KEY = "earsip-theme";
      var saved = localStorage.getItem(THEME_KEY) || "light";

      // Matikan transisi CSS sesaat (lihat aturan [data-theme-changing]
      // di layout.css) supaya penerapan tema awal tidak "flick".
      document.documentElement.setAttribute("data-theme-changing", "");
      document.documentElement.setAttribute("data-theme", saved);
    })();
  </script>

  {{-- Favicon --}}
  <link rel="icon" href="{{ asset('template/dist/images/logo.png') }}" type="image/x-icon">

  {{-- Fonts & Icons --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('template/dist/fonts/tabler-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('template/dist/fonts/phosphor/regular/style.css') }}">
  <link rel="stylesheet" href="{{ asset('template/dist/fonts/fontawesome.css') }}">
  <link rel="stylesheet" href="{{ asset('template/dist/fonts/bi/font/bootstrap-icons.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- Template CSS --}}
  <link rel="stylesheet" href="{{ asset('template/dist/css/plugins/jsvectormap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('template/dist/css/style.min.css') }}" id="main-style-link">
  <link rel="stylesheet" href="{{ asset('template/dist/css/style-preset.css') }}">

  {{-- Flatpickr --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  {{-- Swiper --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

  {{-- App CSS --}}
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="/css/layout.css">
  <link rel="stylesheet" href="/css/kode.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/user.css">
  <link rel="stylesheet" href="/css/surat.css?v={{ filemtime(public_path('css/surat.css')) }}">
  <link rel="stylesheet" href="/css/peserta-didik.css">
  <link rel="stylesheet" href="/css/laporan.css">
  <link rel="stylesheet" href="/css/chat.css?v={{ filemtime(public_path('css/chat.css')) }}">{!! '' !!}

  {{-- Aset dari Vite --}}
  @vite([
    'resources/js/app.js',
    'resources/js/tampilan.js',
    'resources/js/surat.js',
    'resources/js/kode.js',
    'resources/js/user.js',
    'resources/js/dashboard.js',
    'resources/js/peserta-didik.js',
    'resources/js/laporan.js',
    'resources/js/responsif.js',
    'resources/js/chat.js',
  ])

  @stack('styles')

  <style>
    .pc-sidebar { visibility: visible; }
    html { transition: background-color 0.3s, color 0.3s; }

    /* ===== BREADCRUMB ===== */
    .pc-breadcrumb-bar {
      padding: 0 0 0.5rem;
    }
    .pc-breadcrumb-bar .breadcrumb {
      font-size: 0.8rem;
      margin-bottom: 0;
      background: transparent;
      padding: 0;
    }
    .pc-breadcrumb-bar .breadcrumb-item a {
      text-decoration: none;
      color: var(--pc-sidebar-color, #3b82f6);
    }
    .pc-breadcrumb-bar .breadcrumb-item a:hover {
      text-decoration: underline;
    }
    .pc-breadcrumb-bar .breadcrumb-item.active {
      color: var(--bs-body-color);
      font-weight: 500;
    }
    .pc-breadcrumb-bar .breadcrumb-item + .breadcrumb-item::before {
      color: var(--bs-secondary-color, #8a8a8a);
    }
  </style>
</head>
<body>

  {{-- ===== SIDEBAR ===== --}}
  @include('partials.sidebar')

  {{-- ===== NAVBAR ===== --}}
  @include('partials.navbar')

  {{-- ===== DARK MODE TOGGLE SCRIPT ===== --}}
  <script>
    (function () {
      var THEME_KEY = 'earsip-theme';

      function applyTheme(dark) {
        var t = dark ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', t);
        document.body.setAttribute('data-theme', t);
        localStorage.setItem(THEME_KEY, t);
        var inp = document.getElementById('theme-toggle-cb');
        if (inp) inp.checked = dark;
      }

      function init() {
        var saved = localStorage.getItem(THEME_KEY) || 'light';
        applyTheme(saved === 'dark');

        var cb = document.getElementById('theme-toggle-cb');
        if (cb) {
          cb.addEventListener('change', function () {
            applyTheme(this.checked);
          });
        }
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
    })();
  </script>

  {{-- ===== KONTEN UTAMA ===== --}}
  <div class="pc-container">
    <div class="pc-content">
      @hasSection('breadcrumbs')
        <nav aria-label="breadcrumb" class="pc-breadcrumb-bar">
          @yield('breadcrumbs')
        </nav>
      @endif
      @yield('content')
    </div>
  </div>

  {{-- ===== ALERT TOAST ===== --}}
  <div id="alert-container" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;"></div>

  {{-- ===== SCRIPTS ===== --}}
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="{{ asset('template/dist/js/plugins/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('template/dist/js/plugins/popper.min.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Bootstrap Tooltips
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
      const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el))
    });
  </script>

  @stack('scripts')

  {{-- ===== AI CHAT WIDGET ===== --}}
  @include('partials.chat-widget')

</body>
</html>




