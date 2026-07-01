<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Login | E-Arsip SMA Babussalam')</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('template/dist/images/logo.png') }}" type="image/x-icon">

    {{-- Bootstrap + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Global CSS --}}
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, #f8fafc, #e0f2fe);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #f8f9fa;
        }

        /* === Global Alert === */
        .global-alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            max-width: 400px;
            width: 90%;
        }
        .global-alert .alert {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.4s ease;
        }
        .global-alert .alert.show {
            opacity: 1;
            transform: translateY(0);
        }

        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            border: none;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            cursor: pointer;
            color: #555;
            background: #e9ecef;
            transition: all 0.3s ease;
        }
        [data-theme="dark"] .theme-toggle {
            background: #2b3348;
            color: #f8f9fa;
        }
        .theme-toggle:hover {
            background: #0d6efd;
            color: #fff;
        }
    </style>

    @stack('styles')
</head>
<body>
    {{-- Tombol tema --}}
    <button class="theme-toggle" type="button"><i class="bi bi-moon-stars-fill"></i></button>

    {{-- Global Alert --}}
    @if(session('success') || session('error'))
    <div class="global-alert">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center mb-2" role="alert">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-2" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            </div>
        @endif
    </div>
    @endif

    {{-- Konten halaman --}}
    @yield('content')

    {{-- Script --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // === Dark Mode Toggle ===
        const toggleBtn = document.querySelector('.theme-toggle');
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.dataset.theme = savedTheme;
        toggleBtn.innerHTML = savedTheme === 'dark'
            ? '<i class="bi bi-sun-fill"></i>'
            : '<i class="bi bi-moon-stars-fill"></i>';

        toggleBtn.addEventListener('click', () => {
            html.dataset.theme = html.dataset.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', html.dataset.theme);
            toggleBtn.innerHTML = html.dataset.theme === 'dark'
                ? '<i class="bi bi-sun-fill"></i>'
                : '<i class="bi bi-moon-stars-fill"></i>';
        });

        // === Auto Hide Alert ===
        const alertBox = document.querySelector('.global-alert .alert');
        if (alertBox) {
            setTimeout(() => alertBox.classList.add('show'), 100);
            setTimeout(() => {
                alertBox.classList.remove('show');
                setTimeout(() => alertBox.remove(), 500);
            }, 4000);
        }
    </script>
</body>
</html>





