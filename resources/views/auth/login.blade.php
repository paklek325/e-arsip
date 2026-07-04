<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Login </title>

    <link rel="icon" href="{{ asset('template/dist/images/logo.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('template/dist/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('template/dist/assets/css/style-preset.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* =========================================
           BASE
        ========================================= */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background-color .3s ease, color .3s ease;
            background: url("{{ asset('assets/img/login.jpg') }}") no-repeat center center / cover;
            position: relative;
            overflow: hidden;
        }

        /* Overlay latar — sekarang dengan gradient lembut + noise halus */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 15% 20%, rgba(99, 130, 255, 0.25), transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(124, 58, 237, 0.22), transparent 45%),
                rgba(240, 245, 255, 0.55);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            transition: background .3s ease;
            z-index: 0;
        }

        [data-theme="dark"] body::before {
            background:
                radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.20), transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(124, 58, 237, 0.25), transparent 45%),
                rgba(10, 14, 30, 0.72);
        }

        /* Bola-bola cahaya melayang di background untuk depth */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
            opacity: .55;
            animation: floatOrb 12s ease-in-out infinite;
            pointer-events: none;
        }
        .bg-orb.orb-1 {
            width: 320px; height: 320px;
            top: -80px; left: -80px;
            background: radial-gradient(circle, #60a5fa, transparent 70%);
            animation-delay: 0s;
        }
        .bg-orb.orb-2 {
            width: 260px; height: 260px;
            bottom: -60px; right: -60px;
            background: radial-gradient(circle, #a78bfa, transparent 70%);
            animation-delay: 2s;
        }
        @keyframes floatOrb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -25px) scale(1.08); }
        }

        /* =========================================
           CARD
        ========================================= */
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            padding: 1rem;
            animation: cardEnter .7s cubic-bezier(.16,1,.3,1) both;
        }

        @keyframes cardEnter {
            from { opacity: 0; transform: translateY(24px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-card {
            border-radius: 24px;
            padding: 2.8rem 2.4rem 2.4rem;
            width: 100%;
            background: rgba(255, 255, 255, 0.97);
            box-shadow:
                0 2px 0 rgba(255,255,255,0.9) inset,
                0 20px 60px rgba(30, 60, 140, 0.14),
                0 4px 16px rgba(0,0,0,.06);
            border: 1px solid rgba(210, 220, 255, 0.6);
            transition: background .3s ease, box-shadow .3s ease, border .3s ease, transform .3s ease;
            position: relative;
            overflow: hidden;
        }

        /* Garis aksen gradient di atas card */
        .login-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #7c3aed, #2563eb);
            background-size: 200% 100%;
            animation: shimmerLine 4s linear infinite;
        }

        @keyframes shimmerLine {
            0% { background-position: 0% 0; }
            100% { background-position: 200% 0; }
        }

        .login-card:hover {
            box-shadow:
                0 2px 0 rgba(255,255,255,0.9) inset,
                0 28px 70px rgba(30, 60, 140, 0.18),
                0 6px 20px rgba(0,0,0,.08);
        }

        [data-theme="dark"] .login-card {
            background: rgba(18, 24, 42, 0.96);
            box-shadow:
                0 1px 0 rgba(255,255,255,0.04) inset,
                0 20px 60px rgba(0,0,0,.5);
            border: 1px solid rgba(255,255,255,0.07);
        }

        [data-theme="dark"] .login-card:hover {
            box-shadow:
                0 1px 0 rgba(255,255,255,0.04) inset,
                0 28px 80px rgba(0,0,0,.6);
        }

        /* =========================================
           LOGO AREA
        ========================================= */
        .logo-area {
            display: flex;
            justify-content: center;
            margin-bottom: 1.4rem;
        }

        .logo-ring {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, #e8eeff, #dce8ff);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(13, 110, 253, .12);
            transition: background .3s ease, transform .3s ease, box-shadow .3s ease;
        }

        .logo-ring:hover {
            transform: rotate(-4deg) scale(1.05);
            box-shadow: 0 8px 22px rgba(13, 110, 253, .22);
        }

        [data-theme="dark"] .logo-ring {
            background: linear-gradient(135deg, #1a2545, #1e2d50);
            box-shadow: 0 4px 14px rgba(0,0,0,.4);
        }

        /* Logo default (mode terang) */
        .logo-light { display: block; }
        .logo-dark  { display: none;  }

        /* Logo ganti di dark mode */
        [data-theme="dark"] .logo-light { display: none;  }
        [data-theme="dark"] .logo-dark  { display: block; }

        .logo-ring img {
            width: 54px;
            height: 54px;
            object-fit: contain;
        }

        /* =========================================
           TEKS HEADER CARD
        ========================================= */
        .welcome-text {
            font-size: .875rem;
            font-weight: 500;
            color: #64748b;
            text-align: center;
            margin-bottom: .3rem;
            transition: color .3s ease;
        }

        [data-theme="dark"] .welcome-text { color: #94a3b8; }

        .app-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            margin-bottom: 1.6rem;
            transition: color .3s ease;
        }

        [data-theme="dark"] .app-name { color: #e2e8f0; }

        /* Judul LOGIN */
        .login-title {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.4px;
            text-align: center;
            margin-bottom: 1.8rem;
            /* mode terang → gradient biru-ungu juga, biar senada */
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: #0f172a;
            transition: color .3s ease;
        }

        [data-theme="dark"] .login-title {
            /* mode gelap → gradient biru-ungu lebih cerah */
            background: linear-gradient(90deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* =========================================
           FORM
        ========================================= */
        .form-group { margin-bottom: 1rem; }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: .4rem;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        [data-theme="dark"] .form-label { color: #94a3b8; }

        .form-control {
            width: 100%;
            padding: .72rem 1rem;
            font-family: inherit;
            font-size: .95rem;
            font-weight: 500;
            color: #0f172a;
            background: #f8faff;
            border: 1.5px solid #dce3f5;
            border-radius: 12px;
            outline: none;
            transition: border .25s ease, box-shadow .25s ease, background .3s ease, color .3s ease, transform .15s ease;
        }

        .form-control::placeholder { color: #a0aec0; opacity: 1; }

        .form-control:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3.5px rgba(59,130,246,.15);
            transform: translateY(-1px);
        }

        [data-theme="dark"] .form-control {
            background: #1a2540;
            border-color: #2d3a5a;
            color: #e2e8f0;
        }

        [data-theme="dark"] .form-control::placeholder { color: #4b5e82; }

        [data-theme="dark"] .form-control:focus {
            border-color: #60a5fa;
            background: #1e2d50;
            box-shadow: 0 0 0 3.5px rgba(96,165,250,.15);
        }

        /* is-invalid */
        .form-control.is-invalid {
            border-color: #ef4444;
            background-image: none;
            animation: shakeField .35s ease;
        }

        @keyframes shakeField {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .invalid-feedback {
            display: block;
            font-size: .78rem;
            color: #ef4444;
            margin-top: .3rem;
            font-weight: 500;
        }

        /* =========================================
           PASSWORD + TOGGLE ICON
        ========================================= */
        .input-group-password { position: relative; }

        .input-group-password .form-control { padding-right: 2.8rem; }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: .85rem;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.05rem;
            color: #94a3b8;
            transition: color .2s ease, transform .2s ease;
            line-height: 1;
            user-select: none;
        }

        .toggle-password:hover { color: #3b82f6; transform: translateY(-50%) scale(1.15); }

        [data-theme="dark"] .toggle-password { color: #4b5e82; }
        [data-theme="dark"] .toggle-password:hover { color: #60a5fa; }

        /* =========================================
           BUTTON LOGIN
        ========================================= */
        .btn-login {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: .82rem 1rem;
            margin-top: 1.6rem;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .2px;
            color: #fff;
            background: linear-gradient(90deg, #2563eb, #4f46e5);
            background-size: 160% 100%;
            background-position: 0% 0;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 18px rgba(37, 99, 235, .35);
            transition: all .3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, .45);
            background-position: 100% 0;
        }

        .btn-login:active { transform: translateY(0); }

        .btn-login i { transition: transform .3s ease; }
        .btn-login:hover i { transform: translateX(3px); }

        [data-theme="dark"] .btn-login {
            background: linear-gradient(90deg, #3b82f6, #7c3aed);
            box-shadow: 0 4px 18px rgba(59, 130, 246, .3);
        }

        /* =========================================
           DIVIDER / TAGLINE BAWAH
        ========================================= */
        .login-footer {
            margin-top: 1.4rem;
            text-align: center;
            font-size: .78rem;
            color: #94a3b8;
            font-weight: 500;
        }

        [data-theme="dark"] .login-footer { color: #4b5e82; }

        /* =========================================
           THEME SWITCH (pojok kanan atas)
        ========================================= */
        #theme-switch {
            position: fixed;
            top: 18px;
            right: 18px;
            width: 58px;
            height: 30px;
            border-radius: 50px;
            border: 1.5px solid rgba(0,0,0,0.1);
            background: #e8edf5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 7px;
            cursor: pointer;
            transition: background .4s ease, border .4s ease, box-shadow .3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
            z-index: 1050;
        }

        #theme-switch:hover {
            box-shadow: 0 4px 14px rgba(0,0,0,.18);
        }

        #theme-switch i { font-size: 1rem; transition: opacity .3s; }
        #theme-switch .sun  { color: #f59e0b; }
        #theme-switch .moon { color: #64748b; opacity: .35; }

        .toggle-thumb {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 22px;
            height: 22px;
            background: #fff;
            border-radius: 50%;
            transition: left .4s cubic-bezier(.4,0,.2,1);
            box-shadow: 0 1px 5px rgba(0,0,0,.2);
        }

        [data-theme="dark"] #theme-switch {
            background: #1e293b;
            border-color: rgba(255,255,255,.08);
        }

        [data-theme="dark"] #theme-switch .sun  { opacity: .3; }
        [data-theme="dark"] #theme-switch .moon { opacity: 1; color: #93c5fd; }

        [data-theme="dark"] .toggle-thumb {
            left: calc(100% - 25px);
            background: #334155;
        }

        /* =========================================
           NOTIFIKASI
        ========================================= */
        .notif-global {
            position: fixed;
            top: 18px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            opacity: 0;
            border-radius: 14px;
            padding: .75rem 1.2rem;
            font-size: .92rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 8px 24px rgba(0,0,0,.2);
            z-index: 1100;
            transition: opacity .5s ease, transform .5s ease;
            white-space: nowrap;
        }

        .notif-global.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .notif-success { background: linear-gradient(90deg,#22c55e,#16a34a); color:#fff; }
        .notif-danger  { background: linear-gradient(90deg,#ef4444,#b91c1c); color:#fff; }
    </style>
</head>

<body>

    <!-- Orb dekoratif -->
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>

    <!-- SWITCH TEMA -->
    <button id="theme-switch" type="button" aria-label="Toggle Theme">
        <i class="bi bi-sun-fill sun"></i>
        <i class="bi bi-moon-fill moon"></i>
        <span class="toggle-thumb"></span>
    </button>

    <!-- NOTIFIKASI -->
    @if (session('success'))
        <div class="notif-global notif-success show">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="notif-global notif-danger show">
            <i class="bi bi-x-circle-fill"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- CARD LOGIN -->
    <div class="login-wrapper">
        <div class="login-card">

            <!-- Logo: ganti otomatis saat dark mode -->
            <div class="logo-area">
                <div class="logo-ring">
                    {{-- Logo terang (default) --}}
                    <img src="{{ asset('template/dist/images/logo.png') }}"
                         class="logo-light"
                         alt="Logo E-Arsip">
                    {{-- Logo khusus dark mode — ganti path jika ada versi putih/cerah --}}
                    <img src="{{ asset('template/dist/images/logo-dark.png') }}"
                         class="logo-dark"
                         alt="Logo E-Arsip Dark"
                         onerror="this.src='{{ asset('template/dist/images/logo.png') }}'">
                </div>
            </div>

            <p class="welcome-text">Selamat Datang </p>
            <p class="app-name">SISTEM ARSIP DIGITAL
                <br> SMA BABUSSALAM </P> 

            <h4 class="login-title">LOGIN</h4>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- EMAIL -->
                <div class="form-group">
                    <label class="form-label" for="email-input">Email</label>
                    <input
                        id="email-input"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required autofocus
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="Email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                    <label class="form-label" for="password-input">Password</label>
                    <div class="input-group-password">
                        <input
                            id="password-input"
                            type="password"
                            name="password"
                            required
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="••••••••">
                        <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk
                </button>
            </form>

            <p class="login-footer">© {{ date('Y') }} SMA Babussalam &mdash; Sistem Arsip Digital</p>

        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const html      = document.documentElement;
        const switchBtn = document.getElementById('theme-switch');
        const THEME_KEY = 'earsip-theme';

        // Muat tema tersimpan
        const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
        if (savedTheme === 'dark') {
            html.setAttribute('data-theme', 'dark');
        }

        // Toggle tema
        switchBtn.addEventListener('click', () => {
            const isDark = html.getAttribute('data-theme') === 'dark';
            if (isDark) {
                html.removeAttribute('data-theme');
                localStorage.setItem(THEME_KEY, 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem(THEME_KEY, 'dark');
            }
        });

        // Auto-hide notifikasi setelah 4 detik
        document.querySelectorAll('.notif-global').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(-50%) translateY(-20px)';
                setTimeout(() => alert.remove(), 600);
            }, 4000);
        });

        // Toggle show/hide password
        const passwordInput  = document.getElementById('password-input');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', () => {
            const isHidden = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
            togglePassword.classList.toggle('bi-eye');
            togglePassword.classList.toggle('bi-eye-slash');
        });
    });
    </script>

    <script>
        // Bersihkan status chat widget agar panel tidak auto-terbuka setelah login
        try {
            sessionStorage.removeItem('eac_panel_open');
            sessionStorage.removeItem('eac_user');
            sessionStorage.removeItem('eac_history');
            sessionStorage.removeItem('eac_messages_html');
        } catch (_) {}
    </script>

</body>
</html>




