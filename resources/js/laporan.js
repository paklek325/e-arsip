/**
 * laporan.js
 * ══════════════════════════════════════════════════════════════
 * MENU   : Laporan (/laporan)
 * FILE   : resources/js/laporan.js
 *          (di-bundle Vite → public/build/assets/laporan-*.js)
 *
 * ALUR UMUM:
 *  1. Halaman pertama kali dibuka → auto-load rekap tahunan
 *  2. User ganti Tahun / Bulan   → AJAX fetch partial hasil baru
 *  3. User klik Reset            → reset form + auto-load rekap tahunan
 *  4. User klik Cetak            → window.print() tanpa tab baru
 *  5. User klik Download         → buka URL export di tab baru
 *  6. User klik link pagination  → AJAX fetch halaman berikutnya
 *  7. Browser Back/Forward       → restore state dari URL (popstate)
 *
 * ELEMEN YANG DIKENDALIKAN:
 *  #filter_form         — form filter (tahun, bulan, tipe_rekap)
 *  #tahun               — input tahun  → trigger loadLaporan() on change
 *  #bulan               — select bulan → trigger loadLaporan() on change
 *  #tipe_rekap          — hidden input, diisi otomatis oleh syncTipeFromBulan()
 *  #laporan_hasil_container — div tempat hasil laporan di-inject via AJAX
 *  #btn_print           — tombol Cetak  → handlePrint()
 *  #btn_reset_filter    — tombol Reset  → handleResetFilter()
 *  #download_group      — grup tombol Download → handleDownloadClick()
 *
 * PARTIAL VIEW (di-inject ke #laporan_hasil_container):
 *  laporan/partials/hasil.blade.php
 * ══════════════════════════════════════════════════════════════
 */

