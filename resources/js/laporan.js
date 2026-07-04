/**
 * public/js/laporan.js
 * Semua logic JS untuk halaman laporan digabung di sini (vanilla JS).
 * Memicu pemuatan laporan secara otomatis (AJAX) ketika filter Tipe, Tahun, atau Bulan berubah.
 * Menangani navigasi pagination dan link detail bulanan via AJAX.
 */

(function () {
    // CONFIG: id / selector elemen di blade
    const SELECTORS = {
        filterFormId: "filter_form",
        bulanGroupId: "bulan_group",
        bulanSelectId: "bulan",
        tipeRekapId: "tipe_rekap",
        tahunInputId: "tahun",
        hasilContainerId: "laporan_hasil_container",
        printButtonId: "btn_print",
        resetButtonId: "btn_reset_filter",
        downloadGroupId: "download_group", // <-- tombol download (dropdown)
    };

    // Helper: safe querySelector by id
    function $id(id) {
        return document.getElementById(id);
    }

    // Helper: render loading spinner to container
    function renderLoading(container) {
        if (!container) return;
        container.style.display = "block";
        container.innerHTML = `
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <p class="mt-2 text-muted">Memuat laporan...</p>
            </div>
        `;
    }

    function renderError(container, message) {
        if (!container) return;
        container.style.display = "block";
        container.innerHTML = `
            <div class="alert alert-danger p-4 shadow-sm">
                <h5><i class="fas fa-exclamation-triangle"></i> Gagal Memuat Laporan</h5>
                <p class="mb-0">${message}</p>
            </div>
        `;

        // sembunyikan tombol print & download saat error
        const printBtn = $id(SELECTORS.printButtonId);
        const downloadGroup = $id(SELECTORS.downloadGroupId);
        if (printBtn) printBtn.style.display = "none";
        if (downloadGroup) downloadGroup.style.display = "none";
    }

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
     * >>> FUNGSI INTI UNTUK MEMUAT LAPORAN <<<
     * Menganalisis form dan memicu pemuatan URL.
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
     * Fetch URL (GET) and inject response HTML to container
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

    // Hitung tipe otomatis dari nilai bulan
    function syncTipeFromBulan() {
        const bulanSelect = $id(SELECTORS.bulanSelectId);
        const tipeInput   = $id(SELECTORS.tipeRekapId);
        if (!tipeInput) return;
        tipeInput.value = (bulanSelect && bulanSelect.value) ? "Bulan" : "Tahun";
    }

    // Alias lama agar tidak error di tempat lain yang masih memanggil toggleBulanGroup()
    function toggleBulanGroup() { syncTipeFromBulan(); }

    /**
     * Handle Reset Filter.
     * Reset semua input ke default, bersihkan konten laporan, dan hapus query di URL.
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

        // 3. Bersihkan kontainer dan tampilkan pesan awal
        if (container) {
            renderInitialMessage(container);
        }

        // 4. Bersihkan parameter filter di URL (tanpa membuat history baru)
        const currentUrl = new URL(location.href);
        currentUrl.searchParams.delete("tipe");
        currentUrl.searchParams.delete("tahun");
        currentUrl.searchParams.delete("bulan");
        currentUrl.searchParams.delete("jenis");
        currentUrl.searchParams.delete("page");
        history.replaceState(null, "", currentUrl.toString());
    }

    /**
     * Handle print: open hasil container html in new window and print
     */
    function handlePrint() {
        const container = $id(SELECTORS.hasilContainerId);
        if (!container) return;

        const headHtml = Array.from(
            document.querySelectorAll('link[rel="stylesheet"], style')
        )
            .map((el) => el.outerHTML)
            .join("");

        const printStyles = `
            <style>
                @page { 
                    margin: 1cm;
                }
                @media print {
                    .card-footer, .btn-view, .btn-edit, .btn-delete, .pagination, .d-flex .ajax-link { 
                        display: none !important; 
                    }

                    a[href]:after {
                        content: none !important;
                    }

                    a.btn-outline-secondary,
                    a[title*="Kembali"],
                    .ajax-link {
                        display: none !important;
                    }

                    a {
                        text-decoration: none !important;
                        color: #000 !important;
                    }

                    body { font-size: 10pt; }
                    .table-custom { font-size: 9pt; }
                    .table-responsive { overflow: visible !important; }

                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                    }
                    th, td { 
                        border: 1px solid #ccc; 
                        padding: 5px; 
                    }

                    thead { 
                        display: table-header-group;
                    }
                    tfoot {
                        display: table-footer-group;
                    }

                    tr, th, td {
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }

                    .table-primary, .table-dark { 
                        background-color: #f0f0f0 !important; 
                        -webkit-print-color-adjust: exact; 
                        color-adjust: exact; 
                        color: #000 !important; 
                    }

                    h4 {
                        page-break-after: avoid !important;
                    }
                    .card, .table { 
                        page-break-inside: avoid !important; 
                        page-break-before: auto;
                    }

                    h5 { 
                        page-break-after: avoid; 
                        page-break-before: auto;
                    }

                    .d-flex, .justify-content-between { 
                        display: block !important; 
                    } 
                }
            </style>
        `;

        const printHtml = `<!doctype html><html><head>${headHtml}${printStyles}<title>Cetak Laporan</title></head><body>${container.innerHTML}</body></html>`;
        const w = window.open("", "_blank");
        if (!w) {
            window.AppToast("Pop-up diblokir. Izinkan pop-up untuk menggunakan fitur cetak.", "warning");
            return;
        }
        w.document.open();
        w.document.write(printHtml);
        w.document.close();
        w.focus();
        setTimeout(() => {
            w.print();
        }, 300);
    }

    /**
     * Handle klik dropdown download (PDF/Excel/Word)
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
     * Delegated click handler for links inside hasil container (pagination/ajax links).
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
                updateFormFromUrl(location.href);

                loadUrlIntoContainer(location.href, {
                    pushState: false,
                }).finally(() => {
                    hasilContainer.style.display = "block";
                });
            } else {
                renderInitialMessage(hasilContainer);
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
