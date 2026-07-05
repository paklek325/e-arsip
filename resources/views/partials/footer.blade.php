{{-- ===========================
     FOOTER SIDEBAR
=========================== --}}
<div class="sidebar-footer">
    {{-- Brand row: logo kecil + nama --}}
    <div class="sf-brand">
        <img src="{{ asset('template/dist/images/logo-1.png') }}"
             alt="Logo"
             class="sf-logo">
        <div class="sf-brand-text">
            <div class="sf-brand-name">E-Arsip</div>
            <div class="sf-brand-subtitle">SMA Babussalam</div>
        </div>
    </div>

    {{-- Developer --}}
    <div class="sf-dev">
        Developed by <strong>SulsDev</strong>
    </div>

    {{-- Kontak --}}
    <div class="sf-contacts">
        <a href="mailto:sulsdev86@gmail.com" class="sf-contact-link">
            <i class="bi bi-envelope sf-contact-icon"></i>
            <span class="sf-contact-text">sulsdev86@gmail.com</span>
        </a>
        <a href="https://wa.me/6285184640256"
           target="_blank"
           class="sf-contact-link whatsapp">
            <i class="bi bi-whatsapp sf-contact-icon"></i>
            <span>0851-8464-0256</span>
        </a>
    </div>

    {{-- Divider --}}
    <div class="sf-divider"></div>

    {{-- Copyright --}}
    <div class="sf-copyright">
        &copy; {{ date('Y') }} All rights reserved
    </div>
</div>
</nav>
