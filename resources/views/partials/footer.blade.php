{{-- ===========================
     FOOTER SIDEBAR
=========================== --}}
<div class="sidebar-footer" style="
    padding: 0.75rem 1rem;
    border-top: 1px solid rgba(255,255,255,0.06);
    margin-top: auto;
">
    {{-- Brand row: logo kecil + nama --}}
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
        <img src="{{ asset('template/dist/images/logo-1.png') }}"
             alt="Logo"
             style="width: 28px; height: 28px; object-fit: contain; border-radius: 6px; flex-shrink: 0;">
        <div style="min-width: 0;">
            <div style="font-size: 0.8rem; font-weight: 600; color: #e2e8f0; line-height: 1.2; white-space: nowrap;">E-Arsip</div>
            <div style="font-size: 0.7rem; color: #7a8499; white-space: nowrap;">SMA Babussalam</div>
        </div>
    </div>

    {{-- Developer --}}
    <div style="font-size: 0.72rem; color: #7a8499; margin-bottom: 6px;">
        Developed by <strong style="color: #94a3b8;">SulsDev</strong>
    </div>

    {{-- Kontak --}}
    <div style="display: flex; flex-direction: column; gap: 3px; margin-bottom: 8px;">
        <a href="mailto:sulsdev86@gmail.com"
           style="display: flex; align-items: center; gap: 6px; font-size: 0.71rem; color: #7a8499; text-decoration: none; transition: color 0.2s;"
           onmouseover="this.style.color='#94a3b8'" onmouseout="this.style.color='#7a8499'">
            <i class="bi bi-envelope" style="font-size: 0.75rem; flex-shrink: 0;"></i>
            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">sulsdev86@gmail.com</span>
        </a>
        <a href="https://wa.me/6285184640256"
           target="_blank"
           style="display: flex; align-items: center; gap: 6px; font-size: 0.71rem; color: #7a8499; text-decoration: none; transition: color 0.2s;"
           onmouseover="this.style.color='#25d366'" onmouseout="this.style.color='#7a8499'">
            <i class="bi bi-whatsapp" style="font-size: 0.75rem; flex-shrink: 0;"></i>
            <span style="white-space: nowrap;">0851-8464-0256</span>
        </a>
    </div>

    {{-- Divider --}}
    <div style="height: 1px; background: rgba(255,255,255,0.05); margin-bottom: 6px;"></div>

    {{-- Copyright --}}
    <div style="font-size: 0.68rem; color: #4b5563; text-align: center;">
        © {{ date('Y') }} All rights reserved
    </div>
</div>
</nav>




