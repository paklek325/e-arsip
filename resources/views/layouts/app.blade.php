<!doctype html>
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="base-url" content="{{ request()->getSchemeAndHttpHost() }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'E-Arsip')</title>

  {{-- Terapkan tema dari localStorage sebelum CSS dimuat agar tidak ada flash --}}
  <script>
    (function () {
      var t = localStorage.getItem('earsip-theme') || 'light';
      document.documentElement.setAttribute('data-theme-changing', '');
      document.documentElement.setAttribute('data-theme', t);
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

  {{-- Template CSS --}}
  <link rel="stylesheet" href="{{ asset('template/dist/css/plugins/jsvectormap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('template/dist/css/style.min.css') }}" id="main-style-link">
  <link rel="stylesheet" href="{{ asset('template/dist/css/style-preset.css') }}">

  {{-- Flatpickr --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  {{-- App CSS --}}
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="/css/layout.css">
  <link rel="stylesheet" href="/css/kode.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/user.css">
  <link rel="stylesheet" href="/css/surat.css">
  <link rel="stylesheet" href="/css/peserta-didik.css">
  <link rel="stylesheet" href="/css/laporan.css">
  <link rel="stylesheet" href="/css/chat.css">

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
    'resources/js/chat.js',
  ])

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
      window.AppToast?.('{{ addslashes(session('success')) }}', 'success');
    });
  </script>
  @endif
  @if(session('error'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.AppToast?.('{{ addslashes(session('error')) }}', 'error');
    });
  </script>
  @endif

  {{-- ===== AI CHAT WIDGET ===== --}}
  @include('partials.chat-widget')

</body>
</html>