(function () {
    // ── Mapping ID elemen HTML ────────────────────────────────────────────
    // Pusatkan semua ID di sini agar tidak perlu ubah di banyak tempat
    const SELECTORS = {
        filterFormId:     "filter_form",       // <form> filter laporan
        bulanGroupId:     "bulan_group",       // wrapper select bulan
        bulanSelectId:    "bulan",             // <select> bulan
        tipeRekapId:      "tipe_rekap",        // <input hidden> tipe (Tahun/Bulan)
        tahunInputId:     "tahun",             // <input> tahun
        hasilContainerId: "laporan_hasil_container", // div hasil AJAX
        printButtonId:    "btn_print",         // tombol Cetak
        resetButtonId:    "btn_reset_filter",  // tombol Reset Filter
        tampilkanButtonId:"btn_tampilkan",     // (tidak dipakai, reset auto-load)
        downloadGroupId:  "download_group",    // grup tombol Download
    };

    // ── Shortcut getElementById ───────────────────────────────────────────
    function $id(id) { return document.getElementById(id); }

    // ── Wrapper renderLoading (dari app.js) ──────────────────────────────
    // ELEMEN: #laporan_hasil_container
    // KAPAN : saat fetch dimulai — tampilkan spinner agar user tahu loading
    function renderLoading(container) {
        window.renderLoading(container, "Memuat laporan...");
    }

    // ── Wrapper renderError (dari app.js) ────────────────────────────────
    // ELEMEN: #laporan_hasil_container
    // KAPAN : saat fetch gagal — tampilkan alert + sembunyikan tombol Cetak/Download
    function renderError(container, message) {
        window.renderError(container, message);
        const printBtn = $id(SELECTORS.printButtonId);
        const downloadGroup = $id(SELECTORS.downloadGroupId);
        if (printBtn) printBtn.style.display = "none";
        if (downloadGroup) downloadGroup.style.display = "none";
    }

    // ── Pesan awal (sebelum ada laporan dimuat) ───────────────────────────
    // ELEMEN: #laporan_hasil_container
    // KAPAN : kondisi tahun kosong atau setelah reset — sembunyikan tombol Cetak/Download
    function renderInitialMessage(container) {
        if (!container) return;
        container.style.display = "block";
        container.innerHTML =
            '<div class="text-muted text-center p-4">Silakan pilih filter untuk menampilkan laporan.</div>';

        // sembunyikan tombol print & download pada kondisi awal
        const printBtn = $id(SELECTORS.printButtonId);
        const downloadGroup = $id(SELECTORS.downloadGroupId);
        if (printBtn) printBtn.style.display = "none";
        if (downloadGroup) downloadGroup.style.display = "none";
    }

    // Convert form to query string (dipakai untuk LOAD, bukan untuk EXPORT)
    function formToQuery(form) {
        const fd = new FormData(form);
        const params = new URLSearchParams();

        // Hanya tambahkan parameter yang memiliki nilai
        for (const [key, value] of fd.entries()) {
            if (value && String(value).trim() !== "") {
                params.append(key, value);
            }
        }
        return params.toString();
    }

    /**
     * Build URL export berdasarkan URL AKTIF + format (pdf/excel/word).
     * Supaya ketika sedang lihat detail bulan (tipe=Bulan&bulan=...&jenis=...),
     * export juga ikut pakai filter yang sama.
     */
    function buildExportUrl(format) {
        const form = $id(SELECTORS.filterFormId);
        if (!form) return null;

        const base =
            form.getAttribute("data-export-url") || form.getAttribute("action");
        if (!base) return null;

        // Ambil semua query param dari URL saat ini (tipe, tahun, bulan, jenis, page, dll)
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);

        // Pastikan format diset/di-override
        params.set("format", format);

        return `${base}?${params.toString()}`;
    }

    /**
     * loadLaporan — FUNGSI UTAMA LOADER
     * ─────────────────────────────────────────────────────────
     * DIPANGGIL OLEH:
     *  - onChange #tahun              (user ganti tahun)
     *  - onChange #bulan              (user ganti bulan)
     *  - DOMContentLoaded             (auto-load pertama kali)
     *  - handleResetFilter()          (setelah reset form)
     *  - handlePopState()             (browser back/forward)
     * ALUR:
     *  1. syncTipeFromBulan() → tentukan tipe Tahun/Bulan dari nilai select #bulan
     *  2. Jika #tahun kosong  → tampilkan pesan awal, hapus query URL
     *  3. Jika #tahun ada     → build URL dari form, panggil loadUrlIntoContainer()
     */
    function loadLaporan({ pushState = true } = {}) {
        const form = $id(SELECTORS.filterFormId);
        const tipe = $id(SELECTORS.tipeRekapId);
        const container = $id(SELECTORS.hasilContainerId);

        if (!form || !container) return;

        // Sinkronkan tipe berdasarkan bulan sebelum fetch
        syncTipeFromBulan();

        // Cek kondisi: Jika tahun kosong, tampilkan pesan awal dan hentikan.
        const tahunEl = $id(SELECTORS.tahunInputId);
        if (!tahunEl || !tahunEl.value || tahunEl.value.trim() === "") {
            renderInitialMessage(container);

            // Hapus semua parameter filter terkait laporan dari URL
            const currentUrl = new URL(location.href);
            currentUrl.searchParams.delete("tipe");
            currentUrl.searchParams.delete("tahun");
            currentUrl.searchParams.delete("bulan");
            currentUrl.searchParams.delete("jenis");
            currentUrl.searchParams.delete("page");

            if (pushState) {
                // Gunakan replaceState agar tidak membuat entri histori baru saat reset
                history.replaceState(null, "", currentUrl.toString());
            }
            return;
        }

        const action = form.getAttribute("action") || window.location.pathname;
        const qs = formToQuery(form);
        const url = `${action}${qs ? "?" + qs : ""}`;

        loadUrlIntoContainer(url, { pushState });
    }

    /**
     * loadUrlIntoContainer — Fetch HTML partial & inject ke container
     * ─────────────────────────────────────────────────────────
     * ELEMEN  : #laporan_hasil_container
     * RESPONSE: HTML dari laporan/partials/hasil.blade.php
     * SETELAH BERHASIL:
     *  - tampilkan tombol #btn_print dan #download_group
     *  - pushState URL ke browser history (jika pushState=true)
     *  - re-bind delegated handler untuk link pagination/ajax baru
     */
    async function loadUrlIntoContainer(url, { pushState = true } = {}) {
        const container = $id(SELECTORS.hasilContainerId);

        if (!container) return;

        try {
            renderLoading(container); // memastikan container terlihat saat loading

            const res = await fetch(url, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "text/html",
                },
                credentials: "same-origin",
            });

            if (!res.ok) {
                if (res.status === 422) {
                    let err = await res.json().catch(() => null);
                    let msg = "Kesalahan Validasi.";
                    if (err && err.errors) {
                        const firstKey = Object.keys(err.errors)[0];
                        msg = err.errors[firstKey]
                            ? err.errors[firstKey][0]
                            : msg;
                    }
                    renderError(container, msg);
                } else {
                    const txt = await res.text().catch(() => null);
                    renderError(
                        container,
                        `Terjadi kesalahan server (${res.status}).`
                    );
                    console.error("Server error:", res.status, txt);
                }
                return;
            }

            const html = await res.text();
            container.innerHTML = html;

            // Berhasil load laporan -> tampilkan tombol print & download
            const printBtn = $id(SELECTORS.printButtonId);
            const downloadGroup = $id(SELECTORS.downloadGroupId);
            if (printBtn) printBtn.style.display = "inline-block";
            if (downloadGroup) downloadGroup.style.display = "inline-block";

            if (pushState) {
                try {
                    history.pushState({ ajax: true }, "", url);
                } catch (e) {
                    console.warn("PushState blocked by browser:", e);
                }
            }

            // re-bind delegated handlers (for new pagination/ajax links)
            attachDelegatedHandlers();
        } catch (err) {
            console.error("Fetch error:", err);
            renderError(
                container,
                "Tidak dapat terhubung ke server. Periksa koneksi Anda."
            );
        }
    }

    // ── syncTipeFromBulan ─────────────────────────────────────────────────
    // ELEMEN  : #tipe_rekap (hidden input), #bulan (select)
    // FUNGSI  : set #tipe_rekap = "Bulan" jika bulan dipilih, "Tahun" jika kosong
    //           Controller Laravel membaca #tipe_rekap untuk memilih query
    function syncTipeFromBulan() {
        const bulanSelect = $id(SELECTORS.bulanSelectId);
        const tipeInput   = $id(SELECTORS.tipeRekapId);
        if (!tipeInput) return;
        tipeInput.value = (bulanSelect && bulanSelect.value) ? "Bulan" : "Tahun";
    }

    // Alias lama agar tidak error di tempat lain yang masih memanggil toggleBulanGroup()
    function toggleBulanGroup() { syncTipeFromBulan(); }

    /**
     * handleResetFilter — Tombol "Reset Filter" (#btn_reset_filter)
     * ─────────────────────────────────────────────────────────
     * MENU    : Laporan — sidebar filter (laporan/partials/filter.blade.php)
     * TOMBOL  : #btn_reset_filter  (btn btn-outline-secondary "Reset Filter")
     * ALUR:
     *  1. Reset #tahun ke tahun berjalan (dari data-default-year)
     *  2. Kosongkan #bulan → tipe otomatis jadi "Tahun"
     *  3. Hapus query params dari URL (tanpa reload)
     *  4. Auto-load rekap tahunan default (loadLaporan)
     */
    function handleResetFilter(e) {
        e.preventDefault();

        const tipe = $id(SELECTORS.tipeRekapId);
        const tahun = $id(SELECTORS.tahunInputId);
        const bulan = $id(SELECTORS.bulanSelectId);
        const container = $id(SELECTORS.hasilContainerId);

        // 1. Reset nilai semua input filter ke kondisi awal/default
        if (tahun) {
            const defaultYear =
                tahun.getAttribute("data-default-year") ||
                new Date().getFullYear();
            tahun.value = defaultYear;
        }

        if (bulan) bulan.value = "";

        // 2. Sinkron tipe (akan jadi Tahun karena bulan dikosongkan)
        syncTipeFromBulan();

        // 3. Bersihkan parameter filter di URL (tanpa membuat history baru)
        const currentUrl = new URL(location.href);
        currentUrl.searchParams.delete("tipe");
        currentUrl.searchParams.delete("tahun");
        currentUrl.searchParams.delete("bulan");
        currentUrl.searchParams.delete("jenis");
        currentUrl.searchParams.delete("page");
        history.replaceState(null, "", currentUrl.toString());

        // 4. Auto-load rekap tahunan default (sama seperti pertama buka halaman)
        loadLaporan({ pushState: false });
    }

    /**
     * handlePrint — Tombol "Cetak" (#btn_print)
     * ─────────────────────────────────────────────────────────
     * MENU    : Laporan — header halaman (laporan/index.blade.php)
     * TOMBOL  : #btn_print  (btn btn-outline-light "Cetak")
     * ALUR:
     *  1. Tambahkan class "laporan-print-mode" ke <body>
     *  2. CSS @media print di laporan.css membaca class ini:
     *     - sembunyikan sidebar, navbar, filter card, page header
     *     - perlebar kolom hasil laporan ke 100%
     *  3. Panggil window.print() → dialog cetak browser muncul
     *  4. Setelah selesai (event afterprint) → hapus class dari <body>
     */
    function handlePrint() {
        document.body.classList.add("laporan-print-mode");
        window.addEventListener(
            "afterprint",
            function () {
                document.body.classList.remove("laporan-print-mode");
            },
            { once: true }
        );
        window.print();
    }

    /**
     * handleDownloadClick — Dropdown "Download" (#download_group)
     * ─────────────────────────────────────────────────────────
     * MENU    : Laporan — header halaman (laporan/index.blade.php)
     * ELEMEN  : #download_group → .dropdown-menu → .download-link[data-format]
     *           data-format : "pdf" | "excel" | "word"
     * ALUR:
     *  1. Tangkap klik pada .download-link
     *  2. buildExportUrl(format) → ambil semua query URL aktif + set format
     *  3. Buka URL export di tab baru (window.open)
     *  Controller: LaporanController@filter → redirect ke export sesuai format
     */
    function handleDownloadClick(e) {
        const link = e.target.closest(".download-link");
        if (!link) return;

        e.preventDefault();

        const format = link.getAttribute("data-format");
        if (!format) return;

        const url = buildExportUrl(format);
        if (!url) {
            window.AppToast("Tidak dapat membuat URL download.", "error");
            return;
        }

        window.open(url, "_blank");
    }

    /**
     * onDelegatedLinkClick — Delegated handler untuk link di dalam hasil
     * ─────────────────────────────────────────────────────────
     * ELEMEN  : #laporan_hasil_container → a.ajax-link | a di dalam .pagination
     * FUNGSI  : tangkap klik pada link pagination dan link detail bulan
     *           agar navigasi tidak full-page reload, melainkan AJAX
     * CATATAN : handler ini di-attach ke container (delegasi), bukan ke link
     *           langsung — karena konten container di-replace setiap AJAX load
     */
    function onDelegatedLinkClick(e) {
        let el = e.target.closest("a");

        if (!el) return;

        const a = el;
        const isAjaxLink = a.classList.contains("ajax-link");
        const isPaginationLink = a.closest(".pagination");

        if (!isAjaxLink && !isPaginationLink) return;

        e.preventDefault();
        const href = a.getAttribute("href");
        if (!href) return;

        loadUrlIntoContainer(href, { pushState: true });
    }

    /**
     * Attach delegated handlers to hasil container (for pagination links / ajax links)
     */
    function attachDelegatedHandlers() {
        const container = $id(SELECTORS.hasilContainerId);
        if (!container) return;

        if (!container.__hasDelegated) {
            container.addEventListener("click", onDelegatedLinkClick);
            container.__hasDelegated = true;
        }
    }

    // Helper: Update filter form values based on URL parameters (untuk back/forward)
    function updateFormFromUrl(url) {
        const currentUrl = new URL(url);
        const tipe = currentUrl.searchParams.get("tipe") || "";
        const tahun = currentUrl.searchParams.get("tahun");
        const bulan = currentUrl.searchParams.get("bulan") || "";

        const formTipe = $id(SELECTORS.tipeRekapId);
        const formTahun = $id(SELECTORS.tahunInputId);
        const formBulan = $id(SELECTORS.bulanSelectId);

        const defaultYear = formTahun
            ? formTahun.getAttribute("data-default-year") ||
              new Date().getFullYear()
            : new Date().getFullYear();

        if (formTahun) formTahun.value = tahun || defaultYear;
        if (formBulan) formBulan.value = bulan;

        syncTipeFromBulan();
    }

    /**
     * >>> FUNGSI INIT <<<
     */
    function initPage() {
        const tipe = $id(SELECTORS.tipeRekapId);
        const tahun = $id(SELECTORS.tahunInputId);
        const bulan = $id(SELECTORS.bulanSelectId);
        const printBtn = $id(SELECTORS.printButtonId);
        const resetBtn = $id(SELECTORS.resetButtonId);
        const downloadGroup = $id(SELECTORS.downloadGroupId);

        const filterChangeHandler = () => {
            syncTipeFromBulan();
            loadLaporan();
        };

        if (tahun && !tahun.__hasChange) {
            tahun.addEventListener("change", filterChangeHandler);
            tahun.__hasChange = true;
        }

        if (bulan && !bulan.__hasChange) {
            bulan.addEventListener("change", filterChangeHandler);
            bulan.__hasChange = true;
        }

        if (printBtn && !printBtn.__hasClick) {
            printBtn.addEventListener("click", handlePrint);
            printBtn.__hasClick = true;
        }

        if (resetBtn && !resetBtn.__hasClick) {
            resetBtn.addEventListener("click", handleResetFilter);
            resetBtn.__hasClick = true;
        }

        if (downloadGroup && !downloadGroup.__hasClick) {
            downloadGroup.addEventListener("click", handleDownloadClick);
            downloadGroup.__hasClick = true;
        }

        const tampilkanBtn = $id(SELECTORS.tampilkanButtonId);
        if (tampilkanBtn && !tampilkanBtn.__hasClick) {
            tampilkanBtn.addEventListener("click", () => {
                syncTipeFromBulan();
                loadLaporan();
            });
            tampilkanBtn.__hasClick = true;
        }

        attachDelegatedHandlers();
    }

    /**
     * Handle back/forward buttons (popstate).
     */
    function handlePopState(e) {
        updateFormFromUrl(location.href);

        const currentUrl = new URL(location.href);
        const tipe = currentUrl.searchParams.get("tipe");
        const container = $id(SELECTORS.hasilContainerId);
        if (!container) return;

        if (tipe) {
            loadUrlIntoContainer(location.href, { pushState: false });
        } else {
            renderInitialMessage(container);
        }
    }

    // Public init: call on DOMContentLoaded
    document.addEventListener("DOMContentLoaded", function () {
        initPage();
        window.addEventListener("popstate", handlePopState);

        const hasilContainer = $id(SELECTORS.hasilContainerId);
        if (!hasilContainer) return;

        // PERBAIKAN FLICKER: Sembunyikan kontainer secara instan
        hasilContainer.style.display = "none";
        hasilContainer.innerHTML = "";

        try {
            const currentUrl = new URL(location.href);

            if (currentUrl.searchParams.get("tipe")) {
                // Ada parameter URL → restore state dari URL
                updateFormFromUrl(location.href);
                loadUrlIntoContainer(location.href, {
                    pushState: false,
                }).finally(() => {
                    hasilContainer.style.display = "block";
                });
            } else {
                // Tidak ada parameter → auto-load rekap tahunan default
                syncTipeFromBulan();
                loadLaporan({ pushState: false });
            }
        } catch (e) {
            console.error("Error during initial load check:", e);
            hasilContainer.style.display = "block";
        }
    });

    // Reinit function for external use (e.g., if other AJAX updates the filter block)
    window.laporanReinit = function () {
        initPage();
    };
})();
