/**
 * app.js  —  Entry point Vite (di-bundle ke public/build/assets/app-*.js)
 * ─────────────────────────────────────────────────────────────────────────
 * FILE   : resources/js/app.js
 * SCOPE  : GLOBAL — dimuat di setiap halaman via @vite(['resources/js/app.js'])
 *          yang ada di layouts/app.blade.php
 *
 * Isi file ini:
 *  1. Bootstrap JS + Flatpickr (datepicker bahasa Indonesia)
 *  2. window.debounce     — helper delay input, dipakai di user.js & surat.js
 *  3. window.safeFetch    — wrapper fetch dengan header CSRF & AJAX otomatis
 *  4. window.renderLoading — tampilkan spinner di dalam container AJAX
 *  5. window.renderError  — tampilkan alert error di dalam container AJAX
 *  6. window.AppToast     — notifikasi toast pop-up (sukses/error/info/warning)
 *
 * Semua fungsi di-expose ke `window.*` agar bisa diakses oleh modul JS lain
 * (laporan.js, user.js, surat.js, dll.) tanpa perlu import/export ES module.
 * ─────────────────────────────────────────────────────────────────────────
 */

import './bootstrap';
import flatpickr from "flatpickr";
import { Indonesian } from "flatpickr/dist/l10n/id.js";

// Lokalisasi flatpickr ke bahasa Indonesia — berlaku untuk semua input tanggal
// yang menggunakan flatpickr di seluruh aplikasi (surat, laporan, dll.)
flatpickr.localize(Indonesian);

