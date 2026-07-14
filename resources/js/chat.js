/**
 * chat.js
 * ──────────────────────────────────────────────────────────
 * MENU  : Global (semua halaman — widget AI chat Arsy)
 * FILE  : resources/js/chat.js
 * SCOPE : Aktif jika #earsipchat-btn ada di DOM (lihat komponen
 *         resources/views/components/chat-widget.blade.php)
 *
 * Butuh 2 hal di halaman:
 *   1. <meta name="csrf-token" content="{{ csrf_token() }}"> di <head>
 *   2. Elemen widget (#earsipchat-btn) punya data-user & data-sid
 *      (lihat markup di chat-widget.blade.php) — dipakai untuk
 *      mendeteksi logout/login ulang supaya riwayat chat & rekap
 *      ke-reset dengan benar.
 * ──────────────────────────────────────────────────────────
 */
(function () {
    "use strict";

    /* ── Guard: hanya jalan kalau widget Arsy ada di DOM ── */
    const widget = document.getElementById("earsipchat-btn");
    if (!widget) return;

    /* ── Storage keys (percakapan bertahan saat pindah menu, hilang saat logout) ── */
    const HKEY = 'eac_history';
    const MKEY = 'eac_messages_html';
    const PKEY = 'eac_panel_open';

    /* ── Deteksi ganti user / logout — bersihkan state ──
       PENTING: sessionStorage bertahan selama TAB browser masih terbuka,
       termasuk saat logout lalu login lagi di tab yang sama. Kalau cuma
       dibandingkan user ID, login ulang dengan akun YANG SAMA akan
       dianggap "sesi yang sama" padahal sudah logout — riwayat lama jadi
       tidak ke-reset. Makanya dibandingkan juga session ID Laravel-nya,
       yang selalu berubah tiap kali sesi login baru dibuat. */
    const CURRENT_USER  = widget.dataset.user || '';
    const CURRENT_SID   = widget.dataset.sid || '';
    const STORED_USER   = sessionStorage.getItem('eac_user');
    const STORED_SID    = sessionStorage.getItem('eac_sid');
    const isSameSession = !!STORED_USER && STORED_USER === CURRENT_USER
        && !!STORED_SID && STORED_SID === CURRENT_SID;
    if (!isSameSession) {
        // User baru / habis logout-login — riwayat chat lama tidak relevan lagi
        sessionStorage.removeItem(HKEY);
        sessionStorage.removeItem(MKEY);
        sessionStorage.removeItem(PKEY);
        // Reset rekap tab secara eksplisit (mencegah DOM lama tampil via bfcache)
        const _rb = document.getElementById('eac-rekap-body');
        if (_rb) _rb.innerHTML = '<div class="eac-empty">📋 Pilih periode di atas — rekap otomatis tampil</div>';
        const _eb = document.getElementById('eac-export-btns');
        if (_eb) _eb.classList.remove('show');
    }
    sessionStorage.setItem('eac_user', CURRENT_USER);
    sessionStorage.setItem('eac_sid', CURRENT_SID);

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

    /* ── File attach — hanya PDF & Word supaya analisis akurat ── */
    const ALLOWED_FILE_EXT = ['pdf', 'doc', 'docx'];
    function isAllowedFile(f) {
        const ext = f.name.split('.').pop().toLowerCase();
        return ALLOWED_FILE_EXT.includes(ext);
    }

    attachBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
        let rejected = 0;
        Array.from(fileInput.files).forEach(f => {
            if (pendingFiles.length >= 3) return; // max 3 file
            if (!isAllowedFile(f)) { rejected++; return; }
            pendingFiles.push(f);
        });
        fileInput.value = '';
        renderFilePreviews();
        if (rejected > 0) {
            addErrorBubble(`${rejected} file dilewati karena Arsy hanya bisa menganalisis PDF atau Word (.doc/.docx).`);
        }
    });

    function renderFilePreviews() {
        filePreview.innerHTML = '';
        if (pendingFiles.length === 0) { filePreview.style.display='none'; return; }
        filePreview.style.display = 'flex';
        pendingFiles.forEach((f, i) => {
            const icon = getFileIcon(f.name);
            const chip = document.createElement('div');
            chip.className = 'eac-file-thumb';
            chip.innerHTML = `<span class="eac-file-icon">${icon}</span><span title="${esc(f.name)}">${esc(f.name)}</span><button onclick="removeFile(${i})" title="Hapus">✕</button>`;
            filePreview.appendChild(chip);
        });
    }

    window.removeFile = function(i) {
        pendingFiles.splice(i, 1);
        renderFilePreviews();
    };

    function getFileIcon(name) {
        const ext = name.split('.').pop().toLowerCase();
        const map = { pdf: '📄', doc: '📝', docx: '📝' };
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
    function openPanel() {
        cleanBackdrop();
        panel.classList.add('open');
        btn.style.display = 'none';
        try { sessionStorage.setItem(PKEY, '1'); } catch (_) {}
    }
    function closePanel() {
        panel.classList.remove('open');
        btn.style.display = 'flex';
        cleanBackdrop();
        try { sessionStorage.setItem(PKEY, '0'); } catch (_) {}
    }
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        openPanel();
        input.focus();
    });
    closeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        closePanel();
    });
    document.addEventListener('click', (e) => {
        if (panel.classList.contains('open') && !panel.contains(e.target) && !btn.contains(e.target)) closePanel();
    });

    /* ── Navigasi dari dalam chat (link "Buka Menu ..." / link surat) ──
       Chat langsung DITUTUP dulu, baru pindah halaman ke menu tujuan —
       supaya terasa seperti "membuka menu langsung", bukan sekadar
       link biasa yang membiarkan panel chat tetap menggantung terbuka. */
    window.eacNavigate = function (url) {
        try { closePanel(); } catch (_) {}
        window.location.href = url;
    };

    /* ── clear ── */
    clearBtn.addEventListener('click', () => {
        history.length = 0;
        persistHistory();
        try { sessionStorage.removeItem(MKEY); } catch (_) {}
        msgs.innerHTML = WELCOME_HTML;
        sugsEl.style.display = 'flex';
    });

    /* ══════════════════════════════════════
       GANTI TEMA (GELAP / TERANG)
       ──────────────────────────────────────
       Memakai key localStorage yang SAMA dengan tampilan.js
       ('earsip-theme') supaya tombol di chat & tombol di header
       aplikasi selalu sinkron satu sama lain.
    ══════════════════════════════════════ */
    const THEME_KEY  = 'earsip-theme';
    const themeBtn   = document.getElementById('eac-theme-btn');

    function currentTheme() {
        return document.documentElement.getAttribute('data-theme')
            || localStorage.getItem(THEME_KEY)
            || 'light';
    }

    function applyEacTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.body.classList.toggle('dark-mode', theme === 'dark');
        try { localStorage.setItem(THEME_KEY, theme); } catch (_) {}
        if (themeBtn) themeBtn.classList.toggle('is-dark', theme === 'dark');
    }

    function toggleEacTheme() {
        const next = currentTheme() === 'dark' ? 'light' : 'dark';
        applyEacTheme(next);
        return next;
    }

    // Sinkronkan ikon tombol dengan tema yang sedang aktif saat widget dimuat
    if (themeBtn) themeBtn.classList.toggle('is-dark', currentTheme() === 'dark');

    themeBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const next = toggleEacTheme();
        addBotBubble(next === 'dark'
            ? '🌙 Oke, tampilan sudah saya ubah ke **mode gelap**.'
            : '☀️ Oke, tampilan sudah saya ubah ke **mode terang**.');
    });

    // Tetap sinkron kalau tema diubah dari tombol lain di halaman (mis. header aplikasi)
    window.addEventListener('storage', (e) => {
        if (e.key === THEME_KEY && e.newValue) applyEacTheme(e.newValue);
    });

    /**
     * Deteksi perintah ganti tema lewat teks chat (mis. "ubah ke mode gelap",
     * "ganti tema terang", "dark mode dong"). Ini murni aksi client-side
     * (tidak perlu ke server) supaya tema BENAR-BENAR berubah seketika,
     * bukan cuma dijawab AI seolah-olah berubah.
     * Return true kalau pesan berhasil dikenali & ditangani di sini.
     */
    function tryHandleThemeCommand(text) {
        const msg = text.toLowerCase().trim();
        if (!msg) return false;

        const mentionsTheme = /\b(tema|theme|tampilan|mode)\b/.test(msg);
        if (!mentionsTheme) return false;

        const wantsDark  = /\b(gelap|dark|malam)\b/.test(msg);
        const wantsLight = /\b(terang|light|siang)\b/.test(msg);
        // "ganti tema" / "toggle tema" tanpa spesifik gelap/terang → toggle saja
        const wantsToggle = !wantsDark && !wantsLight && /\b(ubah|ganti|ganti\s*ke|toggle|switch)\b/.test(msg);

        if (!wantsDark && !wantsLight && !wantsToggle) return false;

        let next;
        if (wantsDark) { applyEacTheme('dark'); next = 'dark'; }
        else if (wantsLight) { applyEacTheme('light'); next = 'light'; }
        else { next = toggleEacTheme(); }

        addUserBubble(text, []);
        addBotBubble(next === 'dark'
            ? '🌙 Siap! Tampilan sudah saya ubah ke **mode gelap**. Ada lagi yang bisa saya bantu? 😊'
            : '☀️ Siap! Tampilan sudah saya ubah ke **mode terang**. Ada lagi yang bisa saya bantu? 😊');
        history.push({ role: 'user', content: text });
        history.push({ role: 'assistant', content: next === 'dark' ? 'Tampilan diubah ke mode gelap.' : 'Tampilan diubah ke mode terang.' });
        persistHistory();
        return true;
    }

    /* ── tabs ── */
    let rekapAutoLoaded = false;
    document.querySelectorAll('.eac-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.eac-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const t = tab.dataset.tab;
            document.getElementById('eac-tab-chat').style.display  = t==='chat'  ? 'flex' : 'none';
            document.getElementById('eac-tab-rekap').style.display = t==='rekap' ? 'flex' : 'none';

            // Rekap sepenuhnya AJAX — begitu tab dibuka pertama kali, langsung
            // muat data sesuai filter default (bulan & tahun berjalan) tanpa
            // perlu klik apapun lagi.
            if (t === 'rekap' && !rekapAutoLoaded) {
                rekapAutoLoaded = true;
                window.eacLoadRekap(1);
            }
        });
    });

    /* ── saran ──
       Chip menu (data-url) → LANGSUNG pindah halaman, tanpa lewat AI dulu.
       Chip lain (data-msg) → tetap dikirim sebagai pesan ke Arsy seperti biasa. */
    sugsEl.addEventListener('click', e => {
        const b = e.target.closest('.eac-sug-btn');
        if (!b) return;
        const url = b.dataset.url;
        if (url) { window.eacNavigate(url); return; }
        sendMessage(b.dataset.msg);
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

        // Perintah ganti tema ditangani langsung di sini (client-side),
        // tidak perlu ke server — supaya benar-benar berubah seketika.
        if (!hasFile && tryHandleThemeCommand(text)) {
            input.value = ''; input.style.height = 'auto';
            sugsEl.style.display = 'none';
            return;
        }

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
                // Jika AI baru saja benar-benar mengubah data (bukan cuma teks),
                // refresh tabel di halaman supaya user langsung lihat perubahannya
                // tanpa perlu reload manual.
                if (data.refresh && data.refresh.menu) {
                    refreshMenuTable(data.refresh.menu);
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

    /* ══════════════════════════════════════════════════════════
       REFRESH TABEL HALAMAN SETELAH AI MENGUBAH DATA
       ──────────────────────────────────────────────────────────
       Pola generik: setiap halaman yang datanya bisa diubah lewat
       chat cukup punya wrapper <div id="wrapper-table-<menu>"
       data-base-url="...">, mengikuti pola yang sudah dipakai di
       halaman Peserta Didik (lihat index.blade.php). Controller-nya
       cukup mendukung request AJAX (X-Requested-With) yang me-return
       partial view tabel saja — pola ini sudah ada di
       PesertaDidikController@index, tinggal dipakai ulang untuk
       menu lain (Surat, Kode, dst) saat aksi tulis-data via chat
       ditambahkan ke menu tersebut.
    ══════════════════════════════════════════════════════════ */
    function refreshMenuTable(menu) {
        const wrapper = document.getElementById('wrapper-table-' + menu);
        if (!wrapper) return; // user sedang tidak berada di halaman menu ini — tidak perlu refresh
        const url = wrapper.dataset.baseUrl || window.location.pathname;
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            credentials: 'same-origin',
        })
        .then(r => r.ok ? r.text() : Promise.reject(r.status))
        .then(html => {
            wrapper.innerHTML = html;
            // restart animasi fade-in biar user sadar tabelnya baru saja diperbarui
            wrapper.classList.remove('animate-fade-in');
            void wrapper.offsetWidth;
            wrapper.classList.add('animate-fade-in');
        })
        .catch(() => { /* diamkan — tabel tetap akan sinkron saat halaman di-refresh manual */ });
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
            if (s.url) b.dataset.url = s.url; else b.dataset.msg = s.msg;
            b.textContent = s.label;
            wrap.appendChild(b);
        });
        msgs.appendChild(wrap);
        msgs.scrollTop = msgs.scrollHeight;
    }

    msgs.addEventListener('click', e => {
        const chip = e.target.closest('.eac-quick-chip');
        if (!chip || isTyping) return;
        const url = chip.dataset.url;
        if (url) { window.eacNavigate(url); return; }
        sendMessage(chip.dataset.msg);
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
                ? `href="${url}" onclick="window.eacNavigate('${url}');return false;" style="color:#6C3EAB;font-weight:600"`
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

    const DEFAULT_BULAN = String(new Date().getMonth() + 1);
    const DEFAULT_TAHUN = String(new Date().getFullYear());
    const REKAP_EMPTY_HTML = '<div class="eac-empty">📋 Pilih periode di atas — rekap otomatis tampil</div>';

    /* ── Reset filter & tabel rekap ke kondisi awal ── */
    window.eacResetRekap = function () {
        const bulanSel = document.getElementById('eac-r-bulan');
        const tahunSel = document.getElementById('eac-r-tahun');
        const jenisSel = document.getElementById('eac-r-jenis');

        if (bulanSel) bulanSel.value = DEFAULT_BULAN;
        if (tahunSel) tahunSel.value = DEFAULT_TAHUN;
        if (jenisSel) jenisSel.value = '';

        lastFilter = {};
        currentPage = 1;

        const body = document.getElementById('eac-rekap-body');
        if (body) body.innerHTML = REKAP_EMPTY_HTML;
        exportBtns.classList.remove('show');
    };

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
            fetchRekapJSON(bulan, tahun, jenis, page, (err, data) => {
                if (err || !data) {
                    body.innerHTML = '<div class="eac-empty" style="color:#c00">⚠️ Gagal memuat data.</div>';
                    return;
                }
                renderBulan(data, bulan, tahun, jenis, page);
            });
        } else {
            fetchRekapTahunanJSON(tahun, jenis, (err, data) => {
                if (err || !data) {
                    body.innerHTML = '<div class="eac-empty" style="color:#c00">⚠️ Gagal memuat data.</div>';
                    return;
                }
                renderTahunan(data, tahun, jenis);
            });
        }
    };

    /* Render detail bulanan */
    function renderBulan(data, bulan, tahun, jenis, page) {
        const body = document.getElementById('eac-rekap-body');
        const { surat, total_masuk, total_keluar, current_page, last_page } = data;
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
                <td><a class="eac-tbl-link" href="/surat/${s.id}" onclick="window.eacNavigate('/surat/${s.id}');return false;">${esc(s.no_surat||'-')}</a></td>
                <td>${esc(s.perihal||'-')}</td>
                <td style="white-space:nowrap;font-size:11px">${tgl}</td>
                <td>${badge}</td>
            </tr>`;
        });
        html += `</tbody></table>`;

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
    }

    /* Render tahunan */
    function renderTahunan(data, tahun, jenis) {
        const body = document.getElementById('eac-rekap-body');
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
            const m = row.total_masuk || 0;
            const k = row.total_keluar || 0;
            totalM += m; totalK += k;

            out += `<tr><td><strong>${row.bulan_nama}</strong></td>`;
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
        if (!tahun) return;
        const params = new URLSearchParams({
            format,
            tipe: bulan ? 'Bulan' : 'Tahun',
            tahun,
            ...(bulan ? { bulan } : {}),
            ...(jenis ? { jenis } : {}),
        });
        window.open('/laporan/export?' + params.toString(), '_blank');
    };

    /* ══ DROPDOWN DOWNLOAD (PDF/Word) ══ */
    window.eacToggleDownloadMenu = function (e) {
        e?.stopPropagation();
        document.getElementById('eac-download-dropdown')?.classList.toggle('open');
    };
    document.addEventListener('click', (e) => {
        const dd = document.getElementById('eac-download-dropdown');
        if (dd && dd.classList.contains('open') && !dd.contains(e.target)) dd.classList.remove('open');
    });

    /* ══ CETAK ══ */
    window.eacCetak = function() {
        const { bulan, tahun, jenis } = lastFilter;
        if (!tahun) return;
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
        messagesObserver.observe(msgs, { childList: true });
    } catch (_) {}

    /* ── bfcache: reset rekap & CSRF saat halaman dipulihkan via tombol Back ── */
    window.addEventListener('pageshow', function(e) {
        if (!e.persisted) return;
        lastFilter = {};
        rekapAutoLoaded = false;
        const rekapBody = document.getElementById('eac-rekap-body');
        if (rekapBody) rekapBody.innerHTML = '<div class="eac-empty">📋 Pilih periode di atas — rekap otomatis tampil</div>';
        exportBtns.classList.remove('show');
        refreshCsrf();
    });

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