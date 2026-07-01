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
        const baseUrl = wrapper.dataset.baseUrl;
        if (!baseUrl) {
            console.error(
                "[peserta-didik.js] data-base-url tidak ditemukan pada wrapper. " +
                    "Pastikan ada: <div id='wrapper-table-peserta_didik' data-base-url='{{ route('peserta-didik.index') }}'>"
            );
            return;
        }

        const token = $('meta[name="csrf-token"]')?.getAttribute("content");

        // ============================================================
        // TOAST
        // ============================================================
        function toast(message, type = "info") {
            const existing = document.getElementById(
                "PesertaDidikToastContainer"
            );
            if (existing) existing.remove();

            const container = document.createElement("div");
            container.id = "PesertaDidikToastContainer";
            container.className = "position-fixed top-0 end-0 p-3";
            container.style.zIndex = "2100";

            const map = {
                success: ["bg-success", "✅"],
                error: ["bg-danger", "❌"],
                warning: ["bg-warning text-dark", "⚠️"],
                info: ["bg-primary", "ℹ️"],
            };
            const [bg, icon] = map[type] || map.info;

            container.innerHTML = `
            <div class="toast align-items-center text-white ${bg} border-0 shadow-sm"
                role="alert" aria-live="assertive" aria-atomic="true"
                style="min-width:280px;opacity:0;transform:translateY(-10px);transition:all .4s ease;">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">${icon} ${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;

            document.body.appendChild(container);
            const toastEl = container.querySelector(".toast");
            setTimeout(() => {
                toastEl.style.opacity = "1";
                toastEl.style.transform = "translateY(0)";
            }, 50);
            setTimeout(() => {
                toastEl.style.opacity = "0";
                toastEl.style.transform = "translateY(-10px)";
                setTimeout(() => container.remove(), 500);
            }, 3500);
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
            } catch {}
        }

        function hideFormAlert(selector) {
            const el = $(selector);
            if (!el) return;
            el.textContent = "";
            el.classList.add("d-none");
            el.style.display = "none";
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

        // ============================================================
        // DEBOUNCE
        // ============================================================
        function debounce(fn, delay = 400) {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...args), delay);
            };
        }

        // ============================================================
        // FILTER BAR — elemen
        // ============================================================
        const filterForm = $(".filter-bar form");
        const searchInput = $("#searchInput");
        const rombelSelect = $("#rombel");
        const statusSelect = $("#statusFilter");
        const sortAngkatanSelect = $("#sortAngkatan");
        const sortDataSelect = $("#sortData");
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
            if (sortDataSelect?.value) params.sort_data = sortDataSelect.value;
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
            try {
                wrapper.innerHTML = `<div class="text-center p-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div></div>`;
                const url = query ? `${baseUrl}?${query}` : baseUrl;
                const res = await fetch(url, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                    credentials: "same-origin"
                });
                if (!res.ok) throw new Error("Gagal memuat tabel");
                wrapper.innerHTML = await res.text();
                bindRowActions();
            } catch (err) {
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
                    if (sortAngkatanSelect.value && sortDataSelect)
                        sortDataSelect.selectedIndex = 0;
                    applyFilters(true, true);
                });
            }

            if (sortDataSelect) {
                sortDataSelect.addEventListener("change", () => {
                    if (sortDataSelect.value && sortAngkatanSelect)
                        sortAngkatanSelect.selectedIndex = 0;
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
                    if (sortDataSelect) sortDataSelect.selectedIndex = 0;
                    document
                        .getElementById("resetSearch")
                        ?.classList.add("d-none");
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
                    if (sortDataSelect) sortDataSelect.selectedIndex = 0;
                    document
                        .getElementById("resetSearch")
                        ?.classList.add("d-none");
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
            if (sortDataSelect)
                sortDataSelect.value = params.get("sort_data") || "";

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
                const viewer = $("#preview-container");
                viewer.innerHTML = "";
                modalEl.dataset.pesertaDidikId = id;
                modalEl.dataset.field = field;

                if (/\.pdf$/i.test(fileUrl)) {
                    viewer.innerHTML = `<embed src="${fileUrl}" type="application/pdf" width="100%" height="600px" style="border:none;border-radius:8px;" />`;
                } else if (/\.(jpe?g|png|gif|webp)$/i.test(fileUrl)) {
                    viewer.innerHTML = `<img src="${fileUrl}" alt="Preview" class="img-fluid rounded shadow-sm w-100">`;
                } else {
                    viewer.innerHTML = `<div class="alert alert-info mt-3">File tidak dapat dipratinjau. Gunakan tombol <strong>Unduh File</strong>.</div>`;
                }

                new bootstrap.Modal(modalEl).show();
            } catch (err) {
                console.error(err);
                toast("Gagal memuat file.", "error");
            }
        }

        $("#btnUnduhPreview")?.addEventListener("click", () => {
            const modalEl = $("#modalPreviewFile");
            const id = modalEl?.dataset.pesertaDidikId;
            const field = modalEl?.dataset.field;
            if (!id || !field) return toast("File tidak valid.", "error");
            openDownload(id, field);
        });

        // ============================================================
        // REFRESH EDIT DOWNLOAD ALL
        // ============================================================
        function refreshEditDownloadAll() {
            const id = $("#edit_id")?.value || "";
            const fileFields = [
                "file_ppdb",
                "file_kk",
                "file_akte",
                "file_ktp",
                "file_kts",
                "file_foto",
                "file_ijazah_smp",
                "file_ijazah_sma",
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
                    "file_ppdb",
                    "file_kk",
                    "file_akte",
                    "file_ktp",
                    "file_kts",
                    "file_foto",
                    "file_ijazah_smp",
                    "file_ijazah_sma",
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
                    } catch {}
                    input.classList.add("d-none");
                    input.disabled = true;
                    check.checked = !!url;

                    check.onchange = () => {
                        if (check.checked) {
                            input.dataset.markDelete = "0";
                            if (input.dataset.serverFile !== "1") {
                                input.classList.remove("d-none");
                                input.disabled = false;
                            } else {
                                input.classList.add("d-none");
                                input.disabled = true;
                            }
                        } else {
                            if (input.dataset.serverFile === "1")
                                input.dataset.markDelete = "1";
                            else input.dataset.markDelete = "0";
                            input.dataset.replace = "0";
                            input.classList.add("d-none");
                            try {
                                input.value = "";
                            } catch {}
                            input.disabled = true;
                        }
                        refreshEditDownloadAll();
                    };

                    input.onchange = () => {
                        if (input.files && input.files.length > 0) {
                            input.dataset.replace = "1";
                            input.dataset.markDelete = "0";
                            check.checked = true;
                        } else {
                            input.dataset.replace = "0";
                        }
                        refreshEditDownloadAll();
                    };
                });

                refreshEditDownloadAll();

                const modalEl = $("#modalEditPesertaDidik");
                new bootstrap.Modal(modalEl).show();

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
                    "tanggal_lahir",
                    "rombel",
                    "alamat",
                ].forEach((f) => {
                    const el = $(`#detail_${f}`);
                    if (el) el.textContent = s[f] || "-";
                });

                const fileFields = [
                    "file_ppdb",
                    "file_kk",
                    "file_akte",
                    "file_ktp",
                    "file_kts",
                    "file_foto",
                    "file_ijazah_smp",
                    "file_ijazah_sma",
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
                            btn.innerHTML = `
                          <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-sm btn-info text-white" data-peserta-didik-preview="${id}" data-field="${field}">
                              <i class="bi bi-eye"></i> Lihat
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" data-peserta-didik-download="${id}" data-field="${field}">
                              <i class="bi bi-download"></i> Unduh
                            </button>
                          </div>`;
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
                    new bootstrap.Modal(modalEl).show();
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
                    const j = await res.json().catch(() => ({}));
                    toast(
                        j.message || "✅ Peserta didik berhasil ditambahkan",
                        j.success ? "success" : "error"
                    );
                    bootstrap.Modal.getInstance(modalTambah)?.hide();
                    await loadTablePesertaDidik(getCurrentFilters());
                } catch (err) {
                    console.error(err);
                    toast("Terjadi kesalahan server.", "error");
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
                    "file_ppdb",
                    "file_kk",
                    "file_akte",
                    "file_ktp",
                    "file_kts",
                    "file_foto",
                    "file_ijazah_smp",
                    "file_ijazah_sma",
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
                    const j = await res.json().catch(() => ({}));
                    toast(
                        j.message || "✅ Data diperbarui",
                        j.success ? "success" : "error"
                    );
                    bootstrap.Modal.getInstance(
                        $("#modalEditPesertaDidik")
                    )?.hide();
                    await loadTablePesertaDidik(getCurrentFilters());
                } catch (err) {
                    console.error(err);
                    toast("Terjadi kesalahan saat update.", "error");
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
                const j = await res.json().catch(() => ({}));
                toast(
                    j.message || "✅ Data dihapus",
                    j.success ? "success" : "error"
                );
                bootstrap.Modal.getInstance(
                    $("#modalHapusPesertaDidik")
                )?.hide();
                await loadTablePesertaDidik(getCurrentFilters());
            } catch (err) {
                console.error(err);
                toast(
                    "Terjadi kesalahan saat menghapus peserta_didik",
                    "error"
                );
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
                } else {
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
                        const nama = b
                            .closest("tr")
                            ?.querySelector(".nama-peserta-didik")
                            ?.textContent?.trim();
                        const hapusNama = $("#hapus_nama_peserta_didik");
                        if (nama && hapusNama) hapusNama.textContent = nama;
                        new bootstrap.Modal(
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
        // INIT — reset filter setiap kali halaman dimuat/di-refresh
        // (Catatan: dahulu filter dipulihkan dari query string URL,
        //  sekarang sengaja di-reset agar setiap refresh halaman
        //  selalu menampilkan data tanpa filter aktif.)
        // ============================================================
        function initPesertaDidikPage() {
            // Kosongkan semua input filter
            if (searchInput) searchInput.value = "";
            if (rombelSelect) rombelSelect.selectedIndex = 0;
            if (statusSelect) statusSelect.selectedIndex = 0;
            if (sortAngkatanSelect) sortAngkatanSelect.selectedIndex = 0;
            if (sortDataSelect) sortDataSelect.selectedIndex = 0;

            const resetSearch = document.getElementById("resetSearch");
            resetSearch?.classList.add("d-none");

            // Bersihkan query string di URL (tanpa filter/page)
            const cleanUrl = new URL(
                window.location.pathname,
                window.location.origin
            );
            window.history.replaceState(null, "", cleanUrl.href);

            // Muat tabel tanpa filter (page 1)
            loadTablePesertaDidik({});
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
