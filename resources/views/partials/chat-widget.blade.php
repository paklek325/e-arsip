{{-- ============================================================
     ASISTEN E-ARSIP — Chat Widget v2
     Letakkan di: resources/views/components/chat-widget.blade.php
     Panggil di layout: @include('components.chat-widget')
     Pastikan <meta name="csrf-token" content="{{ csrf_token() }}"> ada di <head>
     ============================================================ --}}

{{-- ══ Style ══ --}}
<style>
    /* ══════════════════════════════════════════════════════════
       ARSY CHAT WIDGET — DESIGN SYSTEM
       Semua warna lewat CSS variable supaya otomatis mengikuti
       tema gelap/terang (data-theme di <html>, sinkron dgn
       tampilan.js & tombol tema di header widget ini).
       ══════════════════════════════════════════════════════════ */
    :root{
        --eac-primary:#6C3EAB;
        --eac-primary-dark:#4F46E5;
        --eac-primary-light:#8b5fd6;
        --eac-bg:#ffffff;
        --eac-bg-soft:#f7f5fc;
        --eac-border:#e7e1f5;
        --eac-text:#2c2440;
        --eac-text-muted:#7a7290;
        --eac-bubble-bot:#f2effa;
        --eac-bubble-user-a:#6C3EAB;
        --eac-bubble-user-b:#4F46E5;
        --eac-success:#2d7a4f;
        --eac-warning:#856404;
        --eac-danger:#c0392b;
        --eac-shadow:0 20px 55px -12px rgba(76,40,140,.35), 0 4px 18px rgba(76,40,140,.12);
        --eac-radius:18px;
    }
    [data-theme="dark"]{
        --eac-bg:#1b1730;
        --eac-bg-soft:#241f3d;
        --eac-border:#352c56;
        --eac-text:#eee9fb;
        --eac-text-muted:#a89ecb;
        --eac-bubble-bot:#2a2447;
        --eac-shadow:0 20px 55px -12px rgba(0,0,0,.55), 0 4px 18px rgba(0,0,0,.3);
    }

    #earsipchat-btn, #earsipchat-panel, #earsipchat-panel *{ box-sizing:border-box; }

    /* ── Tombol mengambang ── */
    #earsipchat-btn{
        position:fixed; right:22px; bottom:22px; z-index:2147483000;
        width:58px; height:58px; border:none; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        background:linear-gradient(135deg,var(--eac-primary),var(--eac-primary-dark));
        color:#fff; cursor:pointer;
        box-shadow:0 12px 28px -8px rgba(76,40,140,.55), 0 3px 10px rgba(76,40,140,.3);
        transition:transform .2s ease, box-shadow .2s ease;
        animation:eac-pop .35s ease;
    }
    #earsipchat-btn:hover{ transform:translateY(-3px) scale(1.04); box-shadow:0 16px 34px -8px rgba(76,40,140,.6); }
    #earsipchat-btn svg{ width:26px; height:26px; }
    @keyframes eac-pop{ from{ transform:scale(.4); opacity:0; } to{ transform:scale(1); opacity:1; } }

    /* ── Panel ── */
    #earsipchat-panel{
        position:fixed; right:22px; bottom:22px; z-index:2147483000;
        width:400px; max-width:calc(100vw - 24px);
        height:auto; max-height:min(600px, calc(100vh - 80px));
        background:var(--eac-bg); color:var(--eac-text);
        border-radius:var(--eac-radius); box-shadow:var(--eac-shadow);
        display:flex; flex-direction:column; overflow:hidden;
        opacity:0; pointer-events:none; transform:translateY(16px) scale(.97);
        transition:opacity .22s ease, transform .22s ease;
        font-family:'Segoe UI',Roboto,Inter,Arial,sans-serif; font-size:12px;
    }
    #earsipchat-panel.open{ opacity:1; pointer-events:auto; transform:translateY(0) scale(1); }

    /* ── Header ── */
    .eac-header{
        display:flex; align-items:center; justify-content:space-between;
        padding:11px 14px; flex-shrink:0;
        background:linear-gradient(120deg,var(--eac-primary),var(--eac-primary-dark));
        color:#fff;
    }
    .eac-header-left{ display:flex; align-items:center; gap:9px; min-width:0; }
    .eac-avatar{
        width:33px; height:33px; border-radius:50%; flex-shrink:0;
        background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center;
        font-size:16px;
    }
    .eac-header-info h4{ margin:0; font-size:13px; font-weight:700; line-height:1.2; }
    .eac-header-info span{ font-size:10px; opacity:.85; display:flex; align-items:center; gap:4px; }
    .eac-header-info span::before{ content:''; width:6px; height:6px; border-radius:50%; background:#4ade80; display:inline-block; }
    .eac-header-actions{ display:flex; align-items:center; gap:4px; flex-shrink:0; }
    .eac-header-actions button{
        width:27px; height:27px; border:none; border-radius:8px; cursor:pointer;
        background:rgba(255,255,255,.14); color:#fff; font-size:12.5px;
        display:flex; align-items:center; justify-content:center;
        transition:background .15s ease, transform .15s ease;
    }
    .eac-header-actions button:hover{ background:rgba(255,255,255,.28); transform:translateY(-1px); }
    #eac-theme-btn{ position:relative; }
    #eac-theme-btn svg{ width:14px; height:14px; position:absolute; transition:opacity .2s ease, transform .3s ease; }
    #eac-theme-btn .eac-theme-icon-sun{ opacity:1; transform:rotate(0deg); }
    #eac-theme-btn .eac-theme-icon-moon{ opacity:0; transform:rotate(-60deg); }
    #eac-theme-btn.is-dark .eac-theme-icon-sun{ opacity:0; transform:rotate(60deg); }
    #eac-theme-btn.is-dark .eac-theme-icon-moon{ opacity:1; transform:rotate(0deg); }

    /* ── Tabs ── */
    .eac-tabs{ display:flex; flex-shrink:0; background:var(--eac-bg-soft); border-bottom:1px solid var(--eac-border); }
    .eac-tab{
        flex:1; border:none; background:transparent; cursor:pointer;
        padding:8px 6px; font-size:11.5px; font-weight:600; color:var(--eac-text-muted);
        border-bottom:2px solid transparent; transition:color .15s ease, border-color .15s ease;
    }
    .eac-tab:hover{ color:var(--eac-primary); }
    .eac-tab.active{ color:var(--eac-primary); border-bottom-color:var(--eac-primary); }

    /* ── Messages ── */
    .eac-messages{ flex:1 !important; min-height:0 !important; height:auto !important; overflow-y:auto; padding:12px 10px; display:flex; flex-direction:column; gap:8px; background:var(--eac-bg); }
    .eac-messages::-webkit-scrollbar{ width:6px; }
    .eac-messages::-webkit-scrollbar-thumb{ background:var(--eac-border); border-radius:6px; }

    .eac-bubble{ display:flex; gap:7px; max-width:100%; animation:eac-rise .18s ease; }
    @keyframes eac-rise{ from{ opacity:0; transform:translateY(6px);} to{ opacity:1; transform:translateY(0);} }
    .eac-bubble-avatar{
        width:23px; height:23px; border-radius:50%; flex-shrink:0; font-size:11.5px;
        display:flex; align-items:center; justify-content:center; background:var(--eac-bubble-bot);
    }
    .eac-bubble-text{ padding:8px 11px; border-radius:13px; line-height:1.45; word-break:break-word; }
    .eac-bubble.bot{ align-self:flex-start; }
    .eac-bubble.bot .eac-bubble-text{ background:var(--eac-bubble-bot); color:var(--eac-text); border-bottom-left-radius:4px; }
    .eac-bubble.user{ align-self:flex-end; flex-direction:row-reverse; }
    .eac-bubble.user .eac-bubble-text{
        background:linear-gradient(135deg,var(--eac-bubble-user-a),var(--eac-bubble-user-b));
        color:#fff; border-bottom-right-radius:4px;
    }
    .eac-bubble.user .eac-bubble-avatar{ background:var(--eac-primary); color:#fff; }

    .eac-error{
        align-self:center; background:#fde8e6; color:var(--eac-danger); border:1px solid #f6c6c1;
        padding:7px 12px; border-radius:10px; font-size:12px; max-width:90%;
    }
    [data-theme="dark"] .eac-error{ background:#3a1f1f; border-color:#5c2d2d; color:#f4a19a; }

    .eac-typing{ display:flex; gap:4px; padding:2px 0; }
    .eac-typing span{ width:6px; height:6px; border-radius:50%; background:var(--eac-primary); opacity:.5; animation:eac-blink 1.1s infinite ease-in-out; }
    .eac-typing span:nth-child(2){ animation-delay:.15s; }
    .eac-typing span:nth-child(3){ animation-delay:.3s; }
    @keyframes eac-blink{ 0%,80%,100%{ opacity:.3; transform:scale(.8);} 40%{ opacity:1; transform:scale(1);} }
    .eac-typing-label{ font-size:10.5px; color:var(--eac-text-muted); margin-top:2px; }

    /* ── Saran cepat (grid tetap di bawah #eac-suggestions) ── */
    .eac-suggestions{ display:grid; grid-template-columns:repeat(2, 1fr); row-gap:5px; column-gap:6px; padding:14px 10px 9px; margin-top:6px; border-top:1px solid var(--eac-border); background:var(--eac-bg-soft); flex-shrink:0; }
    .eac-suggestions > span{ grid-column:1 / -1; font-size:9px; color:var(--eac-text-muted); font-weight:600; margin-bottom:3px; line-height:1.2; }
    .eac-sug-btn{
        border:1px solid var(--eac-border); background:var(--eac-bg); color:var(--eac-primary);
        border-radius:999px; padding:4px 7px; font-size:9.5px; font-weight:600; cursor:pointer;
        transition:background .15s ease, transform .15s ease, box-shadow .15s ease;
        width:100%; text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.3;
    }
    .eac-sug-btn:hover{ background:var(--eac-primary); color:#fff; transform:translateY(-1px); box-shadow:0 4px 10px -3px rgba(76,40,140,.4); }

    /* ── Saran lanjutan: chip inline yang muncul di dalam jawaban Arsy
       (mis. "Cara tambah kode", "Buka menu Surat Masuk"). Sengaja TIDAK
       width:100% seperti .eac-sug-btn di atas — supaya beberapa chip
       bisa sejajar rapi dalam satu baris (seperti "tanya cepat"),
       bukan menumpuk satu kolom penuh ke bawah. ── */
    .eac-quick-chip{
        display:inline-flex; align-items:center; width:auto; max-width:100%;
        border:1px solid var(--eac-border); background:var(--eac-bg); color:var(--eac-primary);
        border-radius:999px; padding:6px 12px; font-size:11px; font-weight:600; cursor:pointer;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.3;
        transition:background .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .eac-quick-chip:hover{ background:var(--eac-primary); color:#fff; transform:translateY(-1px); box-shadow:0 4px 10px -3px rgba(76,40,140,.4); }

    /* ── File preview ── */
    #eac-file-preview{ display:none; flex-wrap:wrap; gap:6px; padding:8px 12px 0; flex-shrink:0; }
    .eac-file-thumb{
        display:flex; align-items:center; gap:6px; background:var(--eac-bg-soft); border:1px solid var(--eac-border);
        border-radius:10px; padding:5px 8px; font-size:11px; color:var(--eac-text); max-width:170px;
    }
    .eac-file-thumb img{ width:28px; height:28px; border-radius:6px; object-fit:cover; }
    .eac-file-thumb span:nth-child(2){ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .eac-file-thumb button{ border:none; background:transparent; color:var(--eac-text-muted); cursor:pointer; font-size:12px; flex-shrink:0; }
    .eac-file-thumb button:hover{ color:var(--eac-danger); }

    /* ── Input bar ── */
    .eac-input-bar{ display:flex; align-items:flex-end; gap:6px; padding:8px 10px; border-top:1px solid var(--eac-border); background:var(--eac-bg); flex-shrink:0; }
    #eac-file-input{ display:none; }
    #eac-attach{
        width:32px; height:32px; flex-shrink:0; border:none; border-radius:9px; cursor:pointer;
        background:var(--eac-bg-soft); color:var(--eac-primary); font-size:13px;
        display:flex; align-items:center; justify-content:center; transition:background .15s ease;
    }
    #eac-attach:hover{ background:var(--eac-border); }
    #eac-input{
        flex:1; resize:none; border:1px solid var(--eac-border); border-radius:11px; padding:7px 9px;
        font:inherit; font-size:11.5px; color:var(--eac-text); background:var(--eac-bg-soft);
        max-height:80px; min-height:32px; line-height:1.35; outline:none;
        overflow-y:auto; white-space:pre-wrap; word-break:break-word;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    #eac-input:focus{ border-color:var(--eac-primary); box-shadow:0 0 0 3px rgba(108,62,171,.15); }
    #eac-input::placeholder{ color:var(--eac-text-muted); white-space:normal; }
    #eac-input::-webkit-scrollbar{ width:5px; }
    #eac-input::-webkit-scrollbar-track{ background:transparent; }
    #eac-input::-webkit-scrollbar-thumb{ background:var(--eac-border); border-radius:6px; }
    #eac-send{
        width:32px; height:32px; flex-shrink:0; border:none; border-radius:10px; cursor:pointer;
        background:linear-gradient(135deg,var(--eac-primary),var(--eac-primary-dark)); color:#fff;
        display:flex; align-items:center; justify-content:center; transition:transform .15s ease, opacity .15s ease;
    }
    #eac-send:hover{ transform:scale(1.06); }
    #eac-send:disabled{ opacity:.5; cursor:not-allowed; transform:none; }
    #eac-send svg{ width:14px; height:14px; }

    /* ── Rekap toolbar ── */
    .eac-rekap-toolbar{ padding:8px 10px; border-bottom:1px solid var(--eac-border); background:var(--eac-bg-soft); flex-shrink:0; }
    .eac-rekap-row1{ display:flex; flex-wrap:wrap; gap:5px; }
    .eac-rekap-row1 select{
        flex:1; min-width:70px; border:1px solid var(--eac-border); border-radius:8px; padding:6px 7px;
        font-size:10.5px; color:var(--eac-text); background:var(--eac-bg); outline:none;
    }
    .eac-rekap-row1 select:focus{ border-color:var(--eac-primary); }
    .eac-btn-reset{
        border:none; border-radius:8px; padding:6px 10px; font-size:10.5px; font-weight:700; cursor:pointer;
        transition:transform .15s ease, opacity .15s ease; white-space:nowrap;
        background:var(--eac-bg); color:var(--eac-text-muted); border:1px solid var(--eac-border);
    }
    .eac-btn-reset:hover{ transform:translateY(-1px); color:var(--eac-danger); border-color:var(--eac-danger); }

    .eac-export-btns{ display:none; flex-wrap:wrap; gap:6px; margin-top:8px; align-items:center; }
    .eac-export-btns.show{ display:flex; }
    .eac-export-btns select{
        border:1px solid var(--eac-border); background:var(--eac-bg); color:var(--eac-text);
        border-radius:8px; padding:5px 7px; font-size:10.5px; outline:none; cursor:pointer;
        transition:border-color .15s ease;
    }
    .eac-export-btns select:focus{ border-color:var(--eac-primary); }
    .eac-export-btn{
        border:1px solid var(--eac-border); background:var(--eac-bg); color:var(--eac-text);
        border-radius:8px; padding:6px 10px; font-size:11px; font-weight:600; cursor:pointer;
        transition:background .15s ease, transform .15s ease;
    }
    .eac-export-btn:hover{ transform:translateY(-1px); background:var(--eac-bg-soft); }
    .eac-btn-print:hover{ border-color:var(--eac-primary); color:var(--eac-primary); }

    /* ── Dropdown Download (PDF/Word) ── */
    .eac-dropdown{ position:relative; display:inline-block; }
    .eac-btn-download{ display:flex; align-items:center; gap:4px; }
    .eac-btn-download:hover{ border-color:var(--eac-primary); color:var(--eac-primary); }
    .eac-dropdown-caret{ font-size:9px; transition:transform .15s ease; }
    .eac-dropdown.open .eac-dropdown-caret{ transform:rotate(180deg); }
    .eac-dropdown-menu{
        display:none; position:absolute; top:calc(100% + 6px); left:0; z-index:20;
        min-width:130px; background:var(--eac-bg); border:1px solid var(--eac-border);
        border-radius:10px; box-shadow:0 12px 28px -8px rgba(76,40,140,.28), 0 4px 10px rgba(0,0,0,.08);
        overflow:hidden; padding:4px;
    }
    .eac-dropdown.open .eac-dropdown-menu{ display:block; }
    .eac-dropdown-menu button{
        display:flex; align-items:center; gap:7px; width:100%; text-align:left;
        border:none; background:transparent; color:var(--eac-text); cursor:pointer;
        padding:7px 9px; font-size:11.5px; font-weight:600; border-radius:7px;
        transition:background .15s ease;
    }
    .eac-dropdown-menu button:hover{ background:var(--eac-bg-soft); color:var(--eac-primary); }

    /* ── Rekap body ──
       display:flex + min-height:0 supaya konten pendek (mis. state
       kosong) tetap mengisi seluruh area dan bentuk widget tidak
       ciut mengikuti isi. */
    .eac-rekap-body{ flex:1; min-height:0; overflow-y:auto; padding:12px; background:var(--eac-bg); display:flex; flex-direction:column; }
    .eac-empty{
        margin:auto; text-align:center; color:var(--eac-text-muted); font-size:12px;
        padding:30px 10px; display:flex; flex-direction:column; align-items:center; gap:6px;
    }
    .eac-loading{ flex:1; display:flex; align-items:center; justify-content:center; padding:40px 0; margin:auto; }
    .eac-loading-dots{ display:flex; gap:6px; }
    .eac-loading-dots span{ width:8px; height:8px; border-radius:50%; background:var(--eac-primary); animation:eac-blink 1.1s infinite ease-in-out; }
    .eac-loading-dots span:nth-child(2){ animation-delay:.15s; }
    .eac-loading-dots span:nth-child(3){ animation-delay:.3s; }

    .eac-rekap-info{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:6px; margin-bottom:10px; font-size:12px; color:var(--eac-text); }
    .eac-rekap-badges{ display:flex; gap:6px; flex-wrap:wrap; }
    .eac-badge{ padding:3px 9px; border-radius:999px; font-size:10.5px; font-weight:700; }
    .eac-badge.masuk{ background:#e3f5ea; color:var(--eac-success); }
    .eac-badge.keluar{ background:#fff3cd; color:var(--eac-warning); }
    .eac-badge.total{ background:var(--eac-bg-soft); color:var(--eac-primary); border:1px solid var(--eac-border); }
    [data-theme="dark"] .eac-badge.masuk{ background:#173628; }
    [data-theme="dark"] .eac-badge.keluar{ background:#3a3110; }

    .eac-tbl{ width:100%; border-collapse:collapse; font-size:11.5px; }
    .eac-tbl th{ text-align:left; padding:7px 6px; background:var(--eac-bg-soft); color:var(--eac-text-muted); font-weight:700; border-bottom:1px solid var(--eac-border); }
    .eac-tbl td{ padding:7px 6px; border-bottom:1px solid var(--eac-border); color:var(--eac-text); vertical-align:top; }
    .eac-tbl tr:hover td{ background:var(--eac-bg-soft); }
    .eac-tbl .total-row td{ font-weight:800; background:var(--eac-bg-soft); }
    .eac-tbl-link{ color:var(--eac-primary); font-weight:700; text-decoration:none; }
    .eac-tbl-link:hover{ text-decoration:underline; }

    .eac-pagination{ display:flex; justify-content:center; gap:5px; margin-top:12px; flex-wrap:wrap; }
    .eac-pg-btn{
        border:1px solid var(--eac-border); background:var(--eac-bg); color:var(--eac-text);
        border-radius:7px; padding:5px 9px; font-size:11px; cursor:pointer; transition:background .15s ease;
    }
    .eac-pg-btn:hover{ background:var(--eac-bg-soft); }
    .eac-pg-btn.active{ background:var(--eac-primary); color:#fff; border-color:var(--eac-primary); }

    /* ── Mobile ── */
    @media (max-width:480px){
        #earsipchat-panel{
            right:12px; left:12px; bottom:12px; top:auto; width:auto; max-width:360px; margin-left:auto;
            height:auto; max-height:min(64vh, 480px);
            font-size:11px;
        }
        .eac-avatar{ width:28px; height:28px; font-size:14px; }
        .eac-header-info h4{ font-size:12px; }
        .eac-header-info span{ font-size:9.5px; }
        .eac-header-actions button{ width:24px; height:24px; font-size:11px; }
        .eac-bubble-avatar{ width:20px; height:20px; font-size:10px; }
        .eac-bubble-text{ font-size:11px; padding:7px 9px; line-height:1.4; }
        .eac-sug-btn{ font-size:9px; padding:4px 5px; }
        .eac-quick-chip{ font-size:10px; padding:5px 10px; }
        #eac-input{ font-size:11px; }
        #earsipchat-btn{ right:16px; bottom:16px; }
    }
</style>

{{-- ══ Tombol ══ --}}
<button id="earsipchat-btn" title="Buka Asisten E-Arsip" aria-label="Buka chat asisten"
    data-user="{{ Auth::id() }}" data-sid="{{ session()->getId() }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.84L3 20l1.09-3.14C3.4 15.5 3 13.79 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
    </svg>
</button>

{{-- ══ Panel ══ --}}
<div id="earsipchat-panel" role="dialog" aria-label="Asisten E-Arsip">

    {{-- Header --}}
    <div class="eac-header">
        <div class="eac-header-left">
            <div class="eac-avatar">🤖</div>
            <div class="eac-header-info">
                <h4>Arsy — Asisten E-Arsip</h4>
                <span>SMA Babussalam • Online</span>
            </div>
        </div>
        <div class="eac-header-actions">
            <button id="eac-theme-btn" title="Ganti tema gelap/terang" aria-label="Ganti tema">
                <svg class="eac-theme-icon eac-theme-icon-sun" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="4"/>
                    <path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                </svg>
                <svg class="eac-theme-icon eac-theme-icon-moon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
            </button>
            <button id="eac-clear-btn" title="Hapus percakapan">🗑</button>
            <button id="earsipchat-close" title="Tutup">✕</button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="eac-tabs">
        <button class="eac-tab active" data-tab="chat">💬 Chat</button>
        <button class="eac-tab" data-tab="rekap">📋 Rekap Surat</button>
    </div>

    {{-- ══ TAB CHAT ══ --}}
    <div id="eac-tab-chat" style="display:flex;flex-direction:column;flex:1;overflow:hidden;">
        <div class="eac-messages" id="eac-messages">
            <div class="eac-bubble bot">
                <div class="eac-bubble-avatar">🤖</div>
                <div class="eac-bubble-text">
                    Halo <strong>{{ Auth::user()->name ?? 'Pengguna' }}</strong>! 👋<br>
                    Saya <strong>Arsy</strong> — asisten E-Arsip SMA Babussalam.<br>
                    <span style="display:block;margin-top:6px;line-height:1.85">
                        🔍 <strong>Cari surat</strong> — "carikan surat wisuda bulan ini"<br>
                        🎓 <strong>Data peserta_didik</strong> — "cari peserta_didik rombel angkatan 2024-2025"<br>
                        📊 <strong>Statistik</strong> — "berapa total surat masuk tahun ini?"<br>
                        🧭 <strong>Navigasi</strong> — "buka menu laporan"<br>
                        🎨 <strong>Ganti tema</strong> — "ubah ke mode gelap"<br>
                        📎 <strong>Analisis file</strong> — lampirkan PDF atau Word
                    </span>
                    <span style="display:block;margin-top:6px;color:#7c5cbf;font-size:11px">Ketik pertanyaan atau pilih menu cepat di bawah ⬇️</span>
                </div>
            </div>
        </div>
        <div class="eac-suggestions" id="eac-suggestions">
            <span>Tanya cepat:</span>
            <button class="eac-sug-btn" data-url="/dashboard">🏠 Menu Dashboard</button>
            <button class="eac-sug-btn" data-url="/surat/filter/masuk">📥 Menu Surat Masuk</button>
            <button class="eac-sug-btn" data-url="/surat/filter/keluar">📤 Menu Surat Keluar</button>
            <button class="eac-sug-btn" data-url="/peserta-didik">🎓 Menu Peserta Didik</button>
            <button class="eac-sug-btn" data-url="/kode">🔢 Menu Kode Surat</button>
            <button class="eac-sug-btn" data-url="/laporan">📊 Laporan</button>
            @if(strtolower(auth()->user()->role?->name ?? '') === 'kepala staf')
                <button class="eac-sug-btn" data-url="{{ route('user.index') }}">👤 Menu User</button>
            @endif
            <button class="eac-sug-btn" data-msg="tampilkan statistik arsip hari ini">📈 Statistik</button>
            <button class="eac-sug-btn" data-msg="carikan surat bulan ini">🔍 Surat Bulan Ini</button>
            <button class="eac-sug-btn" data-msg="apa saja yang bisa kamu bantu?">❓ Apa yang bisa Arsy bantu?</button>
        </div>
        <div id="eac-file-preview"></div>
        <div class="eac-input-bar">
            <input type="file" id="eac-file-input" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" multiple>
            <button id="eac-attach" title="Lampirkan file (PDF atau Word)">📎</button>
            <textarea id="eac-input" rows="1" placeholder="Tanya atau lampirkan PDF/Word..."></textarea>
            <button id="eac-send" title="Kirim">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2v-4"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ══ TAB REKAP ══ --}}
    <div id="eac-tab-rekap" style="display:none;flex-direction:column;flex:1;overflow:hidden;">

        {{-- Toolbar Filter --}}
        <div class="eac-rekap-toolbar">
            <div class="eac-rekap-row1">
                <select id="eac-r-bulan" onchange="eacLoadRekap(1)">
                    <option value="" selected>— Semua Bulan —</option>
                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $b)
                        <option value="{{ $i+1 }}">{{ $b }}</option>
                    @endforeach
                </select>
                <select id="eac-r-tahun" onchange="eacLoadRekap(1)">
                    @for($y = date('Y'); $y >= date('Y')-5; $y--)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <select id="eac-r-jenis" onchange="eacLoadRekap(1)">
                    <option value="">Semua</option>
                    <option value="masuk">Masuk</option>
                    <option value="keluar">Keluar</option>
                </select>
                <button class="eac-btn-reset" onclick="eacResetRekap()" title="Reset filter & tabel rekap">↺ Reset</button>
            </div>
            {{-- Tombol Export (muncul setelah data tampil) --}}
            <div class="eac-export-btns" id="eac-export-btns">
                <select id="eac-r-paper" title="Ukuran kertas cetak">
                    <option value="f4">📄 F4 (Folio)</option>
                    <option value="a4">📄 A4</option>
                </select>
                <button class="eac-export-btn eac-btn-print" onclick="eacCetak()">🖨 Cetak</button>
                <div class="eac-dropdown" id="eac-download-dropdown">
                    <button class="eac-export-btn eac-btn-download" onclick="eacToggleDownloadMenu(event)">
                        ⬇ Download <span class="eac-dropdown-caret">▾</span>
                    </button>
                    <div class="eac-dropdown-menu" id="eac-download-menu">
                        <button onclick="eacExport('pdf'); eacToggleDownloadMenu();">📄 PDF</button>
                        <button onclick="eacExport('word'); eacToggleDownloadMenu();">📝 Word</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body / Tabel --}}
        <div class="eac-rekap-body" id="eac-rekap-body">
            <div class="eac-empty">📋 Pilih periode di atas — rekap otomatis tampil</div>
        </div>
    </div>
</div>

{{-- ══ JS ══
     Semua logika widget ini ada di resources/js/chat.js (bukan inline
     lagi) — supaya file komponen ini bersih dari JS dan JS-nya bisa
     di-cache/minify seperti file resources/js lain (dashboard.js, dst).
     File ini SUDAH dimuat lewat @vite(['resources/js/chat.js', ...]) di
     resources/views/layouts/app.blade.php, jadi TIDAK perlu <script src>
     tambahan di sini — kalau ditambahkan lagi, browser akan mencoba GET
     ke public/js/chat.js yang tidak pernah ada (404) karena Vite
     mem-build filenya ke public/build/assets/..., bukan ke public/js/. --}}