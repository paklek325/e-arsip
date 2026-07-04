<header class="pc-header">
    <div class="header-wrapper">

        {{-- ── Kiri: Toggle Sidebar ─────────────────────────────────────── --}}
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">

                {{-- Desktop: klik kiri untuk collapse/expand sidebar --}}
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide" aria-label="Toggle Sidebar">
                        <i class="bi bi-list fs-5"></i>
                    </a>
                </li>

                {{-- Mobile: klik untuk slide-in sidebar --}}
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse" aria-label="Buka Menu">
                        <i class="bi bi-list fs-5"></i>
                    </a>
                </li>

            </ul>
        </div>

        {{-- ── Kanan: Aksi header ───────────────────────────────────────── --}}
        <div class="ms-auto">
            <ul class="list-unstyled">

                {{-- Dark / Light Mode Toggle --}}
                <li class="pc-h-item px-2">
                    <button id="theme-switch"
                            type="button"
                            aria-label="Ganti tema"
                            title="Ganti tema">
                        <i class="bi bi-sun-fill sun"></i>
                        <i class="bi bi-moon-fill moon"></i>
                        <span class="toggle-thumb"></span>
                    </button>
                </li>

                @auth
                @php
                    $authUser = auth()->user();
                    $fotoUrl  = $authUser->foto
                        ? asset('storage/foto_admin/' . $authUser->foto)
                        : asset('assets/img/default_staf.png');
                    $roleName = ucwords($authUser->role->name ?? 'User');
                @endphp

                {{-- User Profile Dropdown --}}
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0"
                       data-bs-toggle="dropdown"
                       href="#"
                       role="button"
                       aria-haspopup="false"
                       data-bs-auto-close="outside"
                       aria-expanded="false">
                        <img src="{{ $fotoUrl }}"
                             onerror="this.src='{{ asset('assets/img/default_staf.png') }}'"
                             alt="Foto {{ $authUser->name }}"
                             class="user-avatar">
                    </a>

                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown p-0 overflow-hidden">

                        {{-- Header dropdown: foto + nama + role + tombol logout --}}
                        <div class="dropdown-header d-flex align-items-center gap-3"
                             style="background: linear-gradient(135deg, #6C3EAB, #4F46E5); padding: 14px 16px;">
                            <img src="{{ $fotoUrl }}"
                                 onerror="this.src='{{ asset('assets/img/default_staf.png') }}'"
                                 alt="{{ $authUser->name }}"
                                 class="img-radius wid-35"
                                 style="height:35px; object-fit:cover; object-position:center; flex-shrink:0;">
                            <div class="flex-grow-1 overflow-hidden">
                                <h6 class="text-white mb-0 text-truncate">{{ $authUser->name }}</h6>
                                <small class="text-white text-opacity-75">{{ $roleName }}</small>
                            </div>
                            {{-- Tombol Logout di sebelah foto --}}
                            <form method="POST"
                                  action="{{ route('logout') }}"
                                  class="flex-shrink-0"
                                  onsubmit="try{
                                      ['eac_user','eac_history','eac_messages_html','eac_panel_open']
                                      .forEach(k => sessionStorage.removeItem(k));
                                  }catch(_){}">
                                @csrf
                                <button type="submit"
                                        class="btn-logout-header"
                                        title="Keluar">
                                    <i class="bi bi-power"></i>
                                </button>
                            </form>
                        </div>

                        {{-- Body: menu --}}
                        <div class="dropdown-body">

                            <a href="{{ route('profil') }}"
                               class="dropdown-item">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-gear fs-6 text-primary"></i>
                                    <span>Profil Saya</span>
                                </span>
                                <i class="bi bi-chevron-right text-muted small"></i>
                            </a>

                        </div>
                    </div>
                </li>
                @endauth

            </ul>
        </div>

    </div>
</header>
