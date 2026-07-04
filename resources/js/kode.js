(() => {
    "use strict";

    const toast = (message, type = "info") => window.AppToast(message, type);

    const debounce = (fn, delay = 400) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    };

    // ==============================
    // 🔁 Init after DOM ready
    // ==============================
    document.addEventListener("DOMContentLoaded", () => {
        // Jalankan hanya jika halaman kode aktif
        if (!document.querySelector("#page-kode")) return;

        const meta = (name) => document.querySelector(`meta[name="${name}"]`);
        const baseMeta = meta("base-url");
        const csrfMeta = meta("csrf-token");

        if (!baseMeta) {
            console.error("meta[name='base-url'] tidak ditemukan");
            toast("meta base-url tidak ditemukan", "error");
            return;
        }

        const baseUrl = baseMeta.getAttribute("content").replace(/\/$/, "");
        const csrf = csrfMeta?.getAttribute("content") ?? "";

        // DEFINISI GLOBAL SELECTORS UNTUK SCOPE INI
        const $ = (s) => document.querySelector(s);
        const $$ = (s) => Array.from(document.querySelectorAll(s));

        // ==============================
        // 🔔 Helper: Validation
        // ==============================

        // Fungsi untuk membersihkan semua pesan error dari form
        const clearValidationErrors = () => {
            $$(".invalid-feedback").forEach((el) => el.remove());
            $$(".is-invalid").forEach((el) =>
                el.classList.remove("is-invalid")
            );
        };

        // Fungsi untuk menampilkan pesan error di form (untuk respons 422)
        const displayValidationErrors = (errors, prefix) => {
            Object.keys(errors).forEach((key) => {
                const input = $(`#${prefix}_${key}`); // Misal: #add_kode atau #edit_kode
                if (input) {
                    input.classList.add("is-invalid");

                    // Cek apakah pesan error sudah ada
                    if (
                        !input.nextElementSibling ||
                        !input.nextElementSibling.classList.contains(
                            "invalid-feedback"
                        )
                    ) {
                        const errorDiv = document.createElement("div");
                        errorDiv.className = "invalid-feedback";
                        errorDiv.textContent = errors[key][0]; // Ambil pesan error pertama dari Controller
                        input.after(errorDiv);
                    }
                }
            });
            // Toast notifikasi saat validasi gagal
            toast("❌ Input tidak valid. Periksa form.", "error");
        };

        const filterForm = $("#filter-form"); // boleh null
        const searchInput = $("#search");
        const btnReset = $("#btnReset");
        const tableContainer = $("#tableContainer");

        // Pastikan modal ditutup/dibuka untuk membersihkan error
        $("#addKodeModal")?.addEventListener(
            "hidden.bs.modal",
            clearValidationErrors
        );
        $("#editKodeModal")?.addEventListener(
            "hidden.bs.modal",
            clearValidationErrors
        );

        if (!tableContainer) {
            console.error("#tableContainer tidak ditemukan");
            toast("container tabel tidak ditemukan", "error");
            return;
        }

        // ==============================
        // 🔄 Loader tabel (AJAX)
        // ==============================
        async function loadTable(url = `${baseUrl}/kode/list`) {
            const params = filterForm
                ? new URLSearchParams(new FormData(filterForm)).toString()
                : "";
            const fetchUrl = params ? `${url}?${params}` : url;

            try {
                const res = await fetch(fetchUrl, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "text/html,application/xhtml+xml",
                    },
                    credentials: "same-origin",
                });

                const ctype = res.headers.get("content-type") || "";
                const text = await res.text();

                if (ctype.includes("application/json")) {
                    console.warn(
                        "[loadTable] server returned JSON, expected HTML:",
                        text
                    );
                    tableContainer.innerHTML = `<div class="alert alert-warning">Response server berbentuk JSON — periksa endpoint.</div>`;
                    return;
                }

                if (
                    text.includes('name="password"') &&
                    text.toLowerCase().includes("login")
                ) {
                    console.warn(
                        "[loadTable] kemungkinan dikembalikan halaman login (session expired)."
                    );
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

        // ==============================
        // 🔗 Binding event di tabel (pagination + aksi)
        // ==============================
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
                b.addEventListener("click", () =>
                    openDeleteModal(b.dataset.id, b.dataset.kode)
                )
            );
        }

        // ==============================
        // 🔎 Filter & search
        // ==============================
        searchInput?.addEventListener(
            "input",
            debounce(() => loadTable(), 400)
        );

        btnReset?.addEventListener("click", (e) => {
            e.preventDefault();
            filterForm?.reset();
            loadTable();
            toast("Filter direset", "info");
        });

        // ==================================================
        // 🔍 CEK DUPLIKAT KODE — real-time (add & edit)
        // ==================================================
        async function checkDuplicateKode(value, mode = "add") {
            const id = mode === "edit" ? $("#edit_id")?.value : "";

            const url = `${baseUrl}/kode/check-duplicate?kode=${encodeURIComponent(
                value
            )}${id ? `&id=${id}` : ""}`;

            try {
                const res = await fetch(url, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                });
                const data = await res.json();
                return data.exists;
            } catch (err) {
                console.error("Error checking duplicate:", err);
                return false;
            }
        }

        // ==================================================
        // 🔄 Binding: cek duplikat ketika input diketik
        // ==================================================
        function bindDuplicateChecker() {
            const addInput = $("#add_kode");
            const editInput = $("#edit_kode");

            // CEK DUPLIKAT SAAT TAMBAH
            addInput?.addEventListener(
                "input",
                debounce(async () => {
                    const val = addInput.value.trim();
                    clearValidationErrors();

                    if (!val) return;

                    const exists = await checkDuplicateKode(val, "add");

                    if (exists) {
                        addInput.classList.add("is-invalid");

                        if (
                            !addInput.nextElementSibling ||
                            !addInput.nextElementSibling.classList.contains(
                                "invalid-feedback"
                            )
                        ) {
                            const err = document.createElement("div");
                            err.className = "invalid-feedback";
                            err.textContent = "Kode sudah digunakan.";
                            addInput.after(err);
                        }
                    } else {
                        addInput.classList.remove("is-invalid");
                    }
                }, 400)
            );

            // CEK DUPLIKAT SAAT EDIT
            editInput?.addEventListener(
                "input",
                debounce(async () => {
                    const val = editInput.value.trim();
                    clearValidationErrors();

                    if (!val) return;

                    const exists = await checkDuplicateKode(val, "edit");

                    if (exists) {
                        editInput.classList.add("is-invalid");

                        if (
                            !editInput.nextElementSibling ||
                            !editInput.nextElementSibling.classList.contains(
                                "invalid-feedback"
                            )
                        ) {
                            const err = document.createElement("div");
                            err.className = "invalid-feedback";
                            err.textContent = "Kode sudah digunakan.";
                            editInput.after(err);
                        }
                    } else {
                        editInput.classList.remove("is-invalid");
                    }
                }, 400)
            );
        }

        // ==============================
        // ➕ Tambah Kode
        // ==============================
        $("#formAddKode")?.addEventListener("submit", async (e) => {
            e.preventDefault();
            clearValidationErrors(); // Bersihkan error sebelum submit

            const kodeInput = $("#add_kode");
            const kodeVal = kodeInput?.value.trim() ?? "";

            // Guard: cek duplikat sebelum kirim ke server
            if (kodeVal) {
                const exists = await checkDuplicateKode(kodeVal, "add");
                if (exists) {
                    kodeInput.classList.add("is-invalid");
                    if (
                        !kodeInput.nextElementSibling ||
                        !kodeInput.nextElementSibling.classList.contains(
                            "invalid-feedback"
                        )
                    ) {
                        const err = document.createElement("div");
                        err.className = "invalid-feedback";
                        err.textContent = "Kode telah tersedia.";
                        kodeInput.after(err);
                    }
                    toast("Kode telah tersedia.", "error");
                    return; // jangan submit ke server
                }
            }

            const data = new FormData(e.target);

            try {
                const res = await fetch(`${baseUrl}/kode`, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": csrf },
                    body: data,
                    credentials: "same-origin",
                });

                if (res.ok) {
                    // Status 200 (SUCCESS)
                    bootstrap.Modal.getInstance($("#addKodeModal"))?.hide();
                    e.target.reset();
                    loadTable();
                    toast("✅ Kode berhasil ditambahkan", "success");
                } else if (res.status === 422) {
                    // Status 422 (VALIDATION ERROR dari backend)
                    const errorData = await res.json();
                    displayValidationErrors(errorData.errors, "add");
                } else {
                    toast("❌ Gagal menambahkan kode", "error");
                }
            } catch (err) {
                console.error(err);
                toast("❌ Terjadi kesalahan saat menambah kode", "error");
            }
        });

        // ==============================
        // ✏️ Edit Kode
        // ==============================
        async function openEditModal(id) {
            clearValidationErrors(); // Bersihkan error saat modal dibuka
            try {
                const res = await fetch(`${baseUrl}/kode/${id}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                    credentials: "same-origin",
                });

                if (!res.ok) throw new Error("Fetch gagal");

                const data = await res.json();
                $("#edit_id").value = data.id_kode;
                $("#edit_kode").value = data.kode;
                $("#edit_description").value = data.description || "";

                new bootstrap.Modal($("#editKodeModal")).show();
            } catch (err) {
                console.error(err);
                toast("Gagal membuka data kode", "error");
            }
        }

        $("#formEditKode")?.addEventListener("submit", async (e) => {
            e.preventDefault();
            clearValidationErrors(); // Bersihkan error sebelum submit

            const id = $("#edit_id").value;
            const kodeInput = $("#edit_kode");
            const kodeVal = kodeInput?.value.trim() ?? "";

            // Guard: cek duplikat sebelum kirim ke server
            if (kodeVal) {
                const exists = await checkDuplicateKode(kodeVal, "edit");
                if (exists) {
                    kodeInput.classList.add("is-invalid");
                    if (
                        !kodeInput.nextElementSibling ||
                        !kodeInput.nextElementSibling.classList.contains(
                            "invalid-feedback"
                        )
                    ) {
                        const err = document.createElement("div");
                        err.className = "invalid-feedback";
                        err.textContent = "Kode telah tersedia.";
                        kodeInput.after(err);
                    }
                    toast("Kode telah tersedia.", "error");
                    return; // jangan submit ke server
                }
            }

            const data = new FormData(e.target);
            data.append("_method", "PUT");

            try {
                const res = await fetch(`${baseUrl}/kode/${id}`, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": csrf },
                    body: data,
                    credentials: "same-origin",
                });

                if (res.ok) {
                    // Status 200 (SUCCESS)
                    bootstrap.Modal.getInstance($("#editKodeModal"))?.hide();
                    loadTable();
                    toast("✅ Kode berhasil diperbarui", "success");
                } else if (res.status === 422) {
                    // Status 422 (VALIDATION ERROR dari backend)
                    const errorData = await res.json();
                    displayValidationErrors(errorData.errors, "edit");
                } else {
                    toast("❌ Gagal memperbarui kode", "error");
                }
            } catch (err) {
                console.error(err);
                toast("❌ Terjadi kesalahan saat update kode", "error");
            }
        });

        // ==============================
        // 🗑️ Hapus Kode
        // ==============================
        function openDeleteModal(id, kode) {
            $("#delete_id").value = id;
            $("#delete_kode_label").textContent = kode;
            new bootstrap.Modal($("#deleteKodeModal")).show();
        }

        $("#formDeleteKode")?.addEventListener("submit", async (e) => {
            e.preventDefault();
            const id = $("#delete_id").value;
            const data = new FormData();
            data.append("_method", "DELETE");

            try {
                const res = await fetch(`${baseUrl}/kode/${id}`, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": csrf },
                    body: data,
                    credentials: "same-origin",
                });

                if (res.ok) {
                    bootstrap.Modal.getInstance($("#deleteKodeModal"))?.hide();
                    loadTable();
                    toast("🗑️ Kode berhasil dihapus", "success");
                } else {
                    toast("❌ Gagal menghapus kode", "error");
                }
            } catch (err) {
                console.error(err);
                toast("❌ Terjadi kesalahan saat hapus kode", "error");
            }
        });

        // ==============================
        // 📄 Detail Kode
        // ==============================
        async function openDetailModal(id) {
            try {
                const res = await fetch(`${baseUrl}/kode/${id}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                    credentials: "same-origin",
                });

                if (!res.ok) throw new Error("Fetch gagal");

                const data = await res.json();
                $("#detail_kode").textContent = data.kode;
                $("#detail_description").textContent = data.description || "-";

                new bootstrap.Modal($("#detailKodeModal")).show();
            } catch (err) {
                console.error(err);
                toast("❌ Gagal memuat detail kode", "error");
            }
        }

        // ==============================
        // 🚀 Init pertama
        // ==============================
        loadTable();
        bindDuplicateChecker();
    });
})();
