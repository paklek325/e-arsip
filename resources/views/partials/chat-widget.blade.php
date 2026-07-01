{{-- ============================================================
     ASISTEN E-ARSIP — Chat Widget v2
     Letakkan di: resources/views/components/chat-widget.blade.php
     Panggil di layout: @include('components.chat-widget')
     Pastikan <meta name="csrf-token" content="{{ csrf_token() }}"> ada di <head>
     ============================================================ --}}

<style>
/* ── Reset & Variables ── */
#earsipchat-btn,
#earsipchat-panel,
#earsipchat-panel * {
    box-sizing: border-box;
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

/* ── Tombol Buka Chat ── */
#earsipchat-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6C3EAB, #4F46E5);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(108,62,171,.4);
    z-index: 9998;
    transition: transform .2s, box-shadow .2s;
}
#earsipchat-btn:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(108,62,171,.55); }
#earsipchat-btn svg   { width: 24px; height: 24px; }

/* ── Panel ── */
#earsipchat-panel {
    position: fixed;
    bottom: 86px;
    right: 24px;
    width: 420px;
    height: min(580px, calc(100vh - 100px));
    max-height: calc(100vh - 100px);
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 9999;
    opacity: 0;
    transform: translateY(10px);
    pointer-events: none;
    transition: opacity .15s ease, transform .15s ease;
}
#earsipchat-panel.open { opacity:1; transform:translateY(0); pointer-events:all; }
#earsipchat-panel:not(.open) * { pointer-events:none; }

