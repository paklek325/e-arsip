/**
 * kode.js
 * ──────────────────────────────────────────────────────────
 * MENU  : Master Kode (/kode)
 * FILE  : resources/js/kode.js
 * SCOPE : Halaman kode saja (guard: #page-kode)
 */
(() => {
    "use strict";

    const toast    = (msg, type = "info") => window.AppToast(msg, type);
    const debounce = window.debounce;

    document.addEventListener("DOMContentLoaded", () => {
        if (!document.querySelector("#page-kode")) return;

        const metaBase = document
            .querySelector('meta[name="base-url"]')
            ?.getAttribute("content")
            .replace(/\/$/, "") ?? "";
        // Gunakan origin browser agar selalu cocok di hosting, abaikan APP_URL
        const baseUrl = window.location.origin === new URL(metaBase || window.location.href).origin
            ? metaBase
            : window.location.origin;

        const $ = (s) => document.querySelector(s);
        const $$ = (s) => Array.from(document.querySelectorAll(s));

        const filterForm     = $("#filter-form");
        const searchInput    = $("#search");
        const btnReset       = $("#btnReset");
        const tableContainer = $("#tableContainer");

        if (!tableContainer) return;

        // ── Validasi form ─────────────────────────────────────────────────
        const clearValidationErrors = () => {
            $$(".invalid-feedback").forEach((el) => el.remove());
            $$(".is-invalid").forEach((el) => el.classList.remove("is-invalid"));
        };

        const displayValidationErrors = (errors, prefix) => {
            Object.keys(errors).forEach((key) => {
                const input = $(`#${prefix}_${key}`);
                if (!input) return;
                input.classList.add("is-invalid");
                if (!input.nextElementSibling?.classList.contains("invalid-feedback")) {
                    const div = document.createElement("div");
                    div.className = "invalid-feedback";
                    div.textContent = errors[key][0];
                    input.after(div);
                }
            });
            toast("Input tidak valid. Periksa form.", "error");
        };

        $("#addKodeModal")?.addEventListener("hidden.bs.modal", clearValidationErrors);
        $("#editKodeModal")?.addEventListener("hidden.bs.modal", clearValidationErrors);

        // ── Loader tabel (AJAX) ───────────────────────────────────────────
        async function loadTable(url = `${baseUrl}/kode/list`) {
            const params = filterForm
                ? new URLSearchParams(new FormData(filterForm)).toString()
                : "";
            const fetchUrl = params ? `${url}?${params}` : url;

            try {
                const res = await window.safeFetch(fetchUrl, {
                    headers: { Accept: "text/html,application/xhtml+xml" },
                });

                const ctype = res.headers.get("content-type") || "";
                const text  = await res.text();

                if (ctype.includes("application/json")) {
                    tableContainer.innerHTML = `<div class="alert alert-warning">Response server berbentuk JSON — periksa endpoint.</div>`;
                    return;
                }
                if (text.includes('name="password"') && text.toLowerCase().includes("login")) {
                    tableContainer.innerHTML = `<div class="alert alert-warning">Session mungkin kadaluwarsa. Silakan login ulang.</div>`;
                    return;
                }

                tableContainer.innerHTML = text;
                bindTableEvents();
            } catch (err) {
                console.error("Gagal memuat data:", err);
                tableContainer.innerHTML = `<div class="alert alert-danger">Gagal memuat tabel.</div>`;
                toast("Gagal memuat data tabel", "error");
            }
        }

        // ── Binding event tabel (pagination + aksi baris) ─────────────────
        function bindTableEvents() {
            $$(".pagination a").forEach((a) => {
                a.addEventListener("click", (e) => {
                    e.preventDefault();
                    if (a.href) loadTable(a.href);
                });
            });
            $$(".btn-detail").forEach((b) =>
                b.addEventListener("click", () => openDetailModal(b.dataset.id))
            );
            $$(".btn-edit").forEach((b) =>
                b.addEventListener("click", () => openEditModal(b.dataset.id))
            );
            $$(".btn-delete").forEach((b) =>
                b.addEventListener("click", () => openDeleteModal(b.dataset.id, b.dataset.kode))
            );
        }

        // ── Filter & search ───────────────────────────────────────────────
        searchInput?.addEventListener("input", debounce(() => loadTable(), 400));

        btnReset?.addEventListener("click", (e) => {
            e.preventDefault();
            filterForm?.reset();
            loadTable();
            toast("Filter direset", "info");
        });

        // ── Cek duplikat kode (real-time) ─────────────────────────────────
        async function checkDuplicateKode(value, mode = "add") {
            const id  = mode === "edit" ? $("#edit_id")?.value : "";
            const url = `${baseUrl}/kode/check-duplicate?kode=${encodeURIComponent(value)}${id ? `&id=${id}` : ""}`;
            try {
                const res  = await window.safeFetch(url);
                const data = await res.json();
                return data.exists;
            } catch (err) {
                console.error("Error checking duplicate:", err);
                return false;
            }
        }

        function bindDuplicateChecker() {
            const addInput  = $("#add_kode");
            const editInput = $("#edit_kode");

            const checkAndMark = (input, mode) =>
                debounce(async () => {
                    const val = input.value.trim();
                    clearValidationErrors();
                    if (!val) return;
                    const exists = await checkDuplicateKode(val, mode);
                    if (exists) {
                        input.classList.add("is-invalid");
                        if (!input.nextElementSibling?.classList.contains("invalid-feedback")) {
                            const err = document.createElement("div");
                            err.className = "invalid-feedback";
                            err.textContent = "Kode sudah digunakan.";
                            input.after(err);
                        }
                    } else {
                        input.classList.remove("is-invalid");
                    }
                }, 400);

            addInput?.addEventListener("input", checkAndMark(addInput, "add"));
            editInput?.addEventListener("input", checkAndMark(editInput, "edit"));
        }

        // ── Tambah Kode ───────────────────────────────────────────────────
        $("#formAddKode")?.addEventListener("submit", async (e) => {
            e.preventDefault();
            clearValidationErrors();

            const kodeInput = $("#add_kode");
            const kodeVal   = kodeInput?.value.trim() ?? "";

            if (kodeVal && await checkDuplicateKode(kodeVal, "add")) {
                kodeInput.classList.add("is-invalid");
                if (!kodeInput.nextElementSibling?.classList.contains("invalid-feedback")) {
                    const err = document.createElement("div");
                    err.className = "invalid-feedback";
                    err.textContent = "Kode telah tersedia.";
                    kodeInput.after(err);
                }
                toast("Kode telah tersedia.", "error");
                return;
            }

            try {
                const res = await window.safeFetch(`${baseUrl}/kode`, {
                    method: "POST",
                    body: new FormData(e.target),
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance($("#addKodeModal"))?.hide();
                    e.target.reset();
                    loadTable();
                    toast(data.message || "Kode berhasil ditambahkan", "success");
                } else if (data.errors) {
                    displayValidationErrors(data.errors, "add");
                } else {
                    toast(data.message || "Gagal menambahkan kode.", "error");
                }
            } catch (err) {
                console.error(err);
                toast(err.message || "Terjadi kesalahan saat menambah kode", "error");
            }
        });

        // ── Edit Kode ─────────────────────────────────────────────────────
        async function openEditModal(id) {
            clearValidationErrors();
            try {
                const res  = await window.safeFetch(`${baseUrl}/kode/${id}`);
                const data = await res.json();
                $("#edit_id").value          = data.id_kode;
                $("#edit_kode").value        = data.kode;
                $("#edit_description").value = data.description || "";
                new bootstrap.Modal($("#editKodeModal")).show();
            } catch (err) {
                console.error(err);
                toast("Gagal membuka data kode", "error");
            }
        }

        $("#formEditKode")?.addEventListener("submit", async (e) => {
            e.preventDefault();
            clearValidationErrors();

            const id        = $("#edit_id").value;
            const kodeInput = $("#edit_kode");
            const kodeVal   = kodeInput?.value.trim() ?? "";

            if (kodeVal && await checkDuplicateKode(kodeVal, "edit")) {
                kodeInput.classList.add("is-invalid");
                if (!kodeInput.nextElementSibling?.classList.contains("invalid-feedback")) {
                    const err = document.createElement("div");
                    err.className = "invalid-feedback";
                    err.textContent = "Kode telah tersedia.";
                    kodeInput.after(err);
                }
                toast("Kode telah tersedia.", "error");
                return;
            }

            const fd = new FormData(e.target);
            fd.append("_method", "PUT");

            try {
                const res = await window.safeFetch(`${baseUrl}/kode/${id}`, {
                    method: "POST",
                    body: fd,
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance($("#editKodeModal"))?.hide();
                    loadTable();
                    toast(data.message || "Kode berhasil diperbarui", "success");
                } else if (data.errors) {
                    displayValidationErrors(data.errors, "edit");
                } else {
                    toast(data.message || "Gagal memperbarui kode.", "error");
                }
            } catch (err) {
                console.error(err);
                toast(err.message || "Terjadi kesalahan saat update kode", "error");
            }
        });

        // ── Hapus Kode ────────────────────────────────────────────────────
        function openDeleteModal(id, kode) {
            $("#delete_id").value              = id;
            $("#delete_kode_label").textContent = kode;
            new bootstrap.Modal($("#deleteKodeModal")).show();
        }

        $("#formDeleteKode")?.addEventListener("submit", async (e) => {
            e.preventDefault();
            const id = $("#delete_id").value;
            const fd = new FormData();
            fd.append("_method", "DELETE");
            try {
                const res = await window.safeFetch(`${baseUrl}/kode/${id}`, { method: "POST", body: fd });
                const data = await res.json().catch(() => ({}));
                if (data.success) {
                    bootstrap.Modal.getInstance($("#deleteKodeModal"))?.hide();
                    loadTable();
                    toast(data.message || "Kode berhasil dihapus", "success");
                } else {
                    toast(data.message || "Gagal menghapus kode.", "error");
                }
            } catch (err) {
                console.error(err);
                toast(err.message || "Terjadi kesalahan saat hapus kode", "error");
            }
        });

        // ── Detail Kode ───────────────────────────────────────────────────
        async function openDetailModal(id) {
            try {
                const res  = await window.safeFetch(`${baseUrl}/kode/${id}`);
                const data = await res.json();
                $("#detail_kode").textContent        = data.kode;
                $("#detail_description").textContent = data.description || "-";
                new bootstrap.Modal($("#detailKodeModal")).show();
            } catch (err) {
                console.error(err);
                toast("Gagal memuat detail kode", "error");
            }
        }

        // ── Init ──────────────────────────────────────────────────────────
        loadTable();
        bindDuplicateChecker();
    });
})();