// ── Global Utilities + Toast ───────────────────────────────────────────────
(function () {
    const CSS = `
        .app-toast{
            display:flex;align-items:flex-start;gap:12px;
            min-width:300px;max-width:380px;
            padding:14px 16px;margin-bottom:10px;
            border:0;border-radius:16px;color:#fff;
            box-shadow:0 12px 28px rgba(0,0,0,.18),0 2px 6px rgba(0,0,0,.08);
            position:relative;overflow:hidden;
            opacity:0;transform:translateX(40px) scale(.96);
            transition:opacity .45s cubic-bezier(.21,1.02,.73,1),
                       transform .45s cubic-bezier(.21,1.02,.73,1);
        }
        .app-toast.app-toast-in{opacity:1;transform:translateX(0) scale(1);}
        .app-toast.app-toast-out{opacity:0;transform:translateX(40px) scale(.96);}
        .app-toast-success{background:linear-gradient(135deg,#16a34a,#22c55e);}
        .app-toast-error{background:linear-gradient(135deg,#dc2626,#ef4444);}
        .app-toast-warning{background:linear-gradient(135deg,#d97706,#f59e0b);}
        .app-toast-info{background:linear-gradient(135deg,#2563eb,#3b82f6);}
        .app-toast-icon{
            flex-shrink:0;width:34px;height:34px;border-radius:50%;
            background:rgba(255,255,255,.22);
            display:flex;align-items:center;justify-content:center;
            font-size:17px;margin-top:1px;
        }
        .app-toast-body{flex:1;font-size:.9rem;font-weight:600;
            line-height:1.35;padding-top:4px;}
        .app-toast-close{
            flex-shrink:0;background:transparent;border:0;color:#fff;
            opacity:.85;font-size:1rem;line-height:1;padding:2px;
            margin-top:2px;cursor:pointer;
        }
        .app-toast-close:hover{opacity:1;}
        .app-toast-progress{
            position:absolute;left:0;bottom:0;height:3px;width:100%;
            background:rgba(255,255,255,.55);transform-origin:left;
            animation:appToastShrink 3.5s linear forwards;
        }
        @keyframes appToastShrink{from{transform:scaleX(1);}to{transform:scaleX(0);}}
    `;

    const style = document.createElement("style");
    style.id = "appToastStyles";
    style.textContent = CSS;
    document.head.appendChild(style);

    // ════════════════════════════════════════════════════════════
    // GLOBAL UTILITIES — dipakai oleh modul JS lain via window.*
    // ════════════════════════════════════════════════════════════

    /**
     * window.debounce
     * ─────────────────────────────────────────────────────────
     * DIPAKAI DI : user.js  → input #search (filter tabel user)
     *              surat.js → input #search (filter tabel surat)
     * FUNGSI     : tunda pemanggilan fn hingga user berhenti mengetik
     *              selama `delay` ms, agar tidak fetch di setiap keystroke
     * CONTOH     : const handler = window.debounce(() => loadTable(), 400);
     */
    window.debounce = function (fn, delay = 400) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    };

    /**
     * window.safeFetch
     * ─────────────────────────────────────────────────────────
     * DIPAKAI DI : surat.js  → semua request CRUD surat (tambah/edit/hapus)
     *              user.js   → CRUD user (tambah/edit/hapus/upload foto)
     * FUNGSI     : wrapper fetch yang otomatis menyertakan header:
     *              - X-Requested-With: XMLHttpRequest  (agar Laravel tahu ini AJAX)
     *              - X-CSRF-TOKEN     (untuk request POST/PUT/DELETE)
     *              Melempar Error jika response.ok === false
     */
    window.safeFetch = async function (url, options = {}) {
        const csrf = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") ?? "";

        const headers = {
            "X-Requested-With": "XMLHttpRequest",
            ...(options.method && options.method !== "GET"
                ? { "X-CSRF-TOKEN": csrf }
                : {}),
            ...options.headers,
        };

        const res = await fetch(url, { ...options, headers });
        if (!res.ok) {
            let msg = res.statusText;
            try {
                const body = await res.json();
                msg = body.message || msg;
            } catch (_) {}
            throw new Error(msg || `HTTP ${res.status}`);
        }
        return res;
    };

    /**
     * window.renderLoading
     * ─────────────────────────────────────────────────────────
     * DIPAKAI DI : laporan.js → #laporan_hasil_container (area hasil laporan)
     *              user.js   → #tableContainer (tabel daftar user)
     *              surat.js  → #tableContainer (tabel daftar surat)
     * FUNGSI     : inject HTML spinner Bootstrap ke dalam container AJAX
     *              agar user tahu data sedang dimuat
     */
    window.renderLoading = function (container, message = "Memuat data...") {
        if (!container) return;
        container.style.display = "block";
        container.innerHTML = `
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <p class="mt-2 text-muted">${message}</p>
            </div>`;
    };

    /**
     * window.renderError
     * ─────────────────────────────────────────────────────────
     * DIPAKAI DI : laporan.js → #laporan_hasil_container (jika fetch gagal)
     *              user.js   → #tableContainer
     *              surat.js  → #tableContainer
     * FUNGSI     : inject HTML alert-danger ke dalam container AJAX
     *              saat fetch/server mengembalikan error
     */
    window.renderError = function (container, message) {
        if (!container) return;
        container.style.display = "block";
        container.innerHTML = `
            <div class="alert alert-danger m-3 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>${message}
            </div>`;
    };

    /**
     * window.AppToast
     * ─────────────────────────────────────────────────────────
     * DIPAKAI DI : surat.js  → notif setelah tambah/edit/hapus surat
     *              user.js   → notif setelah tambah/edit/hapus user
     *              laporan.js → notif error saat download gagal
     * FUNGSI     : tampilkan toast pop-up di pojok kanan atas, auto-tutup 3.5 dtk
     * CONTOH     : window.AppToast("Berhasil disimpan!", "success")
     *              Tipe: "success" | "error" | "warning" | "info"
     */
    window.AppToast = function (message, type = "info") {
        let container = document.getElementById("customToastContainer");
        if (!container) {
            container = document.createElement("div");
            container.id = "customToastContainer";
            container.className = "position-fixed top-0 end-0 p-3 d-flex flex-column align-items-end";
            container.style.zIndex = "2000";
            document.body.appendChild(container);
        }

        const map = {
            success: ["app-toast-success", "bi-check-circle-fill"],
            error:   ["app-toast-error",   "bi-x-circle-fill"],
            warning: ["app-toast-warning", "bi-exclamation-triangle-fill"],
            info:    ["app-toast-info",    "bi-info-circle-fill"],
        };
        const [variant, icon] = map[type] ?? map.info;

        const el = document.createElement("div");
        el.className = `app-toast ${variant}`;
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        el.innerHTML = `
            <div class="app-toast-icon"><i class="bi ${icon}"></i></div>
            <div class="app-toast-body">${message}</div>
            <button type="button" class="app-toast-close" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="app-toast-progress"></div>
        `;
        container.appendChild(el);

        requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add("app-toast-in")));

        const remove = () => {
            el.classList.remove("app-toast-in");
            el.classList.add("app-toast-out");
            setTimeout(() => {
                el.remove();
                if (!container.children.length) container.remove();
            }, 450);
        };

        el.querySelector(".app-toast-close")?.addEventListener("click", remove);
        setTimeout(remove, 3500);
    };
})();
