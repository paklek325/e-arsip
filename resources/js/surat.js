/* ================================================================
 * surat.js  –  Modul manajemen surat (IIFE + namespace guard)
 *
 * Guard dilakukan dengan:
 *  1. Seluruh kode dibungkus IIFE `(() => { "use strict"; ... })()`
 *     → semua `let`/`const`/`function` bersifat lokal, tidak bocor ke global.
 *  2. Variabel lokal `$` dan `$$` TIDAK di-expose ke window sehingga
 *     tidak menimpa jQuery atau helper milik modul lain.
 *  3. API publik (loadTable, viewSurat, editSurat) di-expose di bawah
 *     satu namespace `window.SuratApp` sehingga tidak bentrok dengan
 *     fungsi bernama sama di file JS lain.
 *  4. Guard `if (!window.SuratApp)` mencegah inisialisasi ganda kalau
 *     file ini di-load lebih dari sekali.
 * ================================================================ */
if (window.SuratApp) {
    console.warn("[surat.js] SuratApp sudah terdaftar, skip re-init.");
} else {
    (() => {
        "use strict";

        /* ========================
         * SCOPE GUARD — hanya jalan di halaman surat
         * BUGFIX: sebelumnya guard ini cek `#tableContainer`, padahal ID itu
         * JUGA dipakai di halaman User (dan kemungkinan halaman lain).
         * Akibatnya surat.js tetap jalan di halaman /user, ikut fetch ke
         * /surat, lalu menimpa tabel User dengan tabel Surat.
         * Sekarang dicek pakai elemen unik milik halaman Surat saja,
         * sama seperti pola "#page-user" di user.js.
         * ======================== */
        if (!document.querySelector("#page-surat")) return;

        const tableContainer = document.querySelector("#tableContainer");
        if (!tableContainer) return;

        // Baca kunci jenis (Masuk/Keluar) dan URL dasar dari atribut
        // data-lock-jenis / data-base-url yang di-render oleh Blade.
        // Ini memastikan AJAX sort/filter Surat Masuk SELALU fetch ke
        // /surat/filter/masuk dan Surat Keluar ke /surat/filter/keluar,
        // sehingga data tidak pernah bercampur antara kedua menu.
        const pageSuratEl = document.querySelector("#page-surat");
        const lockJenis = pageSuratEl?.dataset?.lockJenis || "";
        const baseUrl = pageSuratEl?.dataset?.baseUrl || "/surat";

        /* ========================
         * GLOBAL UTILITIES
         * ======================== */
        const CSRF =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") || "";

        // $ dan $$ sengaja TIDAK di-expose ke window agar tidak bentrok dengan
        // jQuery ($) atau helper serupa milik modul JS lain.
        const $ = (sel) => document.querySelector(sel);
        const $$ = (sel) => Array.from(document.querySelectorAll(sel));

        /**
         * Ambil instance bootstrap.Modal yang sudah ada untuk elemen ini,
         * atau buat baru kalau belum ada.
         * BUGFIX: kode lama selalu memanggil `new bootstrap.Modal(el)` setiap
         * modal dibuka. Ini menimpa instance lama di Data store milik elemen
         * TANPA men-dispose-nya, sehingga event listener lama (show/hide,
         * backdrop, dsb.) tetap menempel dan menumpuk. Ketika modal dibuka-
         * tutup berkali-kali (mis. detail surat/siswa dibuka lagi setelah
         * ditutup), listener lama yang sudah "yatim" itu bentrok dengan
         * instance baru dan menyebabkan:
         *   Uncaught TypeError: this._config is undefined
         *       at _initializeBackDrop (bootstrap.bundle.min.js)
         * Solusinya: selalu pakai getOrCreateInstance agar hanya ada SATU
         * instance Modal per elemen sepanjang hidup halaman.
         */
        function getOrCreateModal(elOrSelector) {
            const el =
                typeof elOrSelector === "string"
                    ? $(elOrSelector)
                    : elOrSelector;
            if (!el || typeof bootstrap === "undefined" || !bootstrap.Modal)
                return null;
            return bootstrap.Modal.getOrCreateInstance(el);
        }

        // ID surat yang sedang dibuka di modal detail
        let currentSuratId = null;

        // Tangkap URL params dari laporan sebelum dibersihkan (baca sinkron saat modul dijalankan)
        const _initUrl = new URLSearchParams(window.location.search);
        let lapParams = {
            bulan: _initUrl.get("bulan") || null,
            tahun: _initUrl.get("tahun") || null,
            jenis_surat: _initUrl.get("jenis_surat") || null,
        };

        // Baca param ?tanggal=YYYY-MM dari URL (navigasi dari chat) dan set ke input #tanggal
        let _initTanggal = _initUrl.get("tanggal") || null;
        if (_initTanggal) {
            document.addEventListener("DOMContentLoaded", () => {
                const tanggalEl = document.getElementById("tanggal");
                if (tanggalEl) tanggalEl.value = _initTanggal;
            });
        }

        // Baca param ?detail=ID dari URL (navigasi dari chat Arsy) — langsung
        // buka modal detail surat yang dimaksud begitu halaman termuat.
        let _initDetailId = _initUrl.get("detail") || null;
        if (_initDetailId) {
            document.addEventListener("DOMContentLoaded", () => {
                viewSurat(_initDetailId);
            });
        }

        const toast = (message, type = "info") =>
            window.AppToast(message, type);

        const debounce = window.debounce;
        const safeFetch = window.safeFetch;
        const tableGuard = window.createFetchGuard(); // cegah race condition search/filter tabel surat

        /* ========== INLINE ALERT DI DALAM MODAL ========== */
        function showFormAlert(alertSelector, message, type = "warning") {
            const alertEl = $(alertSelector);
            if (!alertEl) {
                toast(message, type);
                return;
            }
            alertEl.className = `alert alert-${type} mb-3`;
            alertEl.textContent = message;
            alertEl.classList.remove("d-none");
        }

        function hideFormAlert(alertSelector) {
            const alertEl = $(alertSelector);
            if (alertEl) {
                alertEl.classList.add("d-none");
                alertEl.textContent = "";
            }
        }

        /* ========================
         * VALIDASI FILE (frontend)
         * ======================== */

        function validateFileInputs() {
            return true;
        }

        /* ========================
         * DATE HELPERS (d/m/Y)
         * ======================== */

        function formatDateDMY(dateStr) {
            if (!dateStr) return "-";
            const normalized =
                dateStr.trim().length > 10
                    ? dateStr.replace(" ", "T")
                    : dateStr;

            const d = new Date(normalized);
            if (Number.isNaN(d.getTime())) return "-";

            const day = String(d.getDate()).padStart(2, "0");
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        }

        function formatDateTimeDMY(dateStr) {
            if (!dateStr) return "-";
            const normalized =
                dateStr.trim().length > 10
                    ? dateStr.replace(" ", "T")
                    : dateStr;
            const d = new Date(normalized);
            if (Number.isNaN(d.getTime())) return "-";

            const day = String(d.getDate()).padStart(2, "0");
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const year = d.getFullYear();
            const hours = String(d.getHours()).padStart(2, "0");
            const minutes = String(d.getMinutes()).padStart(2, "0");

            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }

        /* ========================
         * HELPERS
         * ======================== */

        function getFileIcon(name) {
            const ext = name.split(".").pop().toLowerCase();
            const map = {
                pdf: "bi bi-file-earmark-pdf text-danger",
                doc: "bi bi-file-earmark-word text-primary",
                docx: "bi bi-file-earmark-word text-primary",
                xls: "bi bi-file-earmark-excel text-success",
                xlsx: "bi bi-file-earmark-excel text-success",
                jpg: "bi bi-file-image text-warning",
                jpeg: "bi bi-file-image text-warning",
                png: "bi bi-file-image text-warning",
            };
            return map[ext] || "bi bi-file-earmark text-muted";
        }

        // ambil nama file dari URL (fallback kalau gagal: "lampiran")
        function getFilenameFromUrl(url, fallback = "lampiran") {
            try {
                const u = new URL(url, window.location.origin);
                const parts = u.pathname.split("/");
                const last = parts.pop() || parts.pop();
                return last || fallback;
            } catch (e) {
                return fallback;
            }
        }

        // ===== SINKRONISASI BULAN / TAHUN / TANGGAL =====
        function syncDateFields(tanggalEl, bulanEl, tahunEl) {
            if (!tanggalEl) return;

            function tanggalKeBulanTahun() {
                if (!tanggalEl.value) return;
                const d = new Date(tanggalEl.value + "T00:00:00");
                if (bulanEl) {
                    bulanEl.value = String(d.getMonth() + 1);
                    // Custom dropdown Bulan punya tampilan label terpisah dari
                    // <select> aslinya — kalau value diubah lewat JS (bukan
                    // klik user), label itu tidak ikut ter-update kecuali
                    // di-sync manual. Makanya Bulan kelihatan tidak berubah
                    // padahal Tanggal Surat sudah diganti.
                    syncModalSelect(bulanEl);
                }
                if (tahunEl) tahunEl.value = String(d.getFullYear());
            }

            function bulanTahunKeTanggal() {
                const bulan = parseInt(bulanEl?.value || 0);
                const tahun = parseInt(tahunEl?.value || 0);
                if (!bulan || !tahun) return;
                let day = 1;
                if (tanggalEl.value) {
                    const ex = new Date(tanggalEl.value + "T00:00:00");
                    const maxDay = new Date(tahun, bulan, 0).getDate();
                    day = Math.min(ex.getDate(), maxDay);
                }
                const mm = String(bulan).padStart(2, "0");
                const dd = String(day).padStart(2, "0");
                tanggalEl.value = `${tahun}-${mm}-${dd}`;
            }

            tanggalEl.removeEventListener("change", tanggalEl._syncHandler);
            tanggalEl._syncHandler = tanggalKeBulanTahun;
            tanggalEl.addEventListener("change", tanggalEl._syncHandler);

            if (bulanEl) {
                bulanEl.removeEventListener("change", bulanEl._syncHandler);
                bulanEl._syncHandler = bulanTahunKeTanggal;
                bulanEl.addEventListener("change", bulanEl._syncHandler);
            }
            if (tahunEl) {
                tahunEl.removeEventListener("change", tahunEl._syncHandler);
                tahunEl._syncHandler = bulanTahunKeTanggal;
                tahunEl.addEventListener("change", tahunEl._syncHandler);
            }

            // Inisialisasi awal: jika tanggal sudah terisi, update bulan & tahun
            tanggalKeBulanTahun();
        }

        // ===== GENERATE NOMOR SURAT OTOMATIS =====
        // Mengirim kode/instansi/bulan/tahun (jika sudah diisi) supaya nomor
        // yang dihasilkan sesuai format resmi: 001/KODE/INSTANSI/ROMAWI/TAHUN,
        // bukan cuma angka urut polos.
        async function generateNoSurat({
            jenis = "Keluar",
            exclude_id = "",
            kode = "",
            bulan = "",
            tahun = "",
        } = {}) {
            try {
                const params = new URLSearchParams({ jenis });
                if (exclude_id) params.append("exclude_id", exclude_id);
                if (kode) params.append("kode", kode);
                if (bulan) params.append("bulan", bulan);
                if (tahun) params.append("tahun", tahun);
                const res = await safeFetch(`/surat/generate-nomor?${params}`, {
                    headers: { Accept: "application/json" },
                });
                const data = await res.json();
                return data.nomor || null;
            } catch (err) {
                console.error("Gagal generate nomor surat:", err);
                toast("Gagal generate nomor surat.", "error");
                return null;
            }
        }

        // Kumpulkan kode/instansi/bulan/tahun yang sedang terisi di modal
        // Tambah/Edit surat untuk dikirim ke generateNoSurat().
        function collectNomorContext(prefix) {
            const kodeEl =
                prefix === "add"
                    ? $("#kode_surat_select_add")
                    : $("#kode_input_edit");
            const bulanEl = $(`#${prefix}_bulan`);
            const tahunEl = $(`#${prefix}_tahun`);

            return {
                kode: kodeEl?.value?.trim() || "",
                bulan: bulanEl?.value || "",
                tahun: tahunEl?.value || "",
            };
        }

        // ===== AUTO REGENERATE NOMOR SURAT saat Bulan/Tahun/Tanggal berubah =====
        // Nomor Surat (khusus Surat Keluar) ikut menyesuaikan setiap kali user
        // mengganti Bulan, Tahun, atau Tanggal Surat (Instansi TIDAK lagi jadi
        // pemicu, karena tidak ikut menentukan nomor induk surat) — TAPI HANYA kalau
        // user belum mengetik nomor secara manual. Begitu user mengetik sendiri
        // di kolom Nomor Surat, field ini dianggap "manual" dan tidak akan
        // ditimpa lagi oleh perubahan field lain (sampai user klik Generate lagi
        // atau modal dibuka ulang dari awal).
        function attachNomorAutoRegen(prefix) {
            const jenisEl =
                prefix === "add" ? $("#jenis_surat_add") : $("#edit_jenis");
            const noEl = document.getElementById(`${prefix}_no_surat`);
            const bulanEl = document.getElementById(`${prefix}_bulan`);
            const tahunEl = document.getElementById(`${prefix}_tahun`);
            const tanggalEl = document.getElementById(`${prefix}_tanggal`);
            if (!noEl) return;

            // Tandai manual begitu user mengetik langsung di kolom Nomor Surat
            noEl.removeEventListener("input", noEl._manualFlagHandler);
            noEl._manualFlagHandler = () => {
                noEl.dataset.manualEdit = "1";
            };
            noEl.addEventListener("input", noEl._manualFlagHandler);

            async function regen() {
                const jenisVal = (jenisEl?.value || "").trim().toLowerCase();
                // Hanya berlaku untuk Surat Keluar (Surat Masuk nomornya manual)
                if (jenisVal !== "keluar") return;
                // Nomor sudah diketik/ditentukan manual → jangan ditimpa
                if (noEl.dataset.manualEdit === "1") return;

                const ctx = collectNomorContext(prefix);
                const exclude_id =
                    prefix === "edit"
                        ? document.getElementById("edit_id")?.value || ""
                        : "";
                const nomor = await generateNoSurat({
                    jenis: "Keluar",
                    exclude_id,
                    ...ctx,
                });
                if (nomor) noEl.value = nomor;
            }

            [bulanEl, tahunEl, tanggalEl].forEach((el) => {
                if (!el) return;
                el.removeEventListener("change", el._nomorRegenHandler);
                el._nomorRegenHandler = regen;
                el.addEventListener("change", el._nomorRegenHandler);
            });
        }

        // Tanggal hari ini dalam format YYYY-MM-DD berdasarkan zona waktu
        // LOKAL (bukan UTC). Memakai `Date#toISOString()` akan salah untuk
        // wilayah dengan offset positif (mis. WIB, UTC+7): dini hari sampai
        // sekitar jam 07.00 pagi bisa menghasilkan tanggal KEMARIN.
        function todayLocalISO() {
            const now = new Date();
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, "0");
            const dd = String(now.getDate()).padStart(2, "0");
            return `${yyyy}-${mm}-${dd}`;
        }

        // Cache di memori — daftar kode surat ini master data yang jarang
        // berubah, jadi tidak perlu di-fetch ulang ke server SETIAP KALI
        // modal Edit/Tambah dibuka. Sebelumnya inilah yang bikin aksi Edit
        // terasa lambat: setiap klik "Edit" menunggu 1 round-trip network
        // baru ke /surat/kode-surat-keluar sebelum modal terisi.
        let _kodeListCache = null;
        let _kodeListPromise = null;

        async function fetchKodeList({ forceRefresh = false } = {}) {
            if (!forceRefresh && _kodeListCache) return _kodeListCache;
            // Kalau ada request yang masih berjalan (mis. 2 klik Edit
            // beruntun), pakai promise yang sama, jangan fetch dobel.
            if (!forceRefresh && _kodeListPromise) return _kodeListPromise;

            _kodeListPromise = (async () => {
                try {
                    const res = await safeFetch("/surat/kode-surat-keluar", {
                        headers: { Accept: "application/json" },
                    });
                    const data = await res.json();

                    const list = Array.isArray(data)
                        ? data.map((k) => ({
                            kode: k.kode,
                            description: k.description || "",
                        }))
                        : [];

                    _kodeListCache = list;
                    return list;
                } catch (err) {
                    console.error("Gagal memuat daftar kode surat:", err);
                    toast("Gagal memuat daftar kode surat", "error");
                    return [];
                } finally {
                    _kodeListPromise = null;
                }
            })();

            return _kodeListPromise;
        }

        // ===== CEK DUPLIKAT SURAT (no_surat + instansi + tanggal_surat) =====
        async function checkDuplicateSurat({
            no_surat,
            instansi,
            tanggal_surat,
            jenis_surat = "",
            exclude_id = "",
        }) {
            if (!no_surat || !instansi || !tanggal_surat) {
                return false;
            }

            try {
                const params = new URLSearchParams({
                    no_surat,
                    instansi,
                    tanggal_surat,
                });

                if (jenis_surat) params.append("jenis_surat", jenis_surat);
                if (exclude_id) params.append("exclude_id", exclude_id);

                const url = `/surat/cek-duplikat?${params.toString()}`;

                const res = await safeFetch(url, {
                    headers: { Accept: "application/json" },
                });

                const data = await res.json();

                if (typeof data?.exists === "boolean") {
                    return data.exists;
                } else {
                    toast(
                        "Respon cek duplikat tidak valid dari server.",
                        "error"
                    );
                    return false;
                }
            } catch (err) {
                console.error("Gagal cek duplikat surat:", err);
                toast("Gagal mengecek duplikat surat.", "error");
                return false;
            }
        }

        function addTodayButton(inputSelector) {
            const input = $(inputSelector);
            if (!input || input.dataset.hasTodayBtn) return;

            input.dataset.hasTodayBtn = "1";

            let inputGroup = input.closest(".input-group");
            if (!inputGroup) return;

            const existingButton = inputGroup.querySelector(".btn-today-date");
            if (existingButton) return;

            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "btn btn-outline-secondary btn-today-date";
            btn.textContent = "Hari Ini";

            btn.addEventListener("click", () => {
                const now = new Date();
                const yyyy = now.getFullYear();
                const mm = String(now.getMonth() + 1).padStart(2, "0");
                const dd = String(now.getDate()).padStart(2, "0");
                input.value = `${yyyy}-${mm}-${dd}`;
                input.dispatchEvent(new Event("change"));
            });

            inputGroup.appendChild(btn);
        }

        /* ========================
         * TABLE + FILTER
         * ======================== */

        let searchDebounceTimer = null;

        function buildFilterParams() {
            const params = new URLSearchParams();
            const searchVal = $("#searchInput")?.value?.trim() || "";
            const tanggalVal = $("#tanggal")?.value || _initTanggal || "";
            const sortVal = $("#sort")?.value || "";

            if (tanggalVal) {
                if (/^\d{4}(?:-\d{2}(?:-\d{2})?)?$/.test(tanggalVal)) {
                    params.append("tanggal", tanggalVal);
                }
            }
            if (searchVal) params.append("search", searchVal);
            if (sortVal) params.append("sort", sortVal);

            // Jika halaman terkunci (Surat Masuk / Keluar), selalu kirim
            // parameter jenis agar backend tidak salah filter.
            // Untuk navigasi dari Laporan (lapParams), parameter jenis_surat
            // tetap dikirim jika lockJenis tidak ada.
            if (lockJenis) {
                params.append("jenis", lockJenis);
            } else {
                if (lapParams.bulan && !params.has("bulan"))
                    params.append("bulan", lapParams.bulan);
                if (lapParams.tahun && !params.has("tahun"))
                    params.append("tahun", lapParams.tahun);
                if (lapParams.jenis_surat)
                    params.append("jenis", lapParams.jenis_surat);
            }

            return params.toString();
        }

        /* ================================================================
         * CUSTOM DROPDOWN UNTUK MODAL TAMBAH & EDIT SURAT
         * ----------------------------------------------------------------
         * Native <select> yang dibuka di mobile melebar ke seluruh viewport
         * (perilaku browser, tidak bisa di-override via CSS). Solusinya:
         * ganti tampilan select dengan .pd-select-trigger + .pd-select-panel
         * — sama persis dengan yang sudah dipakai di filter bar.
         * <select> asli TETAP di DOM (tersembunyi via .pd-select-native)
         * agar FormData saat submit dan semua event listener tetap jalan.
         * ================================================================ */

        const modalSelectMap = new Map(); // select el → { trigger, panel }

        function buildModalCustomSelect(select) {
            if (!select) return;
            if (modalSelectMap.has(select)) {
                _syncModalSelectLabel(select);
                return;
            }
            const wrap = select.parentElement;
            if (!wrap) return;

            const trigger = document.createElement("div");
            trigger.className = select.className + " pd-select-trigger";
            trigger.setAttribute("role", "button");
            trigger.setAttribute("tabindex", "0");
            wrap.insertBefore(trigger, select.nextSibling);
            select.classList.add("pd-select-native");

            const panel = document.createElement("div");
            panel.className = "pd-select-panel";

            function rebuildOptions() {
                panel.innerHTML = "";
                Array.from(select.options).forEach((opt) => {
                    const item = document.createElement("div");
                    item.className = "pd-select-option";
                    item.dataset.value = opt.value;
                    item.textContent = opt.textContent;
                    item.addEventListener("click", () => {
                        select.value = opt.value;
                        _syncModalSelectLabel(select);
                        closeAllModalSelects();
                        select.dispatchEvent(
                            new Event("change", { bubbles: true })
                        );
                    });
                    panel.appendChild(item);
                });
            }

            rebuildOptions();

            // Jika select ada di dalam .input-group (mis. select Bulan
            // yang punya tombol kalender di sebelahnya), gunakan parent
            // dari input-group (.col.position-relative) sebagai container
            // panel agar panel muncul di bawah seluruh baris input-group,
            // bukan di dalam input-group yang tidak punya position:relative.
            const panelContainer =
                wrap.classList.contains("input-group")
                    ? wrap.parentElement
                    : wrap;
            if (panelContainer) {
                panelContainer.appendChild(panel);
            } else {
                wrap.appendChild(panel);
            }

            trigger.addEventListener("click", (e) => {
                e.stopPropagation();
                const isOpen = panel.classList.contains("show");
                closeAllModalSelects();
                if (!isOpen) {
                    rebuildOptions();
                    _syncModalSelectLabel(select);
                    panel.classList.add("show");
                    trigger.classList.add("active");
                }
            });

            trigger.addEventListener("keydown", (e) => {
                if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    trigger.click();
                } else if (e.key === "Escape") {
                    closeAllModalSelects();
                }
            });

            modalSelectMap.set(select, { trigger, panel });
            _syncModalSelectLabel(select);
        }

        function _syncModalSelectLabel(select) {
            const entry = modalSelectMap.get(select);
            if (!entry) return;
            const opt = select.options[select.selectedIndex];
            entry.trigger.textContent = opt ? opt.textContent : "";
            entry.panel
                .querySelectorAll(".pd-select-option")
                .forEach((item) => {
                    item.classList.toggle(
                        "is-selected",
                        item.dataset.value === select.value
                    );
                });
        }

        function syncModalSelect(select) {
            if (select && modalSelectMap.has(select))
                _syncModalSelectLabel(select);
        }

        function closeAllModalSelects() {
            modalSelectMap.forEach(({ trigger, panel }) => {
                panel.classList.remove("show");
                trigger.classList.remove("active");
            });
        }

        document.addEventListener("click", closeAllModalSelects);

        // Inisiasi custom dropdown untuk semua select (non-disabled) di modal
        function initModalSelects(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.querySelectorAll("select:not([disabled])").forEach((sel) => {
                buildModalCustomSelect(sel);
            });
        }

        // Sync semua label setelah value diset programatik
        function syncAllModalSelects(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.querySelectorAll("select").forEach((sel) => {
                if (modalSelectMap.has(sel)) _syncModalSelectLabel(sel);
            });
        }

        async function loadTable(url) {
            // Gunakan baseUrl (dibaca dari data-base-url #page-surat) sebagai
            // default — ini memastikan Surat Masuk selalu fetch ke
            // /surat/filter/masuk dan Surat Keluar ke /surat/filter/keluar,
            // bukan ke /surat umum yang menampilkan semua jenis.
            const fetchBase = url || baseUrl;
            const { signal, isStale } = tableGuard.start();
            try {
                const q = buildFilterParams();
                const fetchUrl = fetchBase.split("?")[0] + (q ? `?${q}` : "");

                const res = await safeFetch(fetchUrl, {
                    headers: { Accept: "text/html" },
                    signal,
                });
                if (isStale()) return; // ada pencarian/filter lebih baru menyusul

                const html = await res.text();
                if (isStale()) return;

                const container = $("#tableContainer");
                if (container) {
                    container.innerHTML = html;
                    // Pasang data-label SEKARANG (sinkron), jangan tunggu
                    // MutationObserver di tampilan.js yang di-debounce 50ms —
                    // itu penyebab tampilan kartu mobile "flick" (label
                    // muncul telat setelah tabel sempat ke-render tanpa label).
                    window.EArsipApplyTableLabels?.(container);
                    bindPaginationLinks();
                    rebindResetButtons();
                    rebindRowActionButtons();
                }
            } catch (err) {
                if (err.name === "AbortError" || isStale()) return;
                console.error(err);
                const container = $("#tableContainer");
                if (container) {
                    container.innerHTML = `<div class="alert alert-danger">Gagal memuat tabel. ${err.message || ""
                        }</div>`;
                }
            }
        }

        function rebindRowActionButtons() {
            // Scope ke tableContainer agar tidak menangkap tombol milik modul lain
            // (mis. btn-view / btn-edit milik siswa.js)
            const scope = tableContainer;

            scope.querySelectorAll(".btn-view").forEach((b) => {
                const clone = b.cloneNode(true);
                b.parentNode.replaceChild(clone, b);
                clone.addEventListener("click", (ev) => {
                    ev.preventDefault();
                    viewSurat(clone.dataset.id);
                });
            });

            scope.querySelectorAll(".btn-edit").forEach((b) => {
                const clone = b.cloneNode(true);
                b.parentNode.replaceChild(clone, b);
                clone.addEventListener("click", (ev) => {
                    ev.preventDefault();
                    editSurat(clone.dataset.id);
                });
            });

            scope.querySelectorAll(".btn-delete").forEach((b) => {
                const clone = b.cloneNode(true);
                b.parentNode.replaceChild(clone, b);
                clone.addEventListener("click", (ev) => {
                    ev.preventDefault();
                    const id = clone.dataset.id;
                    $("#deleteSuratId").value = id;
                    const row = clone.closest("tr");
                    const noSuratText = row
                        ? row
                            .querySelector("td:nth-child(2)")
                            ?.textContent?.trim()
                        : id;
                    const noSuratEl = $("#delete_no_surat_text");
                    if (noSuratEl) noSuratEl.textContent = noSuratText || id;
                    const deleteModal = getOrCreateModal("#deleteSuratModal");
                    if (deleteModal) {
                        deleteModal.show();
                    } else {
                        console.warn("Bootstrap Modal not available.");
                    }
                });
            });
        }

        function initFilters() {
            const searchInputEl = $("#searchInput");
            const resetSearchEl = $("#resetSearch");

            // Tombol X search SELALU tampil (sama seperti resetJenis &
            // resetSort) — tidak lagi bergantung pada ada/tidaknya teks
            // yang diketik di kolom search.
            resetSearchEl?.classList.remove("d-none");

            if (searchInputEl) {
                searchInputEl.addEventListener("input", () => {
                    clearTimeout(searchDebounceTimer);
                    searchDebounceTimer = setTimeout(() => loadTable(), 400);
                });
            }

            resetSearchEl?.addEventListener("click", () => {
                if (searchInputEl) {
                    searchInputEl.value = "";
                    searchInputEl.dispatchEvent(new Event("input"));
                    searchInputEl.focus();
                }
            });

            ["#tanggal", "#sort"].forEach((s) =>
                $(s)?.addEventListener("change", () => loadTable())
            );

            const resetAll = $("#resetBtn");
            if (resetAll) {
                resetAll.addEventListener("click", (e) => {
                    e.preventDefault();

                    const sortEl = $("#sort");
                    const defaultSort =
                        sortEl?.dataset.default || "tanggal_terbaru";

                    ["#searchInput", "#tanggal", "#sort"].forEach(
                        (sel) => {
                            const el = $(sel);
                            if (!el) return;
                            el.value = el.id === "sort" ? defaultSort : "";
                        }
                    );
                    syncCustomSelect($("#sort"));

                    lapParams = { bulan: null, tahun: null, jenis_surat: null };
                    _initTanggal = null;
                    loadTable();
                });
            }

            // Show/hide tombol reset sort
            const sortEl = $("#sort");
            const resetSortEl = $("#resetSort");

            // ============================================================
            // CUSTOM DROPDOWN — sama seperti peserta-didik.js.
            // <select> asli TETAP ADA di DOM (disembunyikan via CSS
            // .pd-select-native), cuma tampilannya diganti div custom
            // (.pd-select-trigger + .pd-select-panel) supaya panah &
            // tombol X tidak pernah tabrakan, dan popup pilihan tidak
            // ikut lebar/posisi native browser. Semua logic filter yang
            // sudah ada (buildFilterParams, updateResetButtons, dst)
            // tidak berubah — mereka tetap baca/tulis select.value biasa.
            // ============================================================
            const FILTER_SELECTS = [sortEl].filter(
                (el) => el && !el.disabled
            );
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
                        select.dispatchEvent(
                            new Event("change", { bubbles: true })
                        );
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
                entry.panel
                    .querySelectorAll(".pd-select-option")
                    .forEach((item) => {
                        item.classList.toggle(
                            "is-selected",
                            item.dataset.value === select.value
                        );
                    });
            }

            // Dipanggil setiap kali select.value diubah SECARA PROGRAMATIK
            // (bukan lewat klik opsi custom) — mis. tombol reset, sync dari
            // URL — supaya label trigger ikut ter-update.
            function syncCustomSelect(select) {
                if (select && customSelectMap.has(select))
                    updateTriggerLabel(select);
            }

            function closeAllCustomSelects() {
                customSelectMap.forEach(({ trigger, panel }) => {
                    panel.classList.remove("show");
                    trigger.classList.remove("active");
                });
            }

            document.addEventListener("click", closeAllCustomSelects);
            FILTER_SELECTS.forEach(buildCustomSelect);

            // Paksa tampil lewat inline style (bukan cuma toggle class),
            // supaya visibilitas tombol reset TIDAK bergantung sama sekali
            // pada CSS eksternal (yang bisa saja ter-cache versi lama di
            // browser user).
            function showResetBtn(el) {
                if (!el) return;
                el.style.setProperty("display", "flex", "important");
                el.classList.remove("d-none");
            }

            // Tombol reset Sort SELALU tampil
            showResetBtn(resetSortEl);

            resetSortEl?.addEventListener("click", () => {
                if (sortEl) sortEl.selectedIndex = 0;
                syncCustomSelect(sortEl);
                lapParams = { bulan: null, tahun: null, jenis_surat: null };
                sortEl?.dispatchEvent(new Event("change"));
            });

            rebindResetButtons();
        }

        function rebindResetButtons() {
            $$("button.reset-input").forEach((btn) => {
                const clone = btn.cloneNode(true);
                btn.parentNode.replaceChild(clone, btn);
                clone.addEventListener("click", (e) => {
                    e.preventDefault();
                    const target = $(clone.dataset.target);
                    if (!target) return;

                    let newVal = "";

                    if (target.dataset.default) newVal = target.dataset.default;
                    else if (target.id === "sort") newVal = "tanggal_terbaru";

                    target.value = newVal;

                    if (target.id === "searchInput") {
                        loadTable();
                    } else {
                        target.dispatchEvent(new Event("change"));
                    }
                });
            });
        }

        function bindPaginationLinks() {
            // Scope ke tableContainer agar tidak tangkap pagination milik tabel lain
            tableContainer.querySelectorAll(".pagination a").forEach((a) => {
                const clone = a.cloneNode(true);
                a.parentNode.replaceChild(clone, a);
                clone.addEventListener("click", (e) => {
                    e.preventDefault();
                    if (clone.href) loadTable(clone.href);
                });
            });
        }

        /* =========================================================
         * 1. TAMBAH SURAT
         * ========================================================= */

        async function setupAddKodeSurat(initialValue = "") {
            const jenisAdd = $("#jenis_surat_add");
            const kodeContainer = $("#kode-container-add");
            if (!jenisAdd || !kodeContainer) return;

            function renderKodeInput() {
                const val = (jenisAdd.value || "").trim().toLowerCase();

                const kodeSelect = document.getElementById(
                    "kode_surat_select_add"
                );
                const kodeInput = document.getElementById(
                    "kode_surat_input_add"
                );
                const kodePlaceholder = document.getElementById(
                    "kode_surat_placeholder_add"
                );
                const reqSpan = document.getElementById("kode-required-add");

                if (!kodeSelect || !kodeInput) return;

                // Reset semua ke hidden/disabled dulu
                kodeSelect.style.display = "none";
                kodeSelect.disabled = true;
                kodeSelect.required = false;
                kodeInput.style.display = "none";
                kodeInput.disabled = true;
                if (kodePlaceholder) kodePlaceholder.style.display = "none";
                if (reqSpan) reqSpan.style.display = "none";

                if (val === "keluar") {
                    kodeSelect.style.display = "";
                    kodeSelect.disabled = false;
                    kodeSelect.required = true;
                    if (initialValue) kodeSelect.value = initialValue;
                    if (reqSpan) reqSpan.style.display = "";
                    buildModalCustomSelect(kodeSelect);

                    // Begitu kode surat dipilih, regenerate nomor supaya
                    // formatnya langsung lengkap (NNN/KODE/INSTANSI/ROMAWI/TAHUN)
                    kodeSelect.removeEventListener(
                        "change",
                        kodeSelect._regenHandler
                    );
                    kodeSelect._regenHandler = async () => {
                        const noEl = document.getElementById("add_no_surat");
                        if (!noEl) return;
                        // Nomor sudah diketik manual → jangan ditimpa
                        if (noEl.dataset.manualEdit === "1") return;
                        const ctx = collectNomorContext("add");
                        const nomor = await generateNoSurat({
                            jenis: "Keluar",
                            ...ctx,
                        });
                        if (nomor) noEl.value = nomor;
                    };
                    kodeSelect.addEventListener(
                        "change",
                        kodeSelect._regenHandler
                    );
                } else if (val === "masuk") {
                    kodeInput.style.display = "";
                    kodeInput.disabled = false;
                    kodeInput.value = initialValue || "";
                } else {
                    if (kodePlaceholder) kodePlaceholder.style.display = "";
                }
            }

            jenisAdd.removeEventListener("change", jenisAdd._kodeHandler);
            jenisAdd._kodeHandler = renderKodeInput;
            jenisAdd.addEventListener("change", jenisAdd._kodeHandler);

            await renderKodeInput();

            // ── Auto-generate nomor, readonly, & instansi berdasarkan jenis ──
            async function applyNomorBehavior() {
                const noEl = document.getElementById("add_no_surat");
                const btnGen = document.getElementById("btnGenerateNoSuratAdd");
                const instansiEl = document.getElementById("add_instansi");
                if (!noEl) return;

                const val = (jenisAdd.value || "").trim().toLowerCase();

                if (val === "keluar") {
                    // Nomor auto-generate, TAPI tetap boleh diedit manual (flexibel).
                    // Sebelumnya field ini readonly — sekarang dibuka supaya user
                    // bisa mengetik/ubah nomor sendiri kalau perlu, sambil tetap
                    // menyediakan nilai otomatis + tombol Generate untuk regenerate.
                    noEl.readOnly = false;
                    noEl.classList.remove("bg-light");
                    noEl.placeholder = "Ketik manual atau klik Generate";
                    if (btnGen) btnGen.style.display = "";
                    // Instansi TIDAK di-auto-isi lagi — field ini untuk
                    // tujuan/dari surat, bukan nama lembaga pengirim, jadi
                    // tidak boleh ikut menentukan nomor induk surat.
                    // Generate nomor urut Keluar (sertakan kode/bulan/tahun
                    // yang sudah terisi agar formatnya langsung lengkap).
                    // Nomor melanjutkan otomatis dari nomor tertinggi yang sudah
                    // ada (mis. sudah ada 007 → generate berikutnya jadi 008),
                    // tapi hanya diisi otomatis kalau field masih kosong supaya
                    // tidak menimpa nomor yang sudah diketik manual oleh user.
                    if (!noEl.value.trim()) {
                        const ctx = collectNomorContext("add");
                        const nomor = await generateNoSurat({
                            jenis: "Keluar",
                            ...ctx,
                        });
                        if (nomor) noEl.value = nomor;
                    }
                } else if (val === "masuk") {
                    // Nomor manual
                    noEl.readOnly = false;
                    noEl.classList.remove("bg-light");
                    noEl.value = "";
                    noEl.placeholder = "Masukkan nomor surat";
                    if (btnGen) btnGen.style.display = "none";
                    // Instansi kosong untuk Masuk
                    if (instansiEl) instansiEl.value = "";
                } else {
                    // Belum pilih jenis
                    noEl.readOnly = false;
                    noEl.classList.remove("bg-light");
                    noEl.value = "";
                    noEl.placeholder = "Pilih jenis surat terlebih dahulu";
                    if (btnGen) btnGen.style.display = "none";
                }
            }

            jenisAdd.removeEventListener("change", jenisAdd._nomorHandler);
            jenisAdd._nomorHandler = applyNomorBehavior;
            jenisAdd.addEventListener("change", jenisAdd._nomorHandler);

            // Terapkan langsung untuk jenis yang sudah dipilih (edit atau re-open)
            await applyNomorBehavior();
        }

        function setupAddSuratForm() {
            const addForm = $("#addSuratForm");
            addForm?.addEventListener("submit", async (e) => {
                e.preventDefault();
                const fd = new FormData(addForm);
                const submitBtn = addForm.querySelector("[type='submit']");
                const orig = submitBtn?.innerHTML;

                validateFileInputs();
                hideFormAlert("#addSuratAlert");

                const no_surat = fd.get("no_surat")?.toString().trim() || "";
                const instansi = fd.get("instansi")?.toString().trim() || "";
                const tanggal_surat =
                    fd.get("tanggal_surat")?.toString().trim() || "";
                const jenis_surat =
                    fd.get("jenis_surat")?.toString().trim() || "";

                try {
                    const isDup = await checkDuplicateSurat({
                        no_surat,
                        instansi,
                        tanggal_surat,
                        jenis_surat,
                    });

                    if (isDup) {
                        showFormAlert(
                            "#addSuratAlert",
                            "Data dengan nomor surat, instansi, dan tanggal yang sama sudah ada.",
                            "warning"
                        );
                        $("#add_no_surat")?.focus();
                        return;
                    }

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;
                    }

                    const res = await safeFetch("/surat", {
                        method: "POST",
                        body: fd,
                    });
                    const data = await res.json();

                    if (data?.success) {
                        toast(
                            data.message || "Surat berhasil ditambahkan",
                            "success"
                        );
                        bootstrap.Modal.getInstance(
                            $("#addSuratModal")
                        )?.hide();
                        addForm.reset();
                        const fileInput =
                            addForm.querySelector('input[type="file"]');
                        if (fileInput) fileInput.value = "";
                        hideFormAlert("#addSuratAlert");
                        loadTable();
                    } else {
                        let msg =
                            data?.message ||
                            "Gagal menyimpan surat. Periksa input Anda.";

                        if (
                            data?.errors &&
                            data.errors.no_surat &&
                            data.errors.no_surat.length
                        ) {
                            msg = data.errors.no_surat[0];
                        }

                        showFormAlert("#addSuratAlert", msg, "danger");
                    }
                } catch (err) {
                    console.error("Error Tambah Surat:", err);
                    showFormAlert(
                        "#addSuratAlert",
                        `Terjadi kesalahan: ${err.message || "Server error"}`,
                        "danger"
                    );
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = orig;
                    }
                }
            });

            const addModalEl = $("#addSuratModal");
            if (addModalEl) {
                addModalEl.addEventListener("show.bs.modal", async () => {
                    const f = $("#addSuratForm");
                    if (f) {
                        f.reset();
                        const fileInput = f.querySelector('input[type="file"]');
                        if (fileInput) fileInput.value = "";
                        // instansi diatur oleh applyNomorBehavior berdasarkan jenis
                    }
                    hideFormAlert("#addSuratAlert");

                    // Form baru → nomor surat mulai dalam mode "auto" (belum
                    // diketik manual oleh user), supaya auto-generate & auto
                    // regenerate saat Bulan/Tahun berubah tetap jalan seperti biasa.
                    const addNoSuratEl = $("#add_no_surat");
                    if (addNoSuratEl) addNoSuratEl.dataset.manualEdit = "0";

                    try {
                        await setupAddKodeSurat(); // termasuk kode + auto-generate nomor
                    } catch (err) {
                        console.error("[surat] setupAddKodeSurat gagal:", err);
                    }

                    // Sinkronisasi bulan / tahun / tanggal
                    const now = new Date();
                    const addBulan = $("#add_bulan");
                    const addTahun = $("#add_tahun");
                    const addTanggal = $("#add_tanggal");
                    if (addBulan) addBulan.value = String(now.getMonth() + 1);
                    if (addTahun) addTahun.value = String(now.getFullYear());
                    syncDateFields(addTanggal, addBulan, addTahun);
                    attachNomorAutoRegen("add");

                    // Tombol Hari Ini
                    const btnHariIni = $("#btnHariIniAdd");
                    if (btnHariIni) {
                        btnHariIni.onclick = () => {
                            if (addTanggal) {
                                addTanggal.value = todayLocalISO();
                                addTanggal.dispatchEvent(new Event("change"));
                            }
                        };
                    }
                    // Tombol Bulan Ini
                    const btnBulanIni = $("#btnBulanIniAdd");
                    if (btnBulanIni) {
                        btnBulanIni.onclick = () => {
                            if (addBulan) {
                                addBulan.value = String(now.getMonth() + 1);
                                syncModalSelect(addBulan);
                                addBulan.dispatchEvent(new Event("change"));
                            }
                        };
                    }
                    // Tombol Tahun Ini
                    const btnTahunIni = $("#btnTahunIniAdd");
                    if (btnTahunIni) {
                        btnTahunIni.onclick = () => {
                            if (addTahun) {
                                addTahun.value = String(now.getFullYear());
                                addTahun.dispatchEvent(new Event("change"));
                            }
                        };
                    }

                    // Tombol Generate: re-generate untuk Surat Keluar
                    const btnGen = $("#btnGenerateNoSuratAdd");
                    if (btnGen) {
                        btnGen.onclick = async () => {
                            const jenis = (
                                $("#jenis_surat_add")?.value || "Keluar"
                            ).trim();
                            const ctx = collectNomorContext("add");
                            const nomor = await generateNoSurat({
                                jenis,
                                ...ctx,
                            });
                            if (nomor) {
                                const el = $("#add_no_surat");
                                if (el) {
                                    el.value = nomor;
                                    el.dispatchEvent(new Event("input"));
                                    // Klik Generate = permintaan eksplisit untuk
                                    // auto-generate lagi, jadi field kembali ke
                                    // mode "auto" (boleh ikut berubah kalau nanti
                                    // Bulan/Tahun/Kode diganti lagi).
                                    el.dataset.manualEdit = "0";
                                }
                            }
                        };
                    }

                    // Konversi semua <select> di modal ke custom dropdown
                    // (harus SETELAH setupAddKodeSurat selesai agar select
                    //  kode_surat yang mungkin visible sudah siap)
                    initModalSelects("addSuratModal");
                    // Sync label setelah value diset programatik (bulan, jenis)
                    syncAllModalSelects("addSuratModal");
                });
            }
        }

        /* =========================================================
         * 2. EDIT SURAT
         * ========================================================= */

        async function setupEditKodeSurat(s = {}, originalData = null) {
            const cont = $("#kode-container-edit");
            if (!cont) return;

            // ── Cegah race condition: batalkan render sebelumnya yang masih berjalan ──
            if (cont._kodeRendering) {
                cont._kodeAborted = true;
            }
            cont._kodeRendering = true;
            cont._kodeAborted = false;

            // Lepas event handler lama sebelum pasang yang baru
            const editJenis = $("#edit_jenis");
            if (editJenis && editJenis._kodeHandler) {
                editJenis.removeEventListener("change", editJenis._kodeHandler);
                editJenis._kodeHandler = null;
            }

            const jenis = (
                s?.jenis_surat ||
                editJenis?.value ||
                ""
            ).toLowerCase();

            // Sinkronkan visibilitas tombol Generate setiap kali jenis berubah
            const btnGenEdit = $("#btnGenerateNoSuratEdit");
            if (btnGenEdit) {
                btnGenEdit.style.display = jenis === "keluar" ? "" : "none";
            }

            // Placeholder Nomor Surat disamakan dengan form Tambah Surat:
            // ikut berubah sesuai jenis surat yang sedang dipilih.
            const noEditEl = document.getElementById("edit_no_surat");
            if (noEditEl) {
                noEditEl.placeholder =
                    jenis === "keluar"
                        ? "Ketik manual atau klik Generate"
                        : "Masukkan nomor surat";
            }

            // Nomor surat tidak diubah otomatis saat jenis berubah di mode edit
            // — user harus klik tombol generate secara manual jika ingin nomor baru

            if (jenis === "keluar") {
                const currentKode = s?.kode_surat || "";

                cont.innerHTML = `
            <label class="form-label">Kode Surat</label>
            <select
                id="kode_input_edit"
                name="kode_surat"
                class="form-select"
                required>
                <option value="">Memuat kode...</option>
            </select>
            <small class="text-muted">Pilih kode surat untuk surat keluar.</small>
        `;

                const sel = $("#kode_input_edit");
                if (!sel) {
                    cont._kodeRendering = false;
                    return;
                }

                sel.disabled = true;

                const kodeList = await fetchKodeList();

                // Batalkan jika sudah di-override oleh render berikutnya
                if (cont._kodeAborted) {
                    cont._kodeRendering = false;
                    return;
                }

                sel.innerHTML =
                    `<option value="">-- Pilih Kode Surat --</option>` +
                    kodeList
                        .map(
                            (k) => `
                <option
                    value="${k.kode}"
                    ${k.kode === currentKode ? "selected" : ""}>
                    ${k.kode} - ${k.description || "-"}
                </option>
            `
                        )
                        .join("");

                sel.disabled = false;
                buildModalCustomSelect(sel);

                // Begitu kode surat dipilih ulang, regenerate nomor supaya
                // formatnya langsung lengkap (NNN/KODE/INSTANSI/ROMAWI/TAHUN)
                sel.removeEventListener("change", sel._regenHandler);
                sel._regenHandler = async () => {
                    const noEl = document.getElementById("edit_no_surat");
                    if (!noEl) return;
                    // Nomor sudah diketik manual → jangan ditimpa
                    if (noEl.dataset.manualEdit === "1") return;
                    const ctx = collectNomorContext("edit");
                    const exclude_id =
                        document.getElementById("edit_id")?.value || "";
                    const nomor = await generateNoSurat({
                        jenis: "Keluar",
                        exclude_id,
                        ...ctx,
                    });
                    if (nomor) noEl.value = nomor;
                };
                sel.addEventListener("change", sel._regenHandler);
            } else {
                // Untuk surat MASUK:
                // - Jika data asli dari server memang Masuk → tampilkan kode_surat lama
                // - Jika user baru saja ganti dari Keluar ke Masuk → kosongkan (jangan bocorkan kode dropdown)
                let manualKode = "";

                const sourceData = originalData || s;
                if (
                    sourceData?.jenis_surat &&
                    sourceData.jenis_surat.toLowerCase() === "masuk"
                ) {
                    manualKode = sourceData?.kode_surat || "";
                }
                // Jika originalData ada tapi jenis aslinya Keluar, manualKode tetap "" ✓

                cont.innerHTML = `
            <label class="form-label">Kode Surat</label>
            <input
                type="text"
                id="kode_input_edit"
                name="kode_surat"
                class="form-control"
                value="${manualKode}"
                placeholder="Masukkan kode surat (opsional)">
            <small class="text-muted">
                Kode surat dapat diisi manual untuk surat masuk.
            </small>
        `;
            }

            cont._kodeRendering = false;

            // Pasang event handler baru, teruskan originalData agar selalu tahu data asli server
            if (editJenis) {
                editJenis._kodeHandler = async function () {
                    await setupEditKodeSurat(
                        { jenis_surat: this.value },
                        originalData || s // pertahankan data asli server
                    );
                };
                editJenis.addEventListener("change", editJenis._kodeHandler);
            }
        }

        async function editSurat(id) {
            try {
                const res = await safeFetch(`/surat/${id}`);
                const j = await res.json();
                if (!j?.success)
                    return toast("Gagal memuat data surat", "error");

                const s = j.surat;
                const editForm = $("#editSuratForm");

                hideFormAlert("#editSuratAlert");

                if (editForm) {
                    editForm
                        .querySelectorAll('input[name="hapus_file[]"]')
                        .forEach((input) => input.remove());

                    editForm.querySelectorAll(".file-item").forEach((item) => {
                        item.classList.remove(
                            "bg-danger-subtle",
                            "text-decoration-line-through"
                        );
                        const button = item.querySelector(
                            ".btn-delete-old-file"
                        );
                        if (button) {
                            button.classList.remove("btn-danger");
                            button.classList.add("btn-outline-danger");
                            const icon = button.querySelector("i");
                            if (icon) icon.className = "bi bi-trash";
                            button.title = "Tandai untuk dihapus";
                        }
                    });

                    const fileInput =
                        editForm.querySelector('input[type="file"]');
                    if (fileInput) fileInput.value = "";
                }

                $("#edit_id").value = s.id ?? "";
                $("#edit_no_surat").value = s.no_surat || "";
                // Nomor surat yang sudah tersimpan dianggap "manual" secara
                // default — tidak boleh ditimpa otomatis hanya karena user
                // mengubah Bulan/Tahun/Instansi. Kalau user memang mau nomor
                // baru, harus klik tombol Generate secara eksplisit.
                $("#edit_no_surat").dataset.manualEdit = "1";
                $("#edit_jenis").value = s.jenis_surat || "";
                // Pakai tanggal_surat_raw (format Y-m-d) karena input type="date"
                // hanya menerima format ISO, bukan format tampilan "d F Y".
                $("#edit_tanggal").value = s.tanggal_surat_raw || "";
                $("#edit_pengirim").value = s.pengirim || "";
                $("#edit_penerima").value = s.penerima || "";
                $("#edit_perihal").value = s.perihal || "";

                const instansiInput = $("#edit_instansi");
                if (instansiInput) {
                    instansiInput.value = s.instansi || "";
                }

                await setupEditKodeSurat(s);

                const fileContainer = $("#edit_file_list");
                if (fileContainer) {
                    fileContainer.innerHTML = "";

                    if (Array.isArray(s.files) && s.files.length) {
                        s.files.forEach((file) => {
                            const fileItem = document.createElement("div");
                            fileItem.className =
                                "d-flex align-items-center justify-content-between p-2 border rounded file-item mb-2";
                            fileItem.dataset.path = file.path;

                            fileItem.innerHTML = `
                            <span>
                                <i class="${getFileIcon(
                                file.name
                            )} me-2 fs-5"></i>
                                <a href="${file.url
                                }" target="_blank" class="text-decoration-none">${file.name
                                }</a>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-old-file" 
                                data-path="${file.path
                                }" title="Tandai untuk dihapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                            fileContainer.appendChild(fileItem);
                        });
                    } else {
                        fileContainer.innerHTML = `<p class="text-muted small mb-0">Belum ada file terlampir</p>`;
                    }
                }

                // Sinkronisasi bulan / tahun / tanggal (edit)
                const editBulan = $("#edit_bulan");
                const editTahun = $("#edit_tahun");
                const editTanggal = $("#edit_tanggal");
                // Isi bulan & tahun dari tanggal_surat yang sudah di-set
                if (editTanggal?.value) {
                    const d = new Date(editTanggal.value + "T00:00:00");
                    if (editBulan) editBulan.value = String(d.getMonth() + 1);
                    if (editTahun) editTahun.value = String(d.getFullYear());
                }
                syncDateFields(editTanggal, editBulan, editTahun);
                attachNomorAutoRegen("edit");

                // Tombol Hari Ini
                const btnHariIniEdit = $("#btnHariIniEdit");
                if (btnHariIniEdit) {
                    btnHariIniEdit.onclick = () => {
                        if (editTanggal) {
                            editTanggal.value = todayLocalISO();
                            editTanggal.dispatchEvent(new Event("change"));
                        }
                    };
                }
                // Tombol Bulan Ini
                const btnBulanIniEdit = $("#btnBulanIniEdit");
                if (btnBulanIniEdit) {
                    btnBulanIniEdit.onclick = () => {
                        if (editBulan) {
                            editBulan.value = String(new Date().getMonth() + 1);
                            syncModalSelect(editBulan);
                            editBulan.dispatchEvent(new Event("change"));
                        }
                    };
                }
                // Tombol Tahun Ini
                const btnTahunIniEdit = $("#btnTahunIniEdit");
                if (btnTahunIniEdit) {
                    btnTahunIniEdit.onclick = () => {
                        if (editTahun) {
                            editTahun.value = String(new Date().getFullYear());
                            editTahun.dispatchEvent(new Event("change"));
                        }
                    };
                }

                // Tombol Generate Nomor (edit) — hanya untuk Surat Keluar
                const btnGenEdit = $("#btnGenerateNoSuratEdit");
                if (btnGenEdit) {
                    const editJenisVal = ($("#edit_jenis")?.value || "").trim();
                    btnGenEdit.style.display =
                        editJenisVal.toLowerCase() === "keluar" ? "" : "none";

                    btnGenEdit.onclick = async () => {
                        const jenis = (
                            $("#edit_jenis")?.value || "Keluar"
                        ).trim();
                        const exclude_id = $("#edit_id")?.value || "";
                        const ctx = collectNomorContext("edit");
                        const nomor = await generateNoSurat({
                            jenis,
                            exclude_id,
                            ...ctx,
                        });
                        if (nomor) {
                            const el = $("#edit_no_surat");
                            if (el) {
                                el.value = nomor;
                                el.dispatchEvent(new Event("input"));
                                // Klik Generate = permintaan eksplisit untuk
                                // auto-generate lagi, jadi field kembali ke
                                // mode "auto".
                                el.dataset.manualEdit = "0";
                            }
                        }
                    };
                }

                getOrCreateModal("#editSuratModal")?.show();
                // Konversi semua <select> di modal edit ke custom dropdown
                // (harus SETELAH semua value di-set agar label sync dengan benar)
                initModalSelects("editSuratModal");
                syncAllModalSelects("editSuratModal");
            } catch (err) {
                console.error(err);
                toast("Gagal memuat data edit", "error");
            }
        }

        function setupEditSuratForm() {
            const editForm = $("#editSuratForm");
            editForm?.addEventListener("submit", async (e) => {
                e.preventDefault();
                const fd = new FormData(editForm);
                const id = fd.get("id") || $("#edit_id")?.value;
                if (!id) return toast("ID surat tidak ditemukan", "error");

                validateFileInputs();
                hideFormAlert("#editSuratAlert");

                const submitBtn = editForm.querySelector("[type='submit']");
                const orig = submitBtn?.innerHTML;

                const no_surat = fd.get("no_surat")?.toString().trim() || "";
                const instansi = fd.get("instansi")?.toString().trim() || "";
                const tanggal_surat =
                    fd.get("tanggal_surat")?.toString().trim() || "";
                const jenis_surat =
                    fd.get("jenis_surat")?.toString().trim() || "";

                try {
                    const isDup = await checkDuplicateSurat({
                        no_surat,
                        instansi,
                        tanggal_surat,
                        jenis_surat,
                        exclude_id: id,
                    });

                    if (isDup) {
                        showFormAlert(
                            "#editSuratAlert",
                            "Data dengan nomor surat, instansi, dan tanggal yang sama sudah ada.",
                            "warning"
                        );
                        $("#edit_no_surat")?.focus();
                        return;
                    }

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;
                    }

                    const jenisSurat = fd.get("jenis_surat");

                    // Selalu sinkronkan kode_surat dari elemen yang sedang tampil di DOM,
                    // baik surat Masuk (input manual) maupun Keluar (select dropdown).
                    // Ini mencegah nilai lama dari elemen yang sudah di-replace ikut terkirim.
                    const kodeAktif =
                        document.getElementById("kode_input_edit")?.value || "";
                    fd.delete("kode_surat");
                    fd.append("kode_surat", kodeAktif);

                    fd.append("_method", "PUT");

                    const res = await safeFetch(`/surat/${id}`, {
                        method: "POST",
                        body: fd,
                    });
                    const data = await res.json();

                    if (data?.success) {
                        toast(
                            data.message || "Surat berhasil diperbarui",
                            "success"
                        );

                        setTimeout(() => {
                            bootstrap.Modal.getInstance(
                                $("#editSuratModal")
                            )?.hide();
                            hideFormAlert("#editSuratAlert");
                            loadTable();
                        }, 400);
                    } else {
                        let msg =
                            data?.message ||
                            "Gagal memperbarui surat. Periksa input Anda.";

                        if (
                            data?.errors &&
                            data.errors.no_surat &&
                            data.errors.no_surat.length
                        ) {
                            msg = data.errors.no_surat[0];
                        }

                        showFormAlert("#editSuratAlert", msg, "danger");
                    }
                } catch (err) {
                    console.error("Error Edit Surat:", err);
                    showFormAlert(
                        "#editSuratAlert",
                        `Terjadi kesalahan: ${err.message || "Server error"}`,
                        "danger"
                    );
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = orig;
                    }
                }
            });
        }

        /* =========================================================
         * 3. HAPUS FILE & HAPUS SURAT
         * ========================================================= */

        function setupDeleteFileHandler() {
            document.addEventListener("click", function (e) {
                const button = e.target.closest(".btn-delete-old-file");
                if (!button) return;

                e.preventDefault();
                const filePath = button.dataset.path;
                const fileItem = button.closest(".file-item");
                const form = $("#editSuratForm");

                if (!filePath || !fileItem || !form) return;

                let hiddenInput = form.querySelector(
                    `input[name="hapus_file[]"][value="${filePath}"]`
                );

                const name = fileItem.querySelector("a")?.textContent || "";

                if (fileItem.classList.contains("bg-danger-subtle")) {
                    fileItem.classList.remove(
                        "bg-danger-subtle",
                        "text-decoration-line-through"
                    );
                    button.classList.remove("btn-danger");
                    button.classList.add("btn-outline-danger");
                    const icon = button.querySelector("i");
                    if (icon) icon.className = "bi bi-trash";
                    button.title = "Tandai untuk dihapus";
                    if (hiddenInput) hiddenInput.remove();
                    toast(`Penghapusan "${name}" dibatalkan.`, "info");
                } else {
                    fileItem.classList.add(
                        "bg-danger-subtle",
                        "text-decoration-line-through"
                    );
                    button.classList.remove("btn-outline-danger");
                    button.classList.add("btn-danger");
                    const icon = button.querySelector("i");
                    if (icon) icon.className = "bi bi-arrow-counterclockwise";
                    button.title = "Batalkan Penghapusan";

                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "hapus_file[]";
                    input.value = filePath;
                    form.appendChild(input);

                    toast(
                        `"${name}" ditandai untuk dihapus saat disimpan.`,
                        "warning"
                    );
                }
            });
        }

        function setupDeleteSurat() {
            const form = $("#deleteSuratForm");
            form?.addEventListener("submit", async (e) => {
                e.preventDefault();
                const id = $("#deleteSuratId")?.value;
                if (!id) return toast("ID surat tidak ditemukan", "error");

                const submitBtn = form.querySelector("[type='submit']");
                const orig = submitBtn?.innerHTML;

                try {
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...`;
                    }

                    const res = await safeFetch(`/surat/${id}`, {
                        method: "DELETE",
                    });
                    const data = await res.json();

                    if (data?.success) {
                        toast(
                            data.message || "Surat berhasil dihapus",
                            "success"
                        );
                        bootstrap.Modal.getInstance(
                            $("#deleteSuratModal")
                        )?.hide();
                        loadTable();
                    } else {
                        toast(
                            data?.message || "Gagal menghapus surat",
                            "error"
                        );
                    }
                } catch (err) {
                    console.error("Error Hapus Surat:", err);
                    toast(
                        `Terjadi kesalahan: ${err.message || "Server error"}`,
                        "error"
                    );
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = orig;
                    }
                }
            });
        }

        /* =========================================================
         * 4. DETAIL SURAT + PREVIEW/PRINT + DOWNLOAD MULTIPLE
         * ========================================================= */

        function updateSelectedInfo() {
            const all = $$("#view_file .file-select-check");
            const checked = all.filter((c) => c.checked);
            const lampiranCount = $("#lampiran_count");

            if (lampiranCount) {
                lampiranCount.textContent = `${all.length} file, ${checked.length} dipilih`;
            }

            const btnPrint = $("#btnPrintSelected");
            const btnDownloadSelected = $("#btnDownloadSelected");

            if (btnPrint) {
                btnPrint.disabled = checked.length !== 1;
            }
            if (btnDownloadSelected) {
                btnDownloadSelected.disabled = checked.length === 0;
            }
        }

        // Helper untuk request ZIP ke backend
        function submitZipDownload(paths) {
            if (!paths.length) {
                toast("Tidak ada file lampiran yang bisa diunduh.", "warning");
                return;
            }
            if (!currentSuratId) {
                toast("ID surat tidak diketahui.", "error");
                return;
            }

            const form = document.createElement("form");
            form.method = "POST";
            form.action = "/surat/download-multiple"; // route ke SuratController@downloadMultiple
            form.style.display = "none";

            // CSRF
            const token = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");
            if (token) {
                const tokenInput = document.createElement("input");
                tokenInput.type = "hidden";
                tokenInput.name = "_token";
                tokenInput.value = token;
                form.appendChild(tokenInput);
            }

            // surat_id → untuk penamaan ZIP di controller
            const idInput = document.createElement("input");
            idInput.type = "hidden";
            idInput.name = "surat_id";
            idInput.value = currentSuratId;
            form.appendChild(idInput);

            // paths[]
            paths.forEach((p) => {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "paths[]";
                input.value = p;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            setTimeout(() => form.remove(), 2000);
        }

        async function viewSurat(id) {
            try {
                currentSuratId = id;

                const res = await safeFetch(`/surat/${id}`);
                const j = await res.json();
                if (!j?.success)
                    return toast("Gagal memuat detail surat", "error");

                const s = j.surat;

                /* ===============================
                 * INFORMASI SURAT
                 * =============================== */

                $("#view_no_surat").textContent = s.no_surat || "-";

                $("#view_kode").textContent = s.kode_keterangan
                    ? `${s.kode_surat} - ${s.kode_keterangan}`
                    : s.kode_surat || "-";

                const jenisEl = $("#view_jenis");

                if (jenisEl) {
                    jenisEl.innerHTML =
                        s.jenis_surat === "Masuk"
                            ? `<span class="badge bg-info text-white">
                    <i class="bi bi-box-arrow-in-down me-1"></i>
                    Masuk
               </span>`
                            : `<span class="badge bg-success text-white">
                    <i class="bi bi-box-arrow-up-right me-1"></i>
                    Keluar
               </span>`;
                }

                $("#view_tanggal").textContent = s.tanggal_surat || "-";

                $("#view_instansi").textContent = s.instansi || "-";

                $("#view_pengirim").textContent = s.pengirim || "-";

                $("#view_penerima").textContent = s.penerima || "-";

                $("#view_perihal").textContent = s.perihal || "-";

                const fileContainer = $("#view_file");

                /* ===============================
                 * INFORMASI ARSIP
                 * =============================== */

                $("#view_created_by").textContent = s.created_by || "-";

                $("#view_created_at").textContent = s.created_at || "-";

                $("#view_updated_by").textContent = s.updated_by || "-";

                $("#view_updated_at").textContent = s.updated_at || "-";

                const status = $("#view_status");

                if (status) {
                    if (s.status === "Pernah Diubah") {
                        status.innerHTML = `
            <span class="badge bg-warning text-dark">
                <i class="bi bi-pencil-square me-1"></i>
                Pernah Diubah
            </span>
        `;
                    } else {
                        status.innerHTML = `
            <span class="badge bg-success">
                <i class="bi bi-check-circle me-1"></i>
                Belum Pernah Diubah
            </span>
        `;
                    }
                }

                if (fileContainer) {
                    fileContainer.innerHTML = "";
                    if (Array.isArray(s.files) && s.files.length) {
                        s.files.forEach((file) => {
                            const item = document.createElement("div");
                            item.className =
                                "list-group-item d-flex justify-content-between align-items-center";

                            // 🔽 tombol download per-file pakai btn-direct-download (bukan target="_blank")
                            item.innerHTML = `
                            <div class="d-flex align-items-center">
                                <input type="checkbox"
                                       class="form-check-input me-2 file-select-check"
                                       data-path="${file.path}"
                                       data-url="${file.url}">
                                <i class="${getFileIcon(
                                file.name
                            )} me-2 fs-5"></i>
                                <span class="text-break">${file.name}</span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button"
                                        class="btn btn-outline-primary btn-preview-file"
                                        data-url="${file.url}"
                                        title="Pratinjau">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="${file.url}"
                                   class="btn btn-outline-success btn-direct-download"
                                   data-filename="${file.name}"
                                   title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        `;
                            fileContainer.appendChild(item);
                        });
                    } else {
                        fileContainer.innerHTML = `
                        <div class="list-group-item text-muted small">
                            Belum ada file terlampir.
                        </div>`;
                    }
                }

                updateSelectedInfo();

                getOrCreateModal("#viewSuratModal")?.show();
            } catch (err) {
                console.error(err);
                toast("Gagal menampilkan surat", "error");
            }
        }

        function openPreview(url) {
            if (!url) return;

            const body = $("#filePreviewBody");
            const spinner = $("#file-loading-spinner");
            const dlLink = $("#previewDownloadLink");

            // Reset state
            if (body) body.innerHTML = "";
            if (spinner) spinner.style.display = "flex";

            if (dlLink) {
                dlLink.href = url;
                const filename = getFilenameFromUrl(url, "lampiran");
                dlLink.setAttribute("download", filename);
                const label = $("#preview_filename_label");
                if (label) label.textContent = filename;
            }

            // Ambil ekstensi (abaikan query string)
            const ext = url.split("?")[0].split(".").pop().toLowerCase();

            let timerId;
            const hideSpinner = () => {
                clearTimeout(timerId);
                if (spinner) spinner.style.display = "none";
            };
            // Fallback: paksa hide spinner setelah 12 detik
            timerId = setTimeout(hideSpinner, 12000);

            let content;

            if (ext === "pdf") {
                // <object> lebih andal dari <iframe> untuk PDF inline di Firefox/Chrome
                content = document.createElement("div");
                content.style.cssText = "width:100%;height:80vh;";
                content.innerHTML = `
                    <object data="${url}" type="application/pdf"
                            style="width:100%;height:100%;border:none;">
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
                            <p class="mt-2">Browser tidak mendukung pratinjau PDF.</p>
                        </div>
                    </object>
                    <div class="text-center py-2 border-top bg-light">
                        <a href="${url}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Buka di Tab Baru
                        </a>
                    </div>`;
                // Sembunyikan spinner setelah 2 detik (object tidak punya event onload)
                setTimeout(hideSpinner, 2000);
            } else if (["jpg", "jpeg", "png"].includes(ext)) {
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
                            <a href="${url}" target="_blank" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Buka di Tab Baru
                            </a>
                        </div>`;
                };
                content.src = url;
            } else {
                // Word, dsb. — tidak bisa di-preview langsung
                hideSpinner();
                content = document.createElement("div");
                content.className = "p-4 text-center";
                content.innerHTML = `
                    <i class="bi bi-file-earmark-word fs-1 text-primary"></i>
                    <p class="mt-2 text-muted">Pratinjau tidak tersedia untuk format <strong>.${ext}</strong>.</p>
                    <a href="${url}" target="_blank" class="btn btn-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Buka / Download File
                    </a>`;
            }

            if (body) body.appendChild(content);
            getOrCreateModal("#filePreviewModal")?.show();
        }

        function setupPreviewHandlers() {
            // perubahan ceklis di area lampiran
            $("#viewSuratModal")?.addEventListener("change", (e) => {
                if (e.target.closest(".file-select-check")) {
                    updateSelectedInfo();
                }
            });

            // preview per-file (tombol mata)
            document.addEventListener("click", (e) => {
                const previewBtn = e.target.closest(".btn-preview-file");
                if (!previewBtn) return;
                const url = previewBtn.dataset.url;
                if (!url) return;
                openPreview(url);
            });

            // download per-file (icon download di list)
            document.addEventListener("click", (e) => {
                const btn = e.target.closest(".btn-direct-download");
                if (!btn) return;

                e.preventDefault();

                const url = btn.getAttribute("href");
                if (!url) {
                    toast("URL file tidak ditemukan.", "error");
                    return;
                }

                let filename = btn.dataset.filename || "lampiran";
                if (!filename) {
                    filename = getFilenameFromUrl(url, "lampiran");
                }

                const a = document.createElement("a");
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
            });

            // tombol Print Terpilih
            $("#btnPrintSelected")?.addEventListener("click", (e) => {
                e.preventDefault();
                const checks = $$("#view_file .file-select-check:checked");
                if (checks.length !== 1) {
                    toast(
                        "Pilih tepat satu file yang akan diprint.",
                        "warning"
                    );
                    return;
                }
                const url = checks[0].dataset.url;
                if (!url) return;

                const w = window.open(url, "_blank");
                if (w) {
                    w.addEventListener("load", () => {
                        try {
                            w.print();
                        } catch (_) { }
                    });
                }
            });

            // tombol Download Terpilih
            $("#btnDownloadSelected")?.addEventListener("click", (e) => {
                e.preventDefault();
                const checks = $$("#view_file .file-select-check:checked");
                if (!checks.length) {
                    toast("Pilih minimal satu file lampiran.", "warning");
                    return;
                }

                // kalau cuma 1 → paksa download file itu
                if (checks.length === 1) {
                    const chk = checks[0];
                    const url = chk.dataset.url;
                    if (!url) {
                        toast("URL file tidak ditemukan.", "error");
                        return;
                    }

                    let filename = "lampiran";
                    const item = chk.closest(".list-group-item");
                    const nameSpan = item?.querySelector(".text-break");
                    if (nameSpan && nameSpan.textContent.trim()) {
                        filename = nameSpan.textContent.trim();
                    } else {
                        filename = getFilenameFromUrl(url, filename);
                    }

                    const a = document.createElement("a");
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    return;
                }

                // lebih dari 1 → ZIP
                const paths = checks
                    .map((chk) => chk.dataset.path)
                    .filter((p) => typeof p === "string" && p.length > 0);

                submitZipDownload(paths);
            });

            // tombol Download Semua
            $("#btnDownloadAll")?.addEventListener("click", (e) => {
                e.preventDefault();
                const allChecks = $$("#view_file .file-select-check");

                if (!allChecks.length) {
                    toast("Tidak ada file lampiran.", "warning");
                    return;
                }

                const paths = allChecks
                    .map((chk) => chk.dataset.path)
                    .filter((p) => typeof p === "string" && p.length > 0);

                submitZipDownload(paths);
            });
        }

        /* =========================================================
         * SANITASI INPUT (alphanumeric)
         * Field teks (instansi, pengirim, penerima) hanya boleh
         * huruf/angka/spasi. Perihal boleh tambahan tanda kurung ().
         * Nomor & Kode Surat boleh tambahan / - .
         * Guard ini realtime; validasi sebenarnya tetap di backend
         * (StoreSuratRequest/UpdateSuratRequest).
         * ========================================================= */

        // Karakter yang TIDAK diizinkan (untuk dibuang saat mengetik).
        const RE_TEXT_STRIP = /[^A-Za-z0-9 ]+/g; // huruf, angka, spasi
        const RE_PERIHAL_STRIP = /[^A-Za-z0-9 ()'.,:/\\&]+/g; // huruf, angka, spasi, ( ) ' . , : / \ &
        const RE_IDENT_STRIP = /[^A-Za-z0-9 .\/-]+/g; // + / - .

        const IDENT_FIELDS = new Set(["no_surat", "kode_surat"]);
        const PERIHAL_FIELDS = new Set(["perihal"]);
        const TEXT_FIELDS = new Set([
            "instansi",
            "pengirim",
            "penerima",
        ]);

        function sanitizeField(el) {
            if (!el || (el.tagName !== "INPUT" && el.tagName !== "TEXTAREA")) {
                return;
            }

            const name = el.name;
            let re = null;
            if (IDENT_FIELDS.has(name)) re = RE_IDENT_STRIP;
            else if (PERIHAL_FIELDS.has(name)) re = RE_PERIHAL_STRIP;
            else if (TEXT_FIELDS.has(name)) re = RE_TEXT_STRIP;
            if (!re) return;

            const before = el.value;
            const after = before.replace(re, "");
            if (before === after) return;

            // Pertahankan posisi kursor semirip mungkin setelah karakter dibuang.
            const start = el.selectionStart ?? after.length;
            const removed = before.length - after.length;
            el.value = after;
            const pos = Math.max(0, start - removed);
            try {
                el.setSelectionRange(pos, pos);
            } catch (_) { }
        }

        function setupAlphanumericGuards() {
            ["#addSuratForm", "#editSuratForm"].forEach((sel) => {
                const form = $(sel);
                if (!form) return;
                // Delegasi: menangkap juga input Kode Surat yang dirender ulang.
                form.addEventListener("input", (e) => sanitizeField(e.target));
            });
        }

        /* =========================================================
         * INIT
         * ========================================================= */

        document.addEventListener("DOMContentLoaded", () => {
            initFilters();
            // CATATAN: sengaja TIDAK memanggil loadTable() di sini.
            // Tabel awal sudah dirender lengkap oleh server (Blade,
            // sudah termasuk filter dari query string) saat halaman
            // dimuat. Memanggil loadTable() lagi di sini hanya
            // mengulang fetch yang sama persis via AJAX, menyebabkan:
            //   1) "flick" — tabel yang sudah benar sempat diganti lagi
            //      dengan hasil fetch (padahal isinya sama).
            //   2) kalau fetch itu gagal, tabel yang tadinya sudah
            //      benar malah ditimpa pesan error, padahal sebenarnya
            //      tidak ada masalah dengan datanya.
            // loadTable() tetap dipanggil normal untuk interaksi
            // berikutnya (search, filter, pagination, reset, dll).
            rebindRowActionButtons();

            setupAddSuratForm();
            setupEditSuratForm();
            setupDeleteFileHandler();
            setupDeleteSurat();
            setupPreviewHandlers();
            setupAlphanumericGuards();

            // Expose API publik di bawah namespace SuratApp supaya tidak
            // bentrok dengan fungsi bernama sama di file JS lain.
            // Gunakan: SuratApp.loadTable(), SuratApp.viewSurat(id), SuratApp.editSurat(id)
            window.SuratApp = {
                loadTable,
                loadSuratTable: loadTable, // alias untuk backward-compat
                viewSurat,
                editSurat,
            };
        });
    })(); // ⬅️ penutup IIFE utama
} // ⬅️ penutup guard `if (!window.SuratApp)`