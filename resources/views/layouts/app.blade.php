<!doctype html>
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="base-url" content="{{ rtrim(url('/'), '/') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'E-Arsip')</title>

  {{-- Terapkan tema + status sidebar dari localStorage SEBELUM CSS dimuat,
       agar tidak ada flash tema maupun "kedipan" posisi sidebar/logo saat
       pindah menu (setiap klik menu = full page load baru di MPA ini,
       jadi state ini harus disiapkan sebelum elemen sempat ke-render). --}}
  <script>
    (function () {
      var html = document.documentElement;
      html.setAttribute('data-ui-changing', '');

      var t = localStorage.getItem('earsip-theme') || 'light';
      html.setAttribute('data-theme-changing', '');
      html.setAttribute('data-theme', t);

      if (localStorage.getItem('earsip-sidebar') === 'collapsed') {
        html.classList.add('pc-sidebar-collapsed');
      }
    })();
  </script>

  {{-- Favicon --}}
  <link rel="icon" href="{{ asset('template/dist/images/logo.png') }}" type="image/x-icon">

  {{-- Preload critical above-fold images --}}
  <link rel="preload" as="image" href="{{ asset('template/dist/images/logo-1.png') }}" fetchpriority="high">
  @auth
  @php
    $__preloadFoto = auth()->user()->foto
      ? asset('storage/foto_admin/' . auth()->user()->foto)
      : asset('assets/img/default_staf.png');
  @endphp
  <link rel="preload" as="image" href="{{ $__preloadFoto }}" fetchpriority="high">
  @endauth

  {{-- Fonts & Icons --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
  <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
  {{-- Google Fonts: non-blocking (preload swap trick) --}}
  <link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap"></noscript>
  <link rel="stylesheet" href="{{ asset('template/dist/fonts/tabler-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('template/dist/fonts/phosphor/regular/style.css') }}">
  <link rel="stylesheet" href="{{ asset('template/dist/fonts/fontawesome.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- Template CSS --}}
  <link rel="stylesheet" href="{{ asset('template/dist/css/plugins/jsvectormap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('template/dist/css/style.min.css') }}" id="main-style-link">
  <link rel="stylesheet" href="{{ asset('template/dist/css/style-preset.css') }}">

  {{-- Flatpickr --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  {{-- App CSS global (semua halaman) --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1' }}">
  <link rel="stylesheet" href="{{ asset('css/layout.css') }}?v={{ file_exists(public_path('css/layout.css')) ? filemtime(public_path('css/layout.css')) : '1' }}">
  <link rel="stylesheet" href="{{ asset('css/chat.css') }}?v={{ file_exists(public_path('css/chat.css')) ? filemtime(public_path('css/chat.css')) : '1' }}">

  {{-- CSS khusus per-halaman (di-push dari masing-masing view) --}}
  @stack('page-css')

  {{-- Aset Vite global (semua halaman) --}}
  @vite([
    'resources/js/app.js',
    'resources/js/tampilan.js',
    'resources/js/chat.js',
  ])

  {{-- JS Vite khusus per-halaman --}}
  @stack('page-js')

  @stack('styles')

</head>
<body>

  {{-- ===== SIDEBAR ===== --}}
  @include('partials.sidebar')

  {{-- ===== NAVBAR ===== --}}
  @include('partials.navbar')

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
  <div id="alert-container" class="position-fixed bottom-0 end-0 p-3"></div>

  {{-- ===== SCRIPTS ===== --}}
  <script src="{{ asset('template/dist/js/plugins/bootstrap.bundle.min.js') }}"></script>

  @stack('scripts')

  {{-- Flash toast (dari session Laravel) --}}
  @if(session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.AppToast?.(@json(session('success')), 'success');
    });
  </script>
  @endif
  @if(session('error'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.AppToast?.(@json(session('error')), 'error');
    });
  </script>
  @endif

  {{-- ===== AI CHAT WIDGET ===== --}}
  @include('partials.chat-widget')

  {{-- ===== MODALS STACK ===== --}}
  @stack('modals')

</body>
</html>