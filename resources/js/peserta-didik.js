/* ================================================================
 * peserta-didik.js  –  Modul manajemen peserta_didik (IIFE + namespace guard)
 *
 * Perbaikan sinkronisasi:
 *  - Filter dan pagination selalu selaras dengan URL (pushState / replaceState)
 *  - Popstate menangani back/forward
 *  - Pagination tidak mereset halaman ke 1
 *  - Tombol reset di header (#resetBtn) juga berfungsi
 *  - baseUrl diambil dari data-base-url wrapper (wajib diisi)
 * ================================================================ */
if (window.PesertaDidikApp) {
    console.warn(
        "[peserta-didik.js] PesertaDidikApp sudah terdaftar, skip re-init."
    );
} else {
    (() => {
        "use strict";

        // ── scope guard: modul ini hanya jalan kalau wrapper ada di halaman ──
        const wrapper = document.querySelector("#wrapper-table-peserta_didik");
        if (!wrapper) return;

        const $ = (sel, ctx = document) => ctx.querySelector(sel);
        const $$ = (sel, ctx = document) =>
            Array.from(ctx.querySelectorAll(sel));

        // WAJIB: set data-base-url pada wrapper di HTML
        const rawBase = wrapper.dataset.baseUrl;
        if (!rawBase) {
            console.error(
                "[peserta-didik.js] data-base-url tidak ditemukan pada wrapper. " +
                "Pastikan ada: <div id='wrapper-table-peserta_didik' data-base-url='{{ route('peserta-didik.index') }}'>"
            );
            return;
        }
        // Gunakan origin browser agar selalu cocok di hosting, abaikan APP_URL
        let baseUrl;
        try {
            const parsed = new URL(rawBase);
            baseUrl = window.location.origin + parsed.pathname.replace(/\/$/, "");
        } catch {
            baseUrl = rawBase;
        }

        const token = $('meta[name="csrf-token"]')?.getAttribute("content");

        // Baca param ?detail=ID dari URL (navigasi dari chat Arsy) — ditangkap
        // SINKRON di sini (sebelum initPesertaDidikPage membersihkan query
        // string) supaya modal detail bisa langsung dibuka otomatis.
        const _initDetailId = new URLSearchParams(window.location.search).get("detail");

        // ============================================================
        // HELPER: Format tanggal ke format Indonesia (contoh: 12 Juli 2026)
        // Menerima string tanggal dari server, format "YYYY-MM-DD" atau
        // "YYYY-MM-DD HH:mm:ss". Mengembalikan "-" bila tidak valid.
        // ============================================================
        const NAMA_BULAN_ID = [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
        ];

        function formatTanggalIndonesia(tanggal) {
            if (!tanggal) return "-";

            // Ambil bagian YYYY-MM-DD saja (buang jam bila ada)
            const match = String(tanggal).match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (!match) return "-";

            const [, tahun, bulan, hari] = match;
            const namaBulan = NAMA_BULAN_ID[parseInt(bulan, 10) - 1];
            if (!namaBulan) return "-";

            return `${parseInt(hari, 10)} ${namaBulan} ${tahun}`;
        }

        // ============================================================
        // TOAST
        // ============================================================
        function toast(message, type = "info") {
            window.AppToast(message, type);
        }

        // ============================================================
        // ALERT FORM
        // ============================================================
        function showFormAlert(selector, message) {
            const el = $(selector);
            if (!el) return;
            el.textContent = message;
            el.classList.remove("d-none");
            el.style.display = "block";
            try {
                el.scrollIntoView({ behavior: "smooth", block: "center" });
            } catch { }
        }

        function hideFormAlert(selector) {
            const el = $(selector);
            if (!el) return;
            el.textContent = "";
            el.classList.add("d-none");
            el.style.display = "none";
        }

        // ============================================================
        // FILE WARNING
        // ============================================================
        function showFileWarning(input) {
            if (!input) return;
            let warn = input.parentElement?.querySelector(".__file-warn");
            if (!warn) {
                warn = document.createElement("div");
                warn.className = "__file-warn text-danger small mt-1";
                warn.innerHTML =
                    '<i class="bi bi-exclamation-triangle-fill me-1"></i>Isi file terlebih dahulu';
                input.insertAdjacentElement("afterend", warn);
            }
            input.classList.add("is-invalid");
        }

        function hideFileWarning(input) {
            if (!input) return;
            input.parentElement?.querySelector(".__file-warn")?.remove();
            input.classList.remove("is-invalid");
        }

        // ============================================================
        // CEK DUPLIKAT
        // ============================================================
        async function cekDuplikatPesertaDidik(
            nama,
            tanggal,
            excludeId = null
        ) {
            if (!nama || !tanggal) return false;
            const params = new URLSearchParams({
                nama_peserta_didik: nama,
                tanggal_lahir: tanggal,
            });
            if (excludeId) params.append("exclude_id", excludeId);
            try {
                const res = await fetch(`${baseUrl}/cek-duplikat?${params}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                });
                const j = await res.json().catch(() => ({}));
                return j.exists === true;
            } catch {
                return false;
            }
        }

        const debounce = window.debounce;
        const tableGuard = window.createFetchGuard(); // cegah race condition search/filter tabel peserta didik

        // ============================================================
        // FILTER BAR — elemen
        // ============================================================
        const filterForm = $(".filter-bar form");
        const searchInput = $("#searchInput");
        const rombelSelect = $("#rombel");
        const statusSelect = $("#statusFilter");
        const sortAngkatanSelect = $("#sortAngkatan");
        const sortNamaSelect = $("#sortNama");
        const resetAllBtn = $("#resetAllBtn"); // tombol di dalam filter
        const resetBtnHeader = document.getElementById("resetBtn"); // tombol di header (opsional)

        // ============================================================
        // KUMPULKAN NILAI FILTER SAAT INI dari elemen (tanpa page)
        // ============================================================
        function getCurrentFilters() {
            const params = {};
            const q = searchInput?.value?.trim();
            if (q) params.search = q;
            if (rombelSelect?.value) params.rombel = rombelSelect.value;
            if (statusSelect?.value) params.status = statusSelect.value;
            if (sortAngkatanSelect?.value)
                params.sort_angkatan = sortAngkatanSelect.value;
            if (sortNamaSelect?.value) params.sort_nama = sortNamaSelect.value;
            return params;
        }

        // ============================================================
        // APPLY FILTERS → update URL + load tabel
        // ============================================================
        function applyFilters(
            pushHistory = true,
            resetPage = true,
            pageOverride = null
        ) {
            const params = getCurrentFilters();

            if (resetPage) {
                params.page = 1;
            } else if (pageOverride !== null) {
                params.page = parseInt(pageOverride);
            } else {
                // jika tidak reset dan tidak ada override, ambil page dari URL saat ini
                const currentPage = new URLSearchParams(
                    window.location.search
                ).get("page");
                if (currentPage) params.page = parseInt(currentPage);
            }

            if (pushHistory) {
                const newUrl = new URL(
                    window.location.pathname,
                    window.location.origin
                );
                newUrl.search = new URLSearchParams(params).toString();
                window.history.pushState(null, "", newUrl.href);
            }
            loadTablePesertaDidik(params);
        }

        // ============================================================
        // LOAD TABLE (AJAX)
        // ============================================================
        async function loadTablePesertaDidik(params = {}) {
            const query = new URLSearchParams(params).toString();
            const { signal, isStale } = tableGuard.start();
            try {
                wrapper.innerHTML = `<div class="text-center p-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div></div>`;
                const url = query ? `${baseUrl}?${query}` : baseUrl;
                const res = await fetch(url, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                    credentials: "same-origin",
                    signal,
                });
                if (isStale()) return; // ada pencarian/filter lebih baru menyusul

                if (!res.ok) throw new Error("Gagal memuat tabel");
                const html = await res.text();
                if (isStale()) return;

                wrapper.innerHTML = html;
                bindRowActions();
            } catch (err) {
                if (err.name === "AbortError" || isStale()) return;
                console.error(err);
                wrapper.innerHTML = `<div class="alert alert-danger">❌ Gagal memuat tabel peserta_didik.</div>`;
            }
        }

        // ============================================================
        // INISIALISASI FILTER BAR
        // ============================================================
        if (filterForm) {
            // Pencarian dengan debounce 400ms
            if (searchInput) {
                const debouncedSearch = debounce(
                    () => applyFilters(true, true),
                    400
                );
                searchInput.addEventListener("input", () => {
                    const resetSearch = document.getElementById("resetSearch");
                    resetSearch?.classList.toggle(
                        "d-none",
                        !searchInput.value.trim()
                    );
                    debouncedSearch();
                });
            }

            // Tombol X reset search
            const resetSearchBtn = document.getElementById("resetSearch");
            if (resetSearchBtn) {
                resetSearchBtn.addEventListener("click", () => {
                    if (searchInput) searchInput.value = "";
                    resetSearchBtn.classList.add("d-none");
                    applyFilters(true, true);
                    searchInput?.focus();
                });
            }

            // Dropdown — langsung apply saat berubah
            if (rombelSelect)
                rombelSelect.addEventListener("change", () =>
                    applyFilters(true, true)
                );
            if (statusSelect)
                statusSelect.addEventListener("change", () =>
                    applyFilters(true, true)
                );

            if (sortAngkatanSelect) {
                sortAngkatanSelect.addEventListener("change", () => {
                    if (sortAngkatanSelect.value && sortNamaSelect) {
                        sortNamaSelect.selectedIndex = 0;
                        syncCustomSelect(sortNamaSelect);
                    }
                    applyFilters(true, true);
                });
            }

            if (sortNamaSelect) {
                sortNamaSelect.addEventListener("change", () => {
                    if (sortNamaSelect.value && sortAngkatanSelect) {
                        sortAngkatanSelect.selectedIndex = 0;
                        syncCustomSelect(sortAngkatanSelect);
                    }
                    applyFilters(true, true);
                });
            }

            // Tombol reset per-input (×)
            $$(".reset-input-and-submit").forEach((btn) => {
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    const target = $(this.dataset.target);
                    if (!target) return;
                    if (target.tagName === "SELECT") {
                        target.selectedIndex = 0;
                        syncCustomSelect(target);
                    } else {
                        target.value = "";
                        document
                            .getElementById("resetSearch")
                            ?.classList.add("d-none");
                    }
                    applyFilters(true, true);
                });
            });

            $$(".reset-sort-and-submit").forEach((btn) => {
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    const target = $(this.dataset.target);
                    if (!target) return;
                    target.selectedIndex = 0;
                    syncCustomSelect(target);
                    applyFilters(true, true);
                });
            });

            // Tombol Reset Semua (di dalam filter)
            if (resetAllBtn) {
                resetAllBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    if (searchInput) searchInput.value = "";
                    if (rombelSelect) rombelSelect.selectedIndex = 0;
                    if (statusSelect) statusSelect.selectedIndex = 0;
                    if (sortAngkatanSelect)
                        sortAngkatanSelect.selectedIndex = 0;
                    if (sortNamaSelect) sortNamaSelect.selectedIndex = 0;
                    document
                        .getElementById("resetSearch")
                        ?.classList.add("d-none");
                    syncAllCustomSelects();
                    applyFilters(true, true);
                    searchInput?.focus();
                });
            }

            // Tombol Reset di header (#resetBtn) – jika ada
            if (resetBtnHeader) {
                resetBtnHeader.addEventListener("click", (e) => {
                    e.preventDefault();
                    if (searchInput) searchInput.value = "";
                    if (rombelSelect) rombelSelect.selectedIndex = 0;
                    if (statusSelect) statusSelect.selectedIndex = 0;
                    if (sortAngkatanSelect)
                        sortAngkatanSelect.selectedIndex = 0;
                    if (sortNamaSelect) sortNamaSelect.selectedIndex = 0;
                    document
                        .getElementById("resetSearch")
                        ?.classList.add("d-none");
                    syncAllCustomSelects();
                    applyFilters(true, true);
                    searchInput?.focus();
                });
            }

            // Cegah submit form biasa
            filterForm.addEventListener("submit", (e) => {
                e.preventDefault();
                applyFilters(true, true);
            });
        }

        // ============================================================
        // CUSTOM DROPDOWN — pengganti TAMPILAN <select> native filter
        // ------------------------------------------------------------
        // Kenapa: popup pilihan <select> native ukurannya dikendalikan
        // browser (bukan CSS kita) — di mobile grid 2x2, popup itu bisa
        // menimpa kolom filter di sebelahnya. Dengan dropdown custom,
        // lebar popup kita kendalikan sendiri (ikut lebar kolom), jadi
        // tidak menimpa apa pun.
        //
        // <select> ASLI TETAP ADA di DOM (cuma disembunyikan via CSS
        // .pd-select-native), supaya SEMUA logika filter yang sudah ada
        // di atas (getCurrentFilters, applyFilters, popstate, reset,
        // dst) tidak perlu diubah — mereka baca/tulis select.value
        // seperti biasa. Dropdown custom cuma "cermin" tampilannya.
        // ============================================================
        const FILTER_SELECTS = [
            rombelSelect,
            statusSelect,
            sortAngkatanSelect,
            sortNamaSelect,
        ].filter(Boolean);

        // Select di dalam modal Tambah/Edit (Jenis Kelamin, Rombel) — bukan
        // bagian dari filter, tapi butuh perlakuan sama: dropdown native di
        // sini juga overlap di mobile (lihat catatan CSS di peserta-didik.css,
        // section "MODAL TAMBAH/EDIT"). Dipakaikan custom dropdown yang sama
        // persis mekanismenya dengan FILTER_SELECTS di atas.
        const MODAL_SELECTS = [
            $("#add_jenis_kelamin"),
            $("#add_rombel"),
            $("#edit_jenis_kelamin"),
            $("#edit_rombel"),
        ].filter(Boolean);
        const ALL_CUSTOM_SELECTS = [...FILTER_SELECTS, ...MODAL_SELECTS];

        const customSelectMap = new Map(); // select -> { trigger, panel }

        function buildCustomSelect(select) {
            if (!select || customSelectMap.has(select)) return;
            const wrap = select.parentElement; // .position-relative
            if (!wrap) return;

            const trigger = document.createElement("div");
            trigger.className = select.className + " pd-select-trigger";
            trigger.setAttribute("role", "button");
            trigger.setAttribute("tabindex", "0");
            wrap.insertBefore(trigger, select);

            select.classList.add("pd-select-native");

            const panel = document.createElement("div");
            panel.className = "pd-select-panel";
            Array.from(select.options).forEach((opt) => {
                const item = document.createElement("div");
                item.className = "pd-select-option";
                item.dataset.value = opt.value;
                item.textContent = opt.textContent;
                item.addEventListener("click", () => {
                    select.value = opt.value;
                    updateTriggerLabel(select);
                    closeAllCustomSelects();
                    // Native change event → semua listener filter yang
                    // sudah ada (apply, cross-reset sort, dst) tetap jalan.
                    select.dispatchEvent(new Event("change", { bubbles: true }));
                });
                panel.appendChild(item);
            });
            wrap.appendChild(panel);

            trigger.addEventListener("click", (e) => {
                e.stopPropagation();
                const isOpen = panel.classList.contains("show");
                closeAllCustomSelects();
                if (!isOpen) {
                    panel.classList.add("show");
                    trigger.classList.add("active");
                }
            });
            trigger.addEventListener("keydown", (e) => {
                if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    trigger.click();
                } else if (e.key === "Escape") {
                    closeAllCustomSelects();
                }
            });

            customSelectMap.set(select, { trigger, panel });
            updateTriggerLabel(select);
        }

        function updateTriggerLabel(select) {
            const entry = customSelectMap.get(select);
            if (!entry) return;
            const opt = select.options[select.selectedIndex];
            entry.trigger.textContent = opt ? opt.textContent : "";
            entry.panel.querySelectorAll(".pd-select-option").forEach((item) => {
                item.classList.toggle(
                    "is-selected",
                    item.dataset.value === select.value
                );
            });
        }

        function closeAllCustomSelects() {
            customSelectMap.forEach(({ trigger, panel }) => {
                panel.classList.remove("show");
                trigger.classList.remove("active");
            });
        }

        // Dipanggil setiap kali select.value diubah SECARA PROGRAMATIK
        // (bukan lewat klik opsi custom) — mis. tombol reset, popstate,
        // init halaman — supaya label trigger ikut ter-update.
        function syncCustomSelect(select) {
            if (select && customSelectMap.has(select)) updateTriggerLabel(select);
        }
        function syncAllCustomSelects() {
            ALL_CUSTOM_SELECTS.forEach(syncCustomSelect);
        }

        document.addEventListener("click", closeAllCustomSelects);
        ALL_CUSTOM_SELECTS.forEach(buildCustomSelect);

        // ============================================================
        // POPSTATE — sinkronisasi saat back/forward
        // ============================================================
        window.addEventListener("popstate", () => {
            const params = new URLSearchParams(window.location.search);
            // Set nilai elemen sesuai URL
            if (searchInput) searchInput.value = params.get("search") || "";
            if (rombelSelect) rombelSelect.value = params.get("rombel") || "";
            if (statusSelect) statusSelect.value = params.get("status") || "";
            if (sortAngkatanSelect)
                sortAngkatanSelect.value = params.get("sort_angkatan") || "";
            if (sortNamaSelect)
                sortNamaSelect.value = params.get("sort_nama") || "";
            syncAllCustomSelects();

            // Tampilkan/sembunyikan tombol reset search
            const resetSearch = document.getElementById("resetSearch");
            if (resetSearch) {
                resetSearch.classList.toggle(
                    "d-none",
                    !searchInput?.value?.trim()
                );
            }

            // Ambil filter + page dari URL
            const filters = getCurrentFilters();
            const page = params.get("page");
            if (page) filters.page = parseInt(page);
            loadTablePesertaDidik(filters);
        });

        // ============================================================
        // (Sisanya: download, edit, detail, tambah, update, hapus, dll.)
        // ============================================================
        // ... [semua fungsi download, preview, edit, detail, tambah, update, hapus, refreshEditDownloadAll, bindRowActions, dll. tetap sama seperti di kode asli Anda] ...
        // Saya akan salin dari kode sebelumnya (karena tidak ada perubahan di bagian ini).
        // ============================================================

        // ============================================================
        // DOWNLOAD ALL FILES (ZIP)
        // ============================================================
        function downloadAllFiles(id) {
            if (!id) return toast("ID peserta_didik tidak valid.", "error");
            const a = document.createElement("a");
            a.href = `${baseUrl}/${id}/download-all`;
            a.download = "";
            document.body.appendChild(a);
            a.click();
            a.remove();
            toast("Memulai unduhan semua file...", "info");
        }

        // ============================================================
        // DOWNLOAD SELECTED FILES (ZIP)
        // ============================================================
        function downloadSelectedFiles() {
            const modalEl = $("#modalDetailPesertaDidik");
            const id = modalEl?.dataset.pesertaDidikId;
            if (!id) return toast("ID peserta_didik tidak valid.", "error");

            const selected = $$(".file-select-checkbox:checked")
                .map((cb) => cb.dataset.field)
                .filter(Boolean);

            if (!selected.length)
                return toast("Pilih minimal satu file.", "error");

            const params = new URLSearchParams();
            params.append("fields", selected.join(","));

            const a = document.createElement("a");
            a.href = `${baseUrl}/${id}/download-selected?${params}`;
            a.download = "";
            document.body.appendChild(a);
            a.click();
            a.remove();
            toast("Memulai unduhan file terpilih...", "info");
        }

        // ============================================================
        // DOWNLOAD SINGLE FILE
        // ============================================================
        function openDownload(id, field) {
            const a = document.createElement("a");
            a.href = `${baseUrl}/${id}/download/${field}?download=true`;
            a.download = "";
            document.body.appendChild(a);
            a.click();
            a.remove();
        }

        // ============================================================
        // PREVIEW FILE
        // ============================================================
        async function previewFile(id, field) {
            try {
                const res = await fetch(`${baseUrl}/${id}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                });
                const j = await res.json().catch(() => ({}));
                const fileUrl = j?.data?.[`${field}_url`];
                if (!j?.success || !fileUrl)
                    return toast("File tidak ditemukan.", "error");

                const modalEl = $("#modalPreviewFile");
                const body = $("#pd-preview-container");
                const spinner = $("#pd-file-loading-spinner");
                const dlLink = $("#pd_previewDownloadLink");
                const label = $("#pd_preview_filename_label");

                // Reset
                if (body) body.innerHTML = "";
                if (spinner) spinner.style.display = "flex";

                // Download link
                if (dlLink) {
                    dlLink.href = `${baseUrl}/${id}/download/${field}?download=true`;
                    dlLink.removeAttribute("download");
                }
                if (label) {
                    label.textContent = field
                        .replace("file_", "")
                        .replace(/_/g, " ")
                        .replace(/\b\w/g, (c) => c.toUpperCase());
                }

                modalEl.dataset.pesertaDidikId = id;
                modalEl.dataset.field = field;

                const ext = fileUrl
                    .split("?")[0]
                    .split(".")
                    .pop()
                    .toLowerCase();

                let timerId;
                const hideSpinner = () => {
                    clearTimeout(timerId);
                    if (spinner) spinner.style.display = "none";
                };
                timerId = setTimeout(hideSpinner, 12000);

                let content;

                if (ext === "pdf") {
                    content = document.createElement("div");
                    content.style.cssText = "width:100%;height:80vh;";
                    content.innerHTML = `
                        <object data="${fileUrl}" type="application/pdf"
                                style="width:100%;height:100%;border:none;">
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
                                <p class="mt-2">Browser tidak mendukung pratinjau PDF.</p>
                            </div>
                        </object>
                        <div class="text-center py-2 border-top bg-light">
                            <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Buka di Tab Baru
                            </a>
                        </div>`;
                    setTimeout(hideSpinner, 2000);
                } else if (
                    ["jpg", "jpeg", "png", "gif", "webp"].includes(ext)
                ) {
                    content = document.createElement("img");
                    content.className = "img-fluid mx-auto d-block";
                    content.style.maxHeight = "75vh";
                    content.onload = hideSpinner;
                    content.onerror = () => {
                        hideSpinner();
                        if (body)
                            body.innerHTML = `
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-image fs-1"></i>
                                <p class="mt-2">Gambar tidak dapat ditampilkan.</p>
                                <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-primary mt-1">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Buka di Tab Baru
                                </a>
                            </div>`;
                    };
                    content.src = fileUrl;
                } else {
                    hideSpinner();
                    content = document.createElement("div");
                    content.className = "p-4 text-center";
                    content.innerHTML = `
                        <i class="bi bi-file-earmark-word fs-1 text-primary"></i>
                        <p class="mt-2 text-muted">Pratinjau tidak tersedia untuk format <strong>.${ext}</strong>.</p>
                        <a href="${fileUrl}" target="_blank" class="btn btn-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Buka / Download File
                        </a>`;
                }

                if (body) body.appendChild(content);
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } catch (err) {
                console.error(err);
                toast("Gagal memuat file.", "error");
            }
        }

        // ============================================================
        // REFRESH EDIT DOWNLOAD ALL
        // ============================================================
        function refreshEditDownloadAll() {
            const id = $("#edit_id")?.value || "";
            const fileFields = [
                "file_kk",
                "file_akte",
                "file_ktp",
                "file_ijazah_smp",
                "file_kip",
            ];

            let total = 0;

            fileFields.forEach((field) => {
                const check = $(`#edit_check_${field}`);
                const input = $(`#edit_${field}`);
                const parent = input?.parentElement;
                if (!check || !input || !parent) return;

                const serverFile = input.dataset?.serverFile === "1";
                const markedDelete = input.dataset?.markDelete === "1";
                const hasNewFile = input.files && input.files.length > 0;

                if (serverFile && !markedDelete) total++;
                if (hasNewFile) total++;

                // Wrapper tombol download
                let existingWrapper = parent.querySelector(
                    ".__dynamic-download-wrapper"
                );
                if (serverFile && !markedDelete) {
                    if (!existingWrapper) {
                        const wrap = document.createElement("div");
                        wrap.className = "__dynamic-download-wrapper";
                        wrap.innerHTML = `<div class="d-grid gap-2 mt-2">
                        <div class="btn btn-sm btn-outline-secondary w-100 btn-download-single" role="button">
                            <i class="bi bi-download"></i> Download File
                        </div></div>`;
                        parent.appendChild(wrap);
                        wrap.querySelector(".btn-download-single").onclick =
                            () => openDownload(id, field);
                    }
                } else {
                    existingWrapper?.remove();
                }

                // Tombol ganti file
                let existingGanti = parent.querySelector(".btn-ganti-single");
                if (serverFile && !markedDelete) {
                    if (!existingGanti) {
                        const gbt = document.createElement("button");
                        gbt.type = "button";
                        gbt.className =
                            "btn btn-sm btn-outline-primary mt-2 w-100 btn-ganti-single";
                        gbt.innerHTML = `<i class="bi bi-pencil"></i> Ganti File`;
                        gbt.onclick = () => {
                            input.classList.remove("d-none");
                            input.disabled = false;
                            input.dataset.replace = "1";
                            input.dataset.markDelete = "0";
                            check.checked = true;
                            refreshEditDownloadAll();
                        };
                        parent.appendChild(gbt);
                    }
                } else {
                    existingGanti?.remove();
                }

                let badge = parent.querySelector(".file-new-badge");
                if (hasNewFile) {
                    if (!badge) {
                        const b = document.createElement("div");
                        b.className = "small text-success file-new-badge mt-2";
                        b.textContent =
                            "✓ File siap diunggah (akan menggantikan file lama saat submit)";
                        parent.appendChild(b);
                    }
                } else {
                    badge?.remove();
                }

                let delNote = parent.querySelector(".file-delete-note");
                if (input.dataset.markDelete === "1") {
                    if (!delNote) {
                        const n = document.createElement("div");
                        n.className = "small text-danger file-delete-note mt-2";
                        n.textContent =
                            "⚠️ File lama akan dihapus saat menyimpan perubahan.";
                        parent.appendChild(n);
                    }
                } else {
                    delNote?.remove();
                }
            });

            const downloadAllContainer = $("#edit-download-all-container");
            if (!downloadAllContainer) return;
            downloadAllContainer.innerHTML =
                fileFields.length && total > 0
                    ? `<button type="button" class="btn btn-success w-100" id="__pesertaDidikEditDlAll">
                <i class="bi bi-file-earmark-zip"></i> Unduh Semua (${total} File)
               </button>`
                    : `<button type="button" class="btn btn-secondary w-100" disabled>
                <i class="bi bi-file-earmark-zip"></i> Tidak Ada File Untuk Diunduh
               </button>`;

            document
                .getElementById("__pesertaDidikEditDlAll")
                ?.addEventListener("click", () =>
                    downloadAllFiles($("#edit_id")?.value || "")
                );
        }

        // ============================================================
        // EDIT SISWA
        // ============================================================
        async function editPesertaDidik(id) {
            if (!id) return toast("ID peserta_didik tidak valid.", "error");
            try {
                const res = await fetch(`${baseUrl}/${id}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                });
                const j = await res.json().catch(() => ({}));
                if (!j?.success || !j?.data)
                    return toast("Gagal memuat data peserta_didik", "error");

                const s = j.data;
                const fileFields = [
                    "file_kk",
                    "file_akte",
                    "file_ktp",
                    "file_ijazah_smp",
                    "file_kip",
                ];

                const pesertaDidikId = s.id_peserta_didik || id;
                const idEl = $("#edit_id");
                if (idEl) idEl.value = pesertaDidikId ?? "";

                hideFormAlert("#alertEditPesertaDidik");

                [
                    "nama_peserta_didik",
                    "alamat",
                    "tahun_angkatan",
                    "tempat_lahir",
                    "tanggal_lahir",
                    "jenis_kelamin",
                    "rombel",
                ].forEach((f) => {
                    const el = $(`#edit_${f}`);
                    if (el) el.value = s[f] ?? "";
                });
                // Select custom (Jenis Kelamin, Rombel) butuh sync manual
                // karena diisi programatik (bukan lewat klik opsi custom).
                syncCustomSelect($("#edit_jenis_kelamin"));
                syncCustomSelect($("#edit_rombel"));

                fileFields.forEach((field) => {
                    const check = $(`#edit_check_${field}`);
                    const input = $(`#edit_${field}`);
                    const parent = input?.parentElement;
                    if (!check || !input || !parent) return;

                    $$(
                        ".__dynamic-download-wrapper,.btn-ganti-single,.file-new-badge,.file-delete-note",
                        parent
                    ).forEach((n) => n.remove());

                    const url = s[`${field}_url`] || null;
                    input.dataset.serverFile = url ? "1" : "0";
                    input.dataset.markDelete = "0";
                    input.dataset.replace = "0";
                    try {
                        input.value = "";
                    } catch { }
                    input.classList.add("d-none");
                    input.disabled = true;
                    check.checked = !!url;

                    check.onchange = () => {
                        if (check.checked) {
                            input.dataset.markDelete = "0";
                            if (input.dataset.serverFile !== "1") {
                                input.classList.remove("d-none");
                                input.disabled = false;
                                showFileWarning(input);
                            } else {
                                input.classList.add("d-none");
                                input.disabled = true;
                                hideFileWarning(input);
                            }
                        } else {
                            hideFileWarning(input);
                            if (input.dataset.serverFile === "1")
                                input.dataset.markDelete = "1";
                            else input.dataset.markDelete = "0";
                            input.dataset.replace = "0";
                            input.classList.add("d-none");
                            try {
                                input.value = "";
                            } catch { }
                            input.disabled = true;
                        }
                        refreshEditDownloadAll();
                    };

                    input.onchange = () => {
                        if (input.files && input.files.length > 0) {
                            input.dataset.replace = "1";
                            input.dataset.markDelete = "0";
                            check.checked = true;
                            hideFileWarning(input);
                        } else {
                            input.dataset.replace = "0";
                            if (
                                check.checked &&
                                input.dataset.serverFile !== "1"
                            )
                                showFileWarning(input);
                        }
                        refreshEditDownloadAll();
                    };
                });

                refreshEditDownloadAll();

                const modalEl = $("#modalEditPesertaDidik");
                bootstrap.Modal.getOrCreateInstance(modalEl).show();

                modalEl.addEventListener(
                    "hidden.bs.modal",
                    () => {
                        $$(".file-new-badge,.file-delete-note").forEach((n) =>
                            n.remove()
                        );
                    },
                    { once: true }
                );
            } catch (err) {
                console.error(err);
                toast(
                    "Terjadi kesalahan saat memuat data peserta_didik",
                    "error"
                );
            }
        }

        // ============================================================
        // DETAIL SISWA
        // ============================================================
        async function detailPesertaDidik(id) {
            if (!id) return toast("ID peserta_didik tidak valid.", "error");
            try {
                const res = await fetch(`${baseUrl}/${id}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                });
                const j = await res.json().catch(() => ({}));
                if (!j?.success || !j?.data)
                    return toast("Gagal memuat detail peserta_didik", "error");

                const s = j.data;
                let fileCount = 0;

                [
                    "nama_peserta_didik",
                    "jenis_kelamin",
                    "tahun_angkatan",
                    "tempat_lahir",
                    "rombel",
                    "alamat",
                ].forEach((f) => {
                    const el = $(`#detail_${f}`);
                    if (el) el.textContent = s[f] || "-";
                });

                const tglLahirEl = $("#detail_tanggal_lahir");
                if (tglLahirEl) {
                    tglLahirEl.textContent = formatTanggalIndonesia(
                        s.tanggal_lahir
                    );
                }

                const fileFields = [
                    "file_kk",
                    "file_akte",
                    "file_ktp",
                    "file_ijazah_smp",
                    "file_kip",
                ];

                fileFields.forEach((field) => {
                    const btn = $(`#detail_${field}`);
                    const none = $(`#detail_${field}_none`);
                    const checkbox = $(`#detail_select_${field}`);
                    const url = s[`${field}_url`];

                    if (btn && none) {
                        if (url) {
                            fileCount++;
                            btn.classList.remove("d-none");
                            none.classList.add("d-none");
                            none.classList.remove("d-block");
                            btn.innerHTML = `
                          <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-sm btn-info text-white" data-peserta-didik-preview="${id}" data-field="${field}">
                              <i class="bi bi-eye"></i> Lihat
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" data-peserta-didik-download="${id}" data-field="${field}">
                              <i class="bi bi-download"></i> Unduh
                            </button>
                          </div>`;
                            const previewBtn = btn.querySelector(
                                `[data-peserta-didik-preview]`
                            );
                            const downloadBtn = btn.querySelector(
                                `[data-peserta-didik-download]`
                            );
                            if (previewBtn)
                                previewBtn.onclick = () =>
                                    previewFile(id, field);
                            if (downloadBtn)
                                downloadBtn.onclick = () =>
                                    openDownload(id, field);
                            if (checkbox) {
                                checkbox.disabled = false;
                                checkbox.checked = false;
                                checkbox
                                    .closest(".form-check")
                                    ?.classList.remove("d-none");
                            }
                        } else {
                            btn.classList.add("d-none");
                            none.classList.remove("d-none");
                            none.classList.add("d-block");
                            none.innerHTML = `<span class="text-danger fw-semibold">Belum Ada File</span>`;
                            if (checkbox) {
                                checkbox.checked = false;
                                checkbox.disabled = true;
                                checkbox
                                    .closest(".form-check")
                                    ?.classList.add("d-none");
                            }
                        }
                    }
                });

                const dlAllContainer = $("#detail-download-all-container");
                if (dlAllContainer) {
                    dlAllContainer.innerHTML =
                        fileCount > 0
                            ? `<button type="button" class="btn btn-success w-100" id="__pesertaDidikDetailDlAll">
                        <i class="bi bi-file-earmark-zip"></i> Unduh Semua (${fileCount} File)
                       </button>`
                            : `<button type="button" class="btn btn-secondary w-100" disabled>
                        <i class="bi bi-file-earmark-zip"></i> Tidak Ada File Untuk Diunduh
                       </button>`;
                    document
                        .getElementById("__pesertaDidikDetailDlAll")
                        ?.addEventListener("click", () => downloadAllFiles(id));
                }

                const dlSelContainer = $("#detail-download-selected-container");
                if (dlSelContainer) {
                    dlSelContainer.innerHTML =
                        fileCount > 0
                            ? `<button type="button" class="btn btn-warning w-100" id="__pesertaDidikDetailDlSel">
                        <i class="bi bi-file-earmark-zip"></i> Unduh Terpilih (Centang di bawah)
                       </button>`
                            : `<button type="button" class="btn btn-secondary w-100" disabled>
                        <i class="bi bi-file-earmark-zip"></i> Tidak Ada File Untuk Diunduh
                       </button>`;
                    document
                        .getElementById("__pesertaDidikDetailDlSel")
                        ?.addEventListener("click", () =>
                            downloadSelectedFiles()
                        );
                }

                const modalEl = $("#modalDetailPesertaDidik");
                if (modalEl) {
                    modalEl.dataset.pesertaDidikId = id;
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            } catch (err) {
                console.error(err);
                toast(
                    "Terjadi kesalahan saat memuat detail peserta_didik",
                    "error"
                );
            }
        }

        // ============================================================
        // TAMBAH SISWA
        // ============================================================
        const modalTambah = $("#modalTambahPesertaDidik");
        const formTambah = $("#formTambahPesertaDidik");

        if (modalTambah && formTambah) {
            modalTambah.addEventListener("show.bs.modal", () => {
                formTambah.reset();
                hideFormAlert("#alertTambahPesertaDidik");
                // Select custom (Jenis Kelamin, Rombel) ikut direset labelnya
                syncCustomSelect($("#add_jenis_kelamin"));
                syncCustomSelect($("#add_rombel"));
                $$("input[type=file]", formTambah).forEach((f) => {
                    f.value = "";
                    f.disabled = true;
                });
                $$(".file-input", formTambah).forEach((i) =>
                    i.classList.add("d-none")
                );
                $$(".file-check", formTambah).forEach(
                    (c) => (c.checked = false)
                );
            });

            formTambah.addEventListener("submit", async (e) => {
                e.preventDefault();
                hideFormAlert("#alertTambahPesertaDidik");

                const fieldLabels = {
                    file_kk: "Kartu Keluarga",
                    file_ktp: "KTP Orang Tua",
                    file_akte: "Akte Kelahiran",
                    file_ijazah_smp: "Ijazah SMP/MTs",
                    file_kip: "Kartu Indonesia Pintar (KIP)",
                };
                let firstInvalid = null;
                for (const [field] of Object.entries(fieldLabels)) {
                    const check = $(`#add_check_${field}`);
                    const input = $(`#add_${field}`);
                    if (
                        check?.checked &&
                        (!input?.files || input.files.length === 0)
                    ) {
                        showFileWarning(input);
                        if (!firstInvalid) firstInvalid = input;
                    }
                }
                if (firstInvalid) {
                    showFormAlert(
                        "#alertTambahPesertaDidik",
                        "❌ Beberapa file yang dicentang belum diisi."
                    );
                    firstInvalid.closest(".file-item")?.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                    return;
                }

                const nama = $("#add_nama_peserta_didik")?.value?.trim();
                const tanggal = $("#add_tanggal_lahir")?.value;
                const isDup = await cekDuplikatPesertaDidik(nama, tanggal);

                if (isDup) {
                    showFormAlert(
                        "#alertTambahPesertaDidik",
                        "❌ Data peserta_didik dengan nama dan tanggal lahir tersebut sudah ada."
                    );
                    return;
                }

                try {
                    const res = await fetch(baseUrl, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": token,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: new FormData(e.target),
                    });

                    let j = {};
                    try { j = await res.json(); } catch (_) { }

                    if (j.success) {
                        toast(j.message || "✅ Peserta didik berhasil ditambahkan", "success");
                        bootstrap.Modal.getInstance(modalTambah)?.hide();
                        await loadTablePesertaDidik(getCurrentFilters());
                    } else if (res.status === 422 && j.errors) {
                        const firstError = Object.values(j.errors).flat()[0];
                        showFormAlert("#alertTambahPesertaDidik", "❌ " + (firstError || j.message || "Validasi gagal."));
                    } else if (res.status === 419) {
                        showFormAlert("#alertTambahPesertaDidik", "❌ Sesi CSRF kedaluwarsa. Silakan refresh halaman lalu coba lagi.");
                    } else {
                        const msg = j.message || `❌ Gagal menyimpan data (HTTP ${res.status}).`;
                        showFormAlert("#alertTambahPesertaDidik", msg);
                    }
                } catch (err) {
                    console.error(err);
                    toast("Terjadi kesalahan jaringan saat menyimpan.", "error");
                }
            });
        }

        // ============================================================
        // UPDATE SISWA
        // ============================================================
        const formEdit = $("#formEditPesertaDidik");
        if (formEdit) {
            formEdit.addEventListener("submit", async (e) => {
                e.preventDefault();
                hideFormAlert("#alertEditPesertaDidik");

                const idInput = $("#edit_id");
                const id = idInput?.value;
                if (!id)
                    return toast("ID peserta_didik tidak ditemukan.", "error");

                const fieldLabels = {
                    file_kk: "Kartu Keluarga",
                    file_ktp: "KTP Orang Tua",
                    file_akte: "Akte Kelahiran",
                    file_ijazah_smp: "Ijazah SMP/MTs",
                    file_kip: "Kartu Indonesia Pintar (KIP)",
                };
                let firstInvalidEdit = null;
                for (const [field] of Object.entries(fieldLabels)) {
                    const check = $(`#edit_check_${field}`);
                    const input = $(`#edit_${field}`);
                    if (
                        check?.checked &&
                        input?.dataset.serverFile !== "1" &&
                        (!input?.files || input.files.length === 0)
                    ) {
                        showFileWarning(input);
                        if (!firstInvalidEdit) firstInvalidEdit = input;
                    }
                }
                if (firstInvalidEdit) {
                    showFormAlert(
                        "#alertEditPesertaDidik",
                        "❌ Beberapa file yang dicentang belum diisi."
                    );
                    firstInvalidEdit.closest(".file-item")?.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                    return;
                }

                const nama = $("#edit_nama_peserta_didik")?.value?.trim();
                const tanggal = $("#edit_tanggal_lahir")?.value;
                const isDup = await cekDuplikatPesertaDidik(nama, tanggal, id);

                if (isDup) {
                    showFormAlert(
                        "#alertEditPesertaDidik",
                        "❌ Data peserta_didik dengan nama dan tanggal lahir tersebut sudah ada."
                    );
                    return;
                }

                if (idInput) idInput.disabled = true;

                const formData = new FormData(formEdit);
                formData.append("_method", "PUT");

                [
                    "file_kk",
                    "file_akte",
                    "file_ktp",
                    "file_ijazah_smp",
                    "file_kip",
                ].forEach((field) => {
                    const input = $(`#edit_${field}`);
                    if (input?.dataset.markDelete === "1")
                        formData.append(`${field}_delete`, "1");
                });

                try {
                    const res = await fetch(`${baseUrl}/${id}`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": token,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: formData,
                    });

                    let j = {};
                    try { j = await res.json(); } catch (_) { }

                    if (j.success) {
                        toast(j.message || "✅ Data diperbarui", "success");
                        bootstrap.Modal.getInstance($("#modalEditPesertaDidik"))?.hide();
                        await loadTablePesertaDidik(getCurrentFilters());
                    } else if (res.status === 422 && j.errors) {
                        const firstError = Object.values(j.errors).flat()[0];
                        showFormAlert("#alertEditPesertaDidik", "❌ " + (firstError || j.message || "Validasi gagal."));
                    } else if (res.status === 419) {
                        showFormAlert("#alertEditPesertaDidik", "❌ Sesi CSRF kedaluwarsa. Silakan refresh halaman lalu coba lagi.");
                    } else {
                        const msg = j.message || `❌ Gagal memperbarui data (HTTP ${res.status}).`;
                        showFormAlert("#alertEditPesertaDidik", msg);
                    }
                } catch (err) {
                    console.error(err);
                    toast("Terjadi kesalahan jaringan saat update.", "error");
                } finally {
                    if (idInput) idInput.disabled = false;
                }
            });
        }

        // ============================================================
        // HAPUS SISWA
        // ============================================================
        $("#formHapusPesertaDidik")?.addEventListener("submit", async (e) => {
            e.preventDefault();
            const id = $("#hapus_id")?.value;
            if (!id) return toast("ID tidak ditemukan.", "error");

            try {
                const res = await fetch(`${baseUrl}/${id}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": token,
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });
                let j = {};
                try { j = await res.json(); } catch (_) { }

                if (j.success) {
                    toast(j.message || "✅ Data dihapus", "success");
                    bootstrap.Modal.getInstance($("#modalHapusPesertaDidik"))?.hide();
                    await loadTablePesertaDidik(getCurrentFilters());
                } else if (res.status === 419) {
                    toast("❌ Sesi CSRF kedaluwarsa. Silakan refresh halaman.", "error");
                } else {
                    toast(j.message || `❌ Gagal menghapus data (HTTP ${res.status}).`, "error");
                }
            } catch (err) {
                console.error(err);
                toast("Terjadi kesalahan jaringan saat menghapus.", "error");
            }
        });

        // ============================================================
        // GLOBAL FILE CHECK
        // ============================================================
        $$(".file-check").forEach((cb) => {
            cb.addEventListener("change", function () {
                const target = $(this.dataset.target);
                if (!target) return;
                if (this.checked) {
                    target.classList.remove("d-none");
                    target.disabled = false;
                    showFileWarning(target);
                    target.onchange = () => {
                        if (target.files && target.files.length > 0)
                            hideFileWarning(target);
                        else showFileWarning(target);
                    };
                } else {
                    hideFileWarning(target);
                    if (target.closest("#modalTambahPesertaDidik")) {
                        target.classList.add("d-none");
                        target.disabled = true;
                        if (target.type === "file") target.value = "";
                    }
                }
            });
        });

        // ============================================================
        // BIND ROW ACTIONS (dipanggil ulang setiap load tabel AJAX)
        // ============================================================
        function bindRowActions() {
            wrapper.querySelectorAll(".btn-edit-peserta-didik").forEach((b) => {
                b.onclick = () => editPesertaDidik(b.dataset.id);
            });
            wrapper
                .querySelectorAll(".btn-delete-peserta-didik")
                .forEach((b) => {
                    b.onclick = () => {
                        const hapusId = $("#hapus_id");
                        if (hapusId) hapusId.value = b.dataset.id;
                        const nama = b.dataset.nama ||
                            b.closest("tr")
                                ?.querySelector(".nama-peserta-didik")
                                ?.textContent?.trim();
                        const hapusNama = $("#hapus_nama_peserta_didik");
                        if (nama && hapusNama) hapusNama.textContent = nama;
                        bootstrap.Modal.getOrCreateInstance(
                            $("#modalHapusPesertaDidik")
                        ).show();
                    };
                });
            wrapper.querySelectorAll(".btn-view-peserta-didik").forEach((b) => {
                b.onclick = () => detailPesertaDidik(b.dataset.id);
            });

            // ── Pagination ──
            wrapper.querySelectorAll(".pagination a").forEach((link) => {
                link.addEventListener("click", function (e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get("page");
                    if (page) {
                        // Ambil filter saat ini, lalu tambahkan page
                        const filters = getCurrentFilters();
                        filters.page = parseInt(page);

                        // Update URL dengan pushState (biar back/forward works)
                        const newUrl = new URL(
                            window.location.pathname,
                            window.location.origin
                        );
                        newUrl.search = new URLSearchParams(filters).toString();
                        window.history.pushState(null, "", newUrl.href);

                        // Muat tabel
                        loadTablePesertaDidik(filters);
                    }
                });
            });

            // ── Preview & Download file ──
            document
                .querySelectorAll("[data-peserta-didik-preview]")
                .forEach((btn) => {
                    btn.onclick = () =>
                        previewFile(
                            btn.dataset.pesertaDidikPreview,
                            btn.dataset.field
                        );
                });
            document
                .querySelectorAll("[data-peserta-didik-download]")
                .forEach((btn) => {
                    btn.onclick = () =>
                        openDownload(
                            btn.dataset.pesertaDidikDownload,
                            btn.dataset.field
                        );
                });

            if (window.bootstrap?.Tooltip) {
                wrapper
                    .querySelectorAll('[data-bs-toggle="tooltip"]')
                    .forEach((el) => {
                        bootstrap.Tooltip.getInstance(el)?.dispose();
                        new bootstrap.Tooltip(el);
                    });
            }
        }

        // ============================================================
        // SANITASI INPUT (alphanumeric)
        // Selaras dengan form Surat: teks hanya huruf/angka/spasi.
        // - nama & tempat lahir : huruf, angka, spasi
        // - tahun angkatan       : + / - (mis. "2025/2026")
        // - alamat               : + . , / - (alamat lazim)
        // Guard realtime; validasi sebenarnya tetap di backend
        // (Store/UpdatePesertaDidikRequest).
        // ============================================================
        const RE_TEXT_STRIP = /[^A-Za-z0-9 ]+/g;
        const RE_YEAR_STRIP = /[^A-Za-z0-9 \/-]+/g;
        const RE_ADDR_STRIP = /[^A-Za-z0-9 .,\/-]+/g;

        const PD_TEXT_FIELDS = new Set(["nama_peserta_didik", "tempat_lahir"]);

        function sanitizePesertaField(el) {
            if (!el || (el.tagName !== "INPUT" && el.tagName !== "TEXTAREA")) {
                return;
            }

            const name = el.name;
            let re = null;
            if (PD_TEXT_FIELDS.has(name)) re = RE_TEXT_STRIP;
            else if (name === "tahun_angkatan") re = RE_YEAR_STRIP;
            else if (name === "alamat") re = RE_ADDR_STRIP;
            if (!re) return;

            const before = el.value;
            const after = before.replace(re, "");
            if (before === after) return;

            const start = el.selectionStart ?? after.length;
            const removed = before.length - after.length;
            el.value = after;
            const pos = Math.max(0, start - removed);
            try {
                el.setSelectionRange(pos, pos);
            } catch (_) { }
        }

        function setupPesertaAlphanumericGuards() {
            ["#formTambahPesertaDidik", "#formEditPesertaDidik"].forEach(
                (sel) => {
                    const form = $(sel);
                    if (!form) return;
                    form.addEventListener("input", (e) =>
                        sanitizePesertaField(e.target)
                    );
                }
            );
        }

        // ============================================================
        // EXPOSE API PUBLIK
        // ============================================================
        window.PesertaDidikApp = {
            loadTable: loadTablePesertaDidik,
            editPesertaDidik,
            detailPesertaDidik,
            downloadAllFiles,
            downloadSelectedFiles,
            openDownload,
            previewFile,
        };

        // ============================================================
        // INIT — bind row actions pada tabel yang sudah di-render server-side.
        // Tidak perlu AJAX ulang karena server sudah render tabel yang benar.
        // AJAX hanya dipanggil saat filter/pagination berubah.
        // ============================================================
        function initPesertaDidikPage() {
            setupPesertaAlphanumericGuards();

            // Reset semua input filter ke default
            if (searchInput) searchInput.value = "";
            if (rombelSelect) rombelSelect.selectedIndex = 0;
            if (statusSelect) statusSelect.selectedIndex = 0;
            if (sortAngkatanSelect) sortAngkatanSelect.selectedIndex = 0;
            if (sortNamaSelect) sortNamaSelect.selectedIndex = 0;
            syncAllCustomSelects();
            document.getElementById("resetSearch")?.classList.add("d-none");

            // Bersihkan query string di URL
            window.history.replaceState(
                null, "",
                new URL(window.location.pathname, window.location.origin).href
            );

            // Tabel sudah ada dari server — cukup pasang event handler baris
            bindRowActions();

            // Kalau dibuka lewat link chat Arsy (?detail=ID), langsung buka
            // modal detail peserta_didik yang dimaksud.
            if (_initDetailId) {
                detailPesertaDidik(_initDetailId);
            }
        }

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initPesertaDidikPage);
        } else {
            // Script dimuat setelah DOMContentLoaded sudah terjadi
            // (mis. saat dibundel sebagai module/defer) — jalankan langsung.
            initPesertaDidikPage();
        }
    })();
}