/* ── Header ── */
.eac-header {
    background: linear-gradient(135deg, #6C3EAB, #4F46E5);
    padding: 13px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.eac-header-left { display:flex; align-items:center; gap:10px; }
.eac-avatar { width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
.eac-header-info h4 { margin:0; font-size:13.5px; font-weight:600; color:#fff; line-height:1.2; }
.eac-header-info span { font-size:11px; color:rgba(255,255,255,.75); }
.eac-header-actions { display:flex; gap:6px; }
.eac-header-actions button { background:rgba(255,255,255,.15); border:none; color:#fff; width:28px; height:28px; border-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .15s; font-size:13px; }
.eac-header-actions button:hover { background:rgba(255,255,255,.28); }

/* ── Tabs ── */
.eac-tabs { display:flex; background:#f8f8ff; border-bottom:1px solid #ebe8f5; flex-shrink:0; }
.eac-tab { flex:1; padding:10px 0; background:none; border:none; font-size:12.5px; font-weight:500; color:#888; cursor:pointer; border-bottom:2px solid transparent; display:flex; align-items:center; justify-content:center; gap:5px; transition:color .15s; }
.eac-tab.active { color:#6C3EAB; border-bottom:2px solid #6C3EAB; font-weight:600; }

/* ── Chat ── */
.eac-messages { flex:1; overflow-y:auto; padding:13px; display:flex; flex-direction:column; gap:10px; scroll-behavior:smooth; }
.eac-messages::-webkit-scrollbar { width:4px; }
.eac-messages::-webkit-scrollbar-thumb { background:#ddd; border-radius:4px; }
.eac-bubble { display:flex; gap:8px; align-items:flex-start; max-width:93%; animation:eacFI .2s ease; }
@keyframes eacFI { from{opacity:0;transform:translateY(5px)} to{opacity:1;transform:translateY(0)} }
.eac-bubble.user { align-self:flex-end; flex-direction:row-reverse; }
.eac-bubble-avatar { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; background:#e8e3f8; }
.eac-bubble.user .eac-bubble-avatar { background:linear-gradient(135deg,#6C3EAB,#4F46E5); color:#fff; }
.eac-bubble-text { background:#f1f0f8; border-radius:14px 14px 14px 4px; padding:9px 13px; font-size:13px; line-height:1.6; color:#2d2d2d; max-width:100%; word-break:break-word; }
.eac-bubble.user .eac-bubble-text { background:linear-gradient(135deg,#6C3EAB,#4F46E5); color:#fff; border-radius:14px 14px 4px 14px; }
.eac-bubble-text a { color:#6C3EAB; text-decoration:underline; }
.eac-bubble.user .eac-bubble-text a { color:#d4c8f8; }
.eac-error { background:#fff0f0; color:#c0392b; border:1px solid #fdc; border-radius:10px; padding:8px 12px; font-size:12.5px; }
.eac-typing { display:flex; gap:4px; align-items:center; padding:4px 2px; }
.eac-typing span { width:7px; height:7px; border-radius:50%; background:#9b87d1; animation:eacB 1.2s infinite ease-in-out; }
.eac-typing span:nth-child(2){animation-delay:.2s} .eac-typing span:nth-child(3){animation-delay:.4s}
@keyframes eacB { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }
.eac-typing-label { font-size:10.5px; color:#9b87d1; margin-top:4px; font-style:italic; animation:eacFade 1.5s infinite ease-in-out; }
@keyframes eacFade { 0%,100%{opacity:.5} 50%{opacity:1} }
.eac-error { background:#fff4f4; color:#b91c1c; border:1px solid #fecaca; border-radius:10px; padding:8px 13px; font-size:12.5px; margin:0 4px; }
.eac-suggestions { padding:7px 13px 9px; display:flex; flex-wrap:wrap; gap:5px; border-top:1px solid #f0edf8; flex-shrink:0; }
.eac-suggestions > span { font-size:11px; color:#888; width:100%; margin-bottom:1px; font-weight:500; }
.eac-sug-btn { background:#f1eef8; border:1px solid #d8d0f0; color:#5a3d96; border-radius:20px; padding:4px 10px; font-size:11.5px; cursor:pointer; transition:all .15s; white-space:nowrap; font-weight:500; }
.eac-sug-btn:hover { background:#6C3EAB; color:#fff; border-color:#6C3EAB; }
.eac-input-bar { padding:9px 11px 11px; border-top:1px solid #ede9f8; display:flex; gap:8px; align-items:flex-end; flex-shrink:0; background:#fafafa; }
#eac-input { flex:1; border:1.5px solid #ddd; border-radius:22px; padding:8px 13px; font-size:13px; resize:none; max-height:90px; outline:none; line-height:1.4; transition:border-color .15s; background:#fff; font-family:inherit; color:#2d2d2d; }
#eac-input::placeholder { color:#b0b0b0; }
#eac-input:focus { border-color:#6C3EAB; }
#eac-send { width:37px; height:37px; border-radius:50%; background:linear-gradient(135deg,#6C3EAB,#4F46E5); border:none; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:transform .15s,opacity .15s; }
#eac-send:hover:not(:disabled) { transform:scale(1.08); }
#eac-send:disabled { opacity:.45; cursor:default; }
#eac-send svg { width:16px; height:16px; }

/* ── Upload file ── */
#eac-attach { width:32px; height:32px; border-radius:50%; background:#f1eef8; border:1.5px solid #d8d0f0; color:#6C3EAB; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:background .15s; font-size:15px; }
#eac-attach:hover { background:#6C3EAB; color:#fff; }
#eac-file-input { display:none; }
#eac-file-preview { padding:6px 13px 0; display:none; flex-wrap:wrap; gap:5px; }
.eac-file-chip { display:inline-flex; align-items:center; gap:5px; background:#ede9f8; border:1px solid #d0c8ef; border-radius:20px; padding:3px 10px; font-size:11.5px; color:#5a3d96; font-weight:500; max-width:200px; }
.eac-file-chip span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.eac-file-chip button { background:none; border:none; color:#9b87d1; cursor:pointer; padding:0; font-size:13px; line-height:1; flex-shrink:0; }
.eac-file-chip button:hover { color:#c0392b; }
.eac-file-thumb { display:flex; align-items:center; gap:6px; background:#f8f6ff; border:1px solid #ddd; border-radius:10px; padding:6px 10px; font-size:12px; color:#444; max-width:180px; }
.eac-file-thumb img { width:36px; height:36px; object-fit:cover; border-radius:6px; flex-shrink:0; }
.eac-file-thumb .eac-file-icon { font-size:22px; flex-shrink:0; }
.eac-file-thumb span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* ── Tab Rekap ── */
#eac-tab-rekap { display:none; flex-direction:column; flex:1; overflow:hidden; }

.eac-rekap-toolbar {
    padding: 10px 13px;
    border-bottom: 1px solid #ebe8f5;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-shrink: 0;
    background: #faf9ff;
}
.eac-rekap-row1 { display:flex; gap:7px; align-items:center; flex-wrap:wrap; }
.eac-rekap-row2 { display:flex; gap:6px; flex-wrap:wrap; }

.eac-rekap-toolbar select {
    padding: 6px 9px;
    border-radius: 8px;
    border: 1.5px solid #d8d0f0;
    font-size: 12px;
    color: #333;
    outline: none;
    cursor: pointer;
    background: #fff;
    transition: border-color .15s;
}
.eac-rekap-toolbar select:focus { border-color: #6C3EAB; }
.eac-rekap-toolbar select#eac-r-bulan { flex:1; min-width:90px; }
.eac-rekap-toolbar select#eac-r-tahun { width:72px; }
.eac-rekap-toolbar select#eac-r-jenis { width:95px; }

.eac-btn-tampil {
    padding: 6px 14px;
    border-radius: 8px;
    background: linear-gradient(135deg,#6C3EAB,#4F46E5);
    color: #fff;
    border: none;
    font-size: 12px;
    cursor: pointer;
    font-weight: 600;
    transition: opacity .15s;
    white-space: nowrap;
}
.eac-btn-tampil:hover { opacity: .88; }

/* Tombol aksi export */
.eac-export-btns { display:none; gap:5px; flex-wrap:wrap; }
.eac-export-btns.show { display:flex; }
.eac-export-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: opacity .15s, transform .1s;
    text-decoration: none;
    white-space: nowrap;
}
.eac-export-btn:hover { opacity:.85; transform:scale(1.03); }
.eac-btn-print  { background:#e8f5e9; color:#2e7d32; }
.eac-btn-pdf    { background:#fce8e8; color:#c62828; }
.eac-btn-excel  { background:#e8f4e8; color:#1b5e20; }
.eac-btn-word   { background:#e3eaf8; color:#1a3c6e; }

/* Tabel hasil rekap */
.eac-rekap-body { flex:1; overflow-y:auto; padding:10px 13px; }
.eac-rekap-body::-webkit-scrollbar { width:4px; }
.eac-rekap-body::-webkit-scrollbar-thumb { background:#ddd; border-radius:4px; }

.eac-rekap-info {
    font-size:12px;
    color:#666;
    margin-bottom:8px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:4px;
}
.eac-rekap-info strong { color:#6C3EAB; }
.eac-rekap-badges { display:flex; gap:5px; }
.eac-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:600; }
.eac-badge.masuk  { background:#e6f4ea; color:#2d7a4f; }
.eac-badge.keluar { background:#fff3cd; color:#856404; }
.eac-badge.total  { background:#ede9f8; color:#5a3d96; }

/* Tabel per-bulan (detail) */
.eac-tbl { width:100%; border-collapse:collapse; font-size:11.5px; }
.eac-tbl th { background:#f0ecfb; color:#5a3d96; padding:7px 8px; text-align:left; font-weight:700; border-bottom:2px solid #d8d0f0; white-space:nowrap; position:sticky; top:0; }
.eac-tbl td { padding:6px 8px; border-bottom:1px solid #f0edf8; color:#333; vertical-align:middle; }
.eac-tbl tr:hover td { background:#faf8ff; }
.eac-tbl-link { color:#6C3EAB; text-decoration:none; font-weight:600; }
.eac-tbl-link:hover { text-decoration:underline; }

/* Tabel rekap tahunan */
.eac-tbl-year th { background:#f0ecfb; color:#5a3d96; padding:7px 9px; text-align:left; font-weight:700; border-bottom:2px solid #d8d0f0; position:sticky; top:0; }
.eac-tbl-year td { padding:7px 9px; border-bottom:1px solid #f0edf8; color:#333; }
.eac-tbl-year tr.total-row td { background:#ede9f8; font-weight:700; color:#5a3d96; }
.eac-tbl-year tr:hover td { background:#faf8ff; }

/* Empty / loading */
.eac-empty { text-align:center; color:#aaa; font-size:12px; padding:28px 0; }
.eac-loading { text-align:center; padding:20px 0; }
.eac-loading-dots { display:inline-flex; gap:5px; }
.eac-loading-dots span { width:8px; height:8px; border-radius:50%; background:#9b87d1; animation:eacB 1.2s infinite; }
.eac-loading-dots span:nth-child(2){animation-delay:.2s} .eac-loading-dots span:nth-child(3){animation-delay:.4s}

/* Pagination */
.eac-pagination { display:flex; justify-content:center; gap:4px; padding:8px 0 2px; flex-wrap:wrap; }
.eac-pg-btn { padding:3px 9px; border-radius:6px; border:1.5px solid #d8d0f0; background:#fff; color:#5a3d96; font-size:11px; cursor:pointer; transition:all .15s; font-weight:500; }
.eac-pg-btn:hover { background:#6C3EAB; color:#fff; border-color:#6C3EAB; }
.eac-pg-btn.active { background:#6C3EAB; color:#fff; border-color:#6C3EAB; font-weight:700; }
.eac-pg-btn:disabled { opacity:.4; cursor:default; }

@media (max-width:440px) { #earsipchat-panel { right:8px; left:8px; width:auto; bottom:76px; height:min(520px, calc(100vh - 96px)); } }
</style>

{{-- ══ Tombol ══ --}}
<button id="earsipchat-btn" title="Buka Asisten E-Arsip" aria-label="Buka chat asisten">
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
                    <span style="display:block;margin-top:8px;line-height:2.1">
                        🔍 <strong>Cari surat</strong> — "carikan surat wisuda bulan ini"<br>
                        🎓 <strong>Data peserta_didik</strong> — "cari peserta_didik IPA angkatan 2024"<br>
                        📊 <strong>Statistik</strong> — "berapa total surat masuk tahun ini?"<br>
                        🧭 <strong>Navigasi</strong> — "buka menu laporan"<br>
                        📎 <strong>Analisis file</strong> — lampirkan PDF/Word/Excel
                    </span>
                    <span style="display:block;margin-top:8px;color:#7c5cbf;font-size:12px">Ketik pertanyaan atau pilih menu cepat di bawah ⬇️</span>
                </div>
            </div>
        </div>
        <div class="eac-suggestions" id="eac-suggestions">
            <span>Tanya cepat:</span>
            <button class="eac-sug-btn" data-msg="menu surat">📁 Menu Surat</button>
            <button class="eac-sug-btn" data-msg="menu peserta_didik">🎓 Menu Peserta Didik</button>
            <button class="eac-sug-btn" data-msg="menu laporan">📊 Laporan</button>
            <button class="eac-sug-btn" data-msg="tampilkan statistik arsip hari ini">📈 Statistik</button>
            <button class="eac-sug-btn" data-msg="carikan surat bulan ini">🔍 Surat Bulan Ini</button>
            <button class="eac-sug-btn" data-msg="apa saja yang bisa kamu bantu?">❓ Apa yang bisa Arsy bantu?</button>
        </div>
        <div id="eac-file-preview"></div>
        <div class="eac-input-bar">
            <input type="file" id="eac-file-input" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" multiple>
            <button id="eac-attach" title="Lampirkan file (gambar/PDF/Word/Excel/PPT)">📎</button>
            <textarea id="eac-input" rows="1" placeholder="Tanya cara pakai / cari surat / lampirkan file..."></textarea>
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
                <select id="eac-r-bulan">
                    <option value="">— Semua Bulan —</option>
                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $b)
                        <option value="{{ $i+1 }}" {{ (date('n') == $i+1) ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
                <select id="eac-r-tahun">
                    @for($y = date('Y'); $y >= date('Y')-5; $y--)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <select id="eac-r-jenis">
                    <option value="">Semua</option>
                    <option value="masuk">Masuk</option>
                    <option value="keluar">Keluar</option>
                </select>
                <button class="eac-btn-tampil" onclick="eacLoadRekap(1)">Tampilkan</button>
            </div>
            {{-- Tombol Export (muncul setelah data tampil) --}}
            <div class="eac-export-btns" id="eac-export-btns">
                <button class="eac-export-btn eac-btn-print" onclick="eacCetak()">🖨 Cetak</button>
                <button class="eac-export-btn eac-btn-pdf"   onclick="eacExport('pdf')">📄 PDF</button>
                <button class="eac-export-btn eac-btn-excel" onclick="eacExport('excel')">📊 Excel</button>
                <button class="eac-export-btn eac-btn-word"  onclick="eacExport('word')">📝 Word</button>
            </div>
        </div>

        {{-- Body / Tabel --}}
        <div class="eac-rekap-body" id="eac-rekap-body">
            <div class="eac-empty">📋 Pilih periode lalu klik <strong>Tampilkan</strong></div>
        </div>
    </div>
</div>

{{-- Frame tersembunyi untuk cetak --}}
<iframe id="eac-print-frame" style="display:none;"></iframe>

<script>
(function () {
    /* ── Storage keys (percakapan bertahan saat pindah menu, hilang saat logout) ── */
    const HKEY = 'eac_history';
    const MKEY = 'eac_messages_html';
    const PKEY = 'eac_panel_open';

    /* ── Deteksi ganti user / logout — bersihkan state ── */
    const CURRENT_USER  = '{{ Auth::id() }}';
    const STORED_USER   = sessionStorage.getItem('eac_user');
    const isSameSession = !!STORED_USER && STORED_USER === CURRENT_USER;
    if (!isSameSession) {
        // User baru / habis logout-login — riwayat chat lama tidak relevan lagi
        sessionStorage.removeItem(HKEY);
        sessionStorage.removeItem(MKEY);
        sessionStorage.removeItem(PKEY);
    }
    sessionStorage.setItem('eac_user', CURRENT_USER);

    /* ── state ── */
    let history = [];
    if (isSameSession) {
        try { history = JSON.parse(sessionStorage.getItem(HKEY) || '[]'); } catch (_) { history = []; }
    }
    function persistHistory() {
        try { sessionStorage.setItem(HKEY, JSON.stringify(history.slice(-20))); } catch (_) {}
    }
    let isTyping  = false;
    let currentPage = 1;
    let lastFilter  = {};

    /* ── elements ── */
    const btn      = document.getElementById('earsipchat-btn');
    const panel    = document.getElementById('earsipchat-panel');
    const closeBtn = document.getElementById('earsipchat-close');
    const clearBtn = document.getElementById('eac-clear-btn');
    const msgs     = document.getElementById('eac-messages');
    const WELCOME_HTML = msgs.innerHTML; // tampilan awal (sebelum dipulihkan dari sessionStorage), dipakai lagi saat "Hapus Percakapan"
    const input    = document.getElementById('eac-input');
    const sendBtn  = document.getElementById('eac-send');
    const sugsEl   = document.getElementById('eac-suggestions');
    const exportBtns = document.getElementById('eac-export-btns');
    const attachBtn  = document.getElementById('eac-attach');
    const fileInput  = document.getElementById('eac-file-input');
    const filePreview = document.getElementById('eac-file-preview');
    let pendingFiles = []; // file yang belum dikirim

    /* ── File attach ── */
    attachBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
        Array.from(fileInput.files).forEach(f => {
            if (pendingFiles.length >= 3) return; // max 3 file
            pendingFiles.push(f);
        });
        fileInput.value = '';
        renderFilePreviews();
    });

    function renderFilePreviews() {
        filePreview.innerHTML = '';
        if (pendingFiles.length === 0) { filePreview.style.display='none'; return; }
        filePreview.style.display = 'flex';
        pendingFiles.forEach((f, i) => {
            const isImg = f.type.startsWith('image/');
            const icon = getFileIcon(f.name);
            const chip = document.createElement('div');
            chip.className = 'eac-file-thumb';
            if (isImg) {
                const url = URL.createObjectURL(f);
                chip.innerHTML = `<img src="${url}" alt="preview"><span title="${esc(f.name)}">${esc(f.name)}</span><button onclick="removeFile(${i})" title="Hapus">✕</button>`;
            } else {
                chip.innerHTML = `<span class="eac-file-icon">${icon}</span><span title="${esc(f.name)}">${esc(f.name)}</span><button onclick="removeFile(${i})" title="Hapus">✕</button>`;
            }
            filePreview.appendChild(chip);
        });
    }

    window.removeFile = function(i) {
        pendingFiles.splice(i, 1);
        renderFilePreviews();
    };

    function getFileIcon(name) {
        const ext = name.split('.').pop().toLowerCase();
        const map = { pdf:'📄', doc:'📝', docx:'📝', xls:'📊', xlsx:'📊', ppt:'📋', pptx:'📋',
                      jpg:'🖼', jpeg:'🖼', png:'🖼', gif:'🖼', webp:'🖼' };
        return map[ext] || '📎';
    }

    /* ── bersihkan Bootstrap backdrop yang tertinggal ── */
    function cleanBackdrop() {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }

    /* ── toggle panel ── */
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        cleanBackdrop();
        panel.classList.add('open');
        btn.style.display = 'none';
        try { sessionStorage.setItem(PKEY, '1'); } catch (_) {}
        input.focus();
    });
    closeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        panel.classList.remove('open');
        btn.style.display = 'flex';
        cleanBackdrop();
        try { sessionStorage.setItem(PKEY, '0'); } catch (_) {}
    });
    document.addEventListener('click', (e) => {
        if (panel.classList.contains('open') && !panel.contains(e.target) && !btn.contains(e.target))
            { panel.classList.remove('open'); btn.style.display='flex'; cleanBackdrop(); try { sessionStorage.setItem(PKEY, '0'); } catch (_) {} }
    });

    /* ── clear ── */
    clearBtn.addEventListener('click', () => {
        history.length = 0;
        persistHistory();
        try { sessionStorage.removeItem(MKEY); } catch (_) {}
        msgs.innerHTML = WELCOME_HTML;
        sugsEl.style.display = 'flex';
    });

    /* ── tabs ── */
    document.querySelectorAll('.eac-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.eac-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const t = tab.dataset.tab;
            document.getElementById('eac-tab-chat').style.display  = t==='chat'  ? 'flex' : 'none';
            document.getElementById('eac-tab-rekap').style.display = t==='rekap' ? 'flex' : 'none';
        });
    });

    /* ── saran ── */
    sugsEl.addEventListener('click', e => {
        const b = e.target.closest('.eac-sug-btn');
        if (b) sendMessage(b.dataset.msg);
    });

    /* ── input ── */
    input.addEventListener('input', () => { input.style.height='auto'; input.style.height=Math.min(input.scrollHeight,90)+'px'; });
    input.addEventListener('keydown', e => { if (e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMessage(input.value.trim());} });
    sendBtn.addEventListener('click', () => sendMessage(input.value.trim()));

    /* ── CSRF ── */
    function csrf() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    /* ── Refresh CSRF token jika expired (419) ── */
    async function refreshCsrf() {
        try {
            const r = await fetch('/chat/csrf-token', { credentials: 'same-origin' });
            if (r.ok) {
                const data = await r.json();
                if (data.token) {
                    const m = document.querySelector('meta[name="csrf-token"]');
                    if (m) m.setAttribute('content', data.token);
                }
            }
        } catch (_) {}
    }

    async function fetchWithCsrf(url, options) {
        let r = await fetch(url, { ...options, headers: { ...options.headers, 'X-CSRF-TOKEN': csrf() } });
        if (r.status === 419) {
            // Token expired — refresh dan coba lagi sekali
            await refreshCsrf();
            r = await fetch(url, { ...options, headers: { ...options.headers, 'X-CSRF-TOKEN': csrf() } });
        }
        return r;
    }

    /* ══ SEND MESSAGE ══ */
    function sendMessage(text) {
        const hasFile = pendingFiles.length > 0;
        if (!text && !hasFile) return;
        if (isTyping) return;

        const msg = text || (hasFile ? 'Tolong analisis file ini.' : '');
        input.value = ''; input.style.height = 'auto';
        sugsEl.style.display = 'none';

        // Tampilkan bubble user dengan preview file jika ada
        addUserBubble(msg, [...pendingFiles]);
        const typingEl = addTypingIndicator();
        isTyping = true; sendBtn.disabled = true;

        if (hasFile) {
            const files = [...pendingFiles];
            pendingFiles = [];
            renderFilePreviews();
            fetchFile(msg, files, typingEl);
        } else {
            fetchData(msg, typingEl);
        }
    }

    /* ── Upload + analisis file ── */
    function fetchFile(caption, files, typingEl) {
        const fd = new FormData();
        fd.append('message', caption || 'Tolong analisis file ini dan berikan ringkasan.');
        files.forEach((f, i) => fd.append(`files[${i}]`, f));

        fetchWithCsrf('/chat/upload', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            typingEl.remove();
            if (data.success && data.message) {
                addBotBubble(data.message);
                history.push({role:'user', content: caption || '[File dikirim]'});
                history.push({role:'assistant', content: data.message});
                persistHistory();
            } else {
                addErrorBubble(data.message || 'Gagal menganalisis file.');
            }
        })
        .catch(() => { typingEl.remove(); addErrorBubble('Koneksi bermasalah saat upload file.'); })
        .finally(() => { isTyping=false; sendBtn.disabled=false; input.focus(); });
    }

    function fetchData(text, typingEl) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 25000); // 25s timeout

        fetchWithCsrf('/chat/ask', {
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json'},
            body: JSON.stringify({ message:text, history:history.slice(-8), page:window.location.pathname }),
            signal: controller.signal,
        })
        .then(r => {
            clearTimeout(timeout);
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            typingEl.remove();
            const reply = data.message || '';
            if (reply) {
                addBotBubble(reply);
                history.push({role:'user',content:text});
                history.push({role:'assistant',content:reply});
                persistHistory();
                // sugsEl (tanya cepat awal) tetap tersembunyi setelah chat dimulai
                if (Array.isArray(data.suggestions) && data.suggestions.length) {
                    addQuickChips(data.suggestions);
                }
            } else {
                addErrorBubble('Maaf, tidak ada respons dari server. Coba lagi.');
            }
        })
        .catch(err => {
            clearTimeout(timeout);
            typingEl.remove();
            if (err.name === 'AbortError') {
                addErrorBubble('⏱️ Permintaan terlalu lama. Coba pertanyaan yang lebih singkat.');
            } else if (err.message.includes('HTTP 5')) {
                addErrorBubble('⚠️ Server sedang bermasalah. Coba beberapa saat lagi.');
            } else {
                addErrorBubble('🔌 Koneksi bermasalah. Periksa internet dan coba lagi.');
            }
        })
        .finally(()=>{ isTyping=false; sendBtn.disabled=false; input.focus(); });
    }

    function fetchStream(text, typingEl) {
        fetchWithCsrf('/chat/ask', {
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'text/event-stream'},
            body: JSON.stringify({ message:text, history:history.slice(-6), page:window.location.pathname })
        })
        .then(r => {
            typingEl.remove();
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            const wrapper = addBotBubble('');
            const textEl  = wrapper.querySelector('.eac-bubble-text');
            let full = '';
            const reader = r.body.getReader(), dec = new TextDecoder();
            function read() {
                reader.read().then(({done,value})=>{
                    if (done) { history.push({role:'user',content:text}); history.push({role:'assistant',content:full}); persistHistory(); isTyping=false; sendBtn.disabled=false; input.focus(); return; }
                    dec.decode(value).split('\n').forEach(line=>{
                        if (!line.startsWith('data: ')) return;
                        const raw = line.slice(6).trim();
                        if (raw==='[DONE]') return;
                        try { const d=JSON.parse(raw).choices?.[0]?.delta?.content; if(d){full+=d;textEl.innerHTML=md(full);msgs.scrollTop=msgs.scrollHeight;} } catch(_){}
                    });
                    read();
                });
            }
            read();
        })
        .catch(()=>{ typingEl.remove(); addErrorBubble('Koneksi bermasalah. Coba lagi.'); isTyping=false; sendBtn.disabled=false; });
    }

    /* ── bubble helpers ── */
    function addUserBubble(t, files) {
        const d=document.createElement('div'); d.className='eac-bubble user';
        let fileHtml = '';
        if (files && files.length > 0) {
            fileHtml = '<div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:' + (t?'5px':'0') + '">';
            files.forEach(f => {
                if (f.type.startsWith('image/')) {
                    const url = URL.createObjectURL(f);
                    fileHtml += `<img src="${url}" style="max-width:110px;max-height:80px;border-radius:8px;object-fit:cover;" alt="${esc(f.name)}">`;
                } else {
                    fileHtml += `<span style="background:rgba(255,255,255,.2);border-radius:8px;padding:3px 8px;font-size:11px;display:inline-flex;align-items:center;gap:4px">${getFileIcon(f.name)} ${esc(f.name)}</span>`;
                }
            });
            fileHtml += '</div>';
        }
        d.innerHTML=`<div class="eac-bubble-avatar">👤</div><div class="eac-bubble-text">${fileHtml}${t ? esc(t) : ''}</div>`;
        msgs.appendChild(d); msgs.scrollTop=msgs.scrollHeight;
    }
    function addBotBubble(html) {
        const d=document.createElement('div'); d.className='eac-bubble bot';
        d.innerHTML=`<div class="eac-bubble-avatar">🤖</div><div class="eac-bubble-text">${md(html)}</div>`;
        msgs.appendChild(d); msgs.scrollTop=msgs.scrollHeight; return d;
    }
    function addErrorBubble(msg) {
        const d=document.createElement('div'); d.className='eac-error';
        d.innerHTML=`⚠️ ${esc(msg)}`; msgs.appendChild(d); msgs.scrollTop=msgs.scrollHeight;
    }
    function addTypingIndicator() {
        const d=document.createElement('div'); d.className='eac-bubble bot';
        d.innerHTML=`<div class="eac-bubble-avatar">🤖</div><div class="eac-bubble-text"><div class="eac-typing"><span></span><span></span><span></span></div><div class="eac-typing-label">Arsy sedang mengetik...</div></div>`;
        msgs.appendChild(d); msgs.scrollTop=msgs.scrollHeight; return d;
    }

    /* ── Saran tanya cepat inline (muncul setelah jawaban navigasi menu) ── */
    function addQuickChips(suggestions) {
        const wrap = document.createElement('div');
        wrap.className = 'eac-inline-chips';
        wrap.style.cssText = 'display:flex;flex-wrap:wrap;gap:5px;margin:2px 0 4px 36px;';
        suggestions.forEach(s => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'eac-sug-btn eac-quick-chip';
            b.dataset.msg = s.msg;
            b.textContent = s.label;
            wrap.appendChild(b);
        });
        msgs.appendChild(wrap);
        msgs.scrollTop = msgs.scrollHeight;
    }

    msgs.addEventListener('click', e => {
        const chip = e.target.closest('.eac-quick-chip');
        if (chip && !isTyping) sendMessage(chip.dataset.msg);
    });
    function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
    function md(t) {
        // Langkah 1: ekstrak semua link markdown dulu, ganti dengan placeholder
        // agar tanda kurung/kutip di URL tidak ikut di-escape
        const links = [];
        let s = t;

        // Link markdown [label](url) — internal /path atau eksternal http
        s = s.replace(/\[([^\]]+)\]\(((?:https?:\/\/|\/)[^)]+)\)/g, (_, label, url) => {
            const isInternal = url.startsWith('/') || url.includes(window.location.hostname);
            const attrs = isInternal
                ? `href="${url}" onclick="window.location.href='${url}';return false;" style="color:#6C3EAB;font-weight:600"`
                : `href="${url}" target="_blank" rel="noopener" style="color:#6C3EAB;font-weight:600"`;
            const idx = links.push(`<a ${attrs}>${label}</a>`) - 1;
            return `\x00LINK${idx}\x00`;
        });

        // Langkah 2: baru escape HTML pada teks biasa
        s = s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

        // Langkah 3: kembalikan placeholder link
        s = s.replace(/\x00LINK(\d+)\x00/g, (_, i) => links[+i]);

        // Plain URLs (belum jadi anchor)
        s = s.replace(/(?<!href=")(https?:\/\/[^\s<"&]+)/g, u=>`<a href="${u}" target="_blank" rel="noopener" style="color:#6C3EAB">${u}</a>`);

        // Headers
        s = s.replace(/^### (.+)$/gm,'<strong style="font-size:12.5px;color:#5a3d96">$1</strong>');
        s = s.replace(/^## (.+)$/gm,'<strong style="font-size:13px;color:#4F46E5">$1</strong>');
        // Bold & italic
        s = s.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>');
        s = s.replace(/\*(.+?)\*/g,'<em>$1</em>');
        // Bullet lists
        s = s.replace(/^[\-•] (.+)$/gm,'<span style="display:block;padding-left:10px">• $1</span>');
        // Numbered list
        s = s.replace(/^(\d+)\. (.+)$/gm,'<span style="display:block;padding-left:4px"><strong>$1.</strong> $2</span>');
        // Divider
        s = s.replace(/^---+$/gm,'<hr style="border:none;border-top:1px solid #e0dcf0;margin:6px 0">');
        // Newlines
        s = s.replace(/\n/g,'<br>');
        return s;
    }

    /* ══════════════════════════════════════
       REKAP SURAT
    ══════════════════════════════════════ */

    const MONTHS = ['','Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'];

    window.eacLoadRekap = function(page) {
        page = page || 1;
        const bulan = document.getElementById('eac-r-bulan').value;
        const tahun = document.getElementById('eac-r-tahun').value;
        const jenis = document.getElementById('eac-r-jenis').value;

        lastFilter = { bulan, tahun, jenis };
        currentPage = page;

        const body = document.getElementById('eac-rekap-body');
        body.innerHTML = '<div class="eac-loading"><div class="eac-loading-dots"><span></span><span></span><span></span></div></div>';
        exportBtns.classList.remove('show');

        if (bulan) {
            /* ── MODE BULANAN: detail surat ── */
            const params = new URLSearchParams({
                tipe: 'Bulan',
                tahun: tahun,
                bulan: bulan,
                ...(jenis ? { jenis } : {}),
                page: page,
            });

            fetch('/laporan/generate?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html', 'X-CSRF-TOKEN': csrf() }
            })
            .then(r => r.text())
            .then(html => {
                /* Parse partial view — ambil data dari response HTML */
                renderBulan(html, bulan, tahun, jenis, page);
            })
            .catch(() => {
                body.innerHTML = '<div class="eac-empty" style="color:#c00">⚠️ Gagal memuat data.</div>';
            });
        } else {
            /* ── MODE TAHUNAN: ringkasan per bulan ── */
            const params = new URLSearchParams({
                tipe: 'Tahun',
                tahun: tahun,
                ...(jenis ? { jenis } : {}),
            });

            fetch('/laporan/generate?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html', 'X-CSRF-TOKEN': csrf() }
            })
            .then(r => r.text())
            .then(html => {
                renderTahunan(html, tahun, jenis);
            })
            .catch(() => {
                body.innerHTML = '<div class="eac-empty" style="color:#c00">⚠️ Gagal memuat data.</div>';
            });
        }
    };

    /* Render detail bulanan — parse data dari partial */
    function renderBulan(html, bulan, tahun, jenis, page) {
        const body   = document.getElementById('eac-rekap-body');
        const parser = new DOMParser();
        const doc    = parser.parseFromString(html, 'text/html');

        /* Coba ambil dari partial blade via endpoint JSON fallback */
        fetchRekapJSON(bulan, tahun, jenis, page, (err, data) => {
            if (err || !data) {
                body.innerHTML = '<div class="eac-empty" style="color:#c00">⚠️ Gagal memuat data.</div>';
                return;
            }

            const { surat, total_masuk, total_keluar, current_page, last_page, total } = data;
            const bulanNama = MONTHS[parseInt(bulan)];
            const jenisLabel = jenis === 'masuk' ? ' — Masuk' : jenis === 'keluar' ? ' — Keluar' : '';

            let html = `<div class="eac-rekap-info">
                <span>📋 <strong>${bulanNama} ${tahun}${jenisLabel}</strong></span>
                <div class="eac-rekap-badges">`;
            if (!jenis || jenis==='masuk')  html += `<span class="eac-badge masuk">Masuk: ${total_masuk}</span>`;
            if (!jenis || jenis==='keluar') html += `<span class="eac-badge keluar">Keluar: ${total_keluar}</span>`;
            html += `</div></div>`;

            if (!surat || surat.length === 0) {
                html += `<div class="eac-empty">Tidak ada surat untuk periode ini.</div>`;
                body.innerHTML = html;
                exportBtns.classList.remove('show');
                return;
            }

            html += `<table class="eac-tbl">
                <thead><tr>
                    <th>#</th>
                    <th>No. Surat</th>
                    <th>Perihal</th>
                    <th>Tgl</th>
                    <th>Jenis</th>
                </tr></thead><tbody>`;

            const offset = (current_page - 1) * 10;
            surat.forEach((s, i) => {
                const badge = s.jenis_surat === 'Masuk'
                    ? `<span class="eac-badge masuk">Masuk</span>`
                    : `<span class="eac-badge keluar">Keluar</span>`;
                const tgl = s.tanggal_surat ? new Date(s.tanggal_surat).toLocaleDateString('id-ID',{day:'2-digit',month:'2-digit',year:'numeric'}) : '-';
                html += `<tr>
                    <td>${offset + i + 1}</td>
                    <td><a class="eac-tbl-link" href="/surat/${s.id}" onclick="window.location.href='/surat/${s.id}';return false;">${esc(s.no_surat||'-')}</a></td>
                    <td>${esc(s.perihal||'-')}</td>
                    <td style="white-space:nowrap;font-size:11px">${tgl}</td>
                    <td>${badge}</td>
                </tr>`;
            });
            html += `</tbody></table>`;

            /* Pagination */
            if (last_page > 1) {
                html += `<div class="eac-pagination">`;
                if (current_page > 1) html += `<button class="eac-pg-btn" onclick="eacLoadRekap(${current_page-1})">‹ Prev</button>`;
                for (let p = Math.max(1,current_page-2); p <= Math.min(last_page,current_page+2); p++) {
                    html += `<button class="eac-pg-btn${p===current_page?' active':''}" onclick="eacLoadRekap(${p})">${p}</button>`;
                }
                if (current_page < last_page) html += `<button class="eac-pg-btn" onclick="eacLoadRekap(${current_page+1})">Next ›</button>`;
                html += `</div>`;
            }

            body.innerHTML = html;
            exportBtns.classList.add('show');
        });
    }

    /* Render tahunan */
    function renderTahunan(html, tahun, jenis) {
        const body = document.getElementById('eac-rekap-body');

        fetchRekapTahunanJSON(tahun, jenis, (err, data) => {
            if (err || !data) {
                body.innerHTML = '<div class="eac-empty" style="color:#c00">⚠️ Gagal memuat data.</div>';
                return;
            }

            const { bulan_data, total_masuk_all, total_keluar_all } = data;
            const jenisLabel = jenis === 'masuk' ? ' — Masuk' : jenis === 'keluar' ? ' — Keluar' : '';

            let out = `<div class="eac-rekap-info">
                <span>📅 <strong>Rekap Tahunan ${tahun}${jenisLabel}</strong></span>
                <div class="eac-rekap-badges">
                    <span class="eac-badge total">Total: ${total_masuk_all + total_keluar_all}</span>
                </div>
            </div>`;

            out += `<table class="eac-tbl eac-tbl-year">
                <thead><tr>
                    <th>Bulan</th>`;
            if (!jenis || jenis==='masuk')  out += `<th>Masuk</th>`;
            if (!jenis || jenis==='keluar') out += `<th>Keluar</th>`;
            if (!jenis) out += `<th>Total</th>`;
            out += `</tr></thead><tbody>`;

            let totalM = 0, totalK = 0;
            bulan_data.forEach(row => {
                /* Filter: jika jenis spesifik, sembunyikan baris nol */
                const m = row.total_masuk || 0;
                const k = row.total_keluar || 0;
                totalM += m; totalK += k;

                out += `<tr>
                    <td><strong>${row.bulan_nama}</strong></td>`;
                if (!jenis || jenis==='masuk')  out += `<td>${m > 0 ? `<strong style="color:#2d7a4f">${m}</strong>` : '<span style="color:#ccc">0</span>'}</td>`;
                if (!jenis || jenis==='keluar') out += `<td>${k > 0 ? `<strong style="color:#856404">${k}</strong>` : '<span style="color:#ccc">0</span>'}</td>`;
                if (!jenis) out += `<td>${m+k > 0 ? m+k : '<span style="color:#ccc">0</span>'}</td>`;
                out += `</tr>`;
            });

            out += `<tr class="total-row"><td>TOTAL</td>`;
            if (!jenis || jenis==='masuk')  out += `<td>${totalM}</td>`;
            if (!jenis || jenis==='keluar') out += `<td>${totalK}</td>`;
            if (!jenis) out += `<td>${totalM + totalK}</td>`;
            out += `</tr></tbody></table>`;

            body.innerHTML = out;
            exportBtns.classList.add('show');
        });
    }

    /* ── FETCH JSON untuk rekap bulanan ── */
    function fetchRekapJSON(bulan, tahun, jenis, page, cb) {
        const params = new URLSearchParams({ bulan, tahun, ...(jenis?{jenis}:{}), page });
        fetch('/chat/rekap?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
        })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(d => cb(null, d))
        .catch(e => cb(e, null));
    }

    /* ── FETCH JSON untuk rekap tahunan ── */
    function fetchRekapTahunanJSON(tahun, jenis, cb) {
        const params = new URLSearchParams({ tahun, tipe:'Tahun', ...(jenis?{jenis}:{}) });
        fetch('/chat/rekap-tahunan?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
        })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(d => cb(null, d))
        .catch(e => cb(e, null));
    }

    /* ══ EXPORT ══ */
    window.eacExport = function(format) {
        const { bulan, tahun, jenis } = lastFilter;
        const params = new URLSearchParams({
            format,
            tipe: bulan ? 'Bulan' : 'Tahun',
            tahun,
            ...(bulan ? { bulan } : {}),
            ...(jenis ? { jenis } : {}),
        });
        window.open('/laporan/export?' + params.toString(), '_blank');
    };

    /* ══ CETAK ══ */
    window.eacCetak = function() {
        const { bulan, tahun, jenis } = lastFilter;
        const params = new URLSearchParams({
            format: 'pdf',
            tipe: bulan ? 'Bulan' : 'Tahun',
            tahun,
            ...(bulan ? { bulan } : {}),
            ...(jenis ? { jenis } : {}),
        });
        const url = '/laporan/export?' + params.toString();
        const frame = document.getElementById('eac-print-frame');
        frame.src = url;
        frame.onload = () => { try { frame.contentWindow.print(); } catch(e) { window.open(url,'_blank'); } };
    };

    /* ══════════════════════════════════════
       PERSISTENSI PERCAKAPAN ANTAR HALAMAN
       (chat tidak hilang saat pindah menu,
        hanya dibersihkan saat logout)
    ══════════════════════════════════════ */

    // Simpan ulang isi #eac-messages setiap kali berubah
    // Hanya simpan jika sudah ada bubble chat (bukan sekadar welcome message)
    try {
        const messagesObserver = new MutationObserver(() => {
            // Hanya simpan jika sudah ada pesan dari user (bukan sekadar welcome bot)
            if (msgs.querySelector('.eac-bubble.user')) {
                try { sessionStorage.setItem(MKEY, msgs.innerHTML); } catch (_) {}
            }
        });
        messagesObserver.observe(msgs, { childList: true, subtree: true, characterData: true });
    } catch (_) {}

    // Pulihkan percakapan & status panel dari halaman sebelumnya
    (function restoreChatState() {
        if (!isSameSession) return; // user baru / habis logout — biarkan tampilan default

        try {
            const savedHtml = sessionStorage.getItem(MKEY);
            // Hanya restore jika ada pesan dari user (bukan sekadar welcome bot)
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = savedHtml || '';
            const hasUserBubble = tempDiv.querySelector('.eac-bubble.user') !== null;

            if (savedHtml && savedHtml.trim() !== '' && hasUserBubble) {
                msgs.innerHTML = savedHtml;
                sugsEl.style.display = 'none'; // tanya cepat tidak tampil jika ada riwayat chat
                msgs.scrollTop = msgs.scrollHeight;
            } else {
                // Tidak ada riwayat chat dari user — bersihkan & tampilkan tanya cepat
                sessionStorage.removeItem(MKEY);
                sugsEl.style.display = 'flex';
            }
        } catch (_) {}

        try {
            if (sessionStorage.getItem(PKEY) === '1') {
                panel.classList.add('open');
                btn.style.display = 'none';
            }
        } catch (_) {}
    })();

})();
</script>




