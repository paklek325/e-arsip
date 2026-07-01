(() => {
    "use strict";

    if (!document.querySelector("#page-user")) return;

    const baseUrl = document
        .querySelector('meta[name="base-url"]')
        .getAttribute("content");
    const csrf = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    // Helper function untuk DOM selection
    const $ = (s) => document.querySelector(s);
    const $$ = (s) => Array.from(document.querySelectorAll(s));

    const tableContainer = $("#tableContainer");
    const filterForm = $("#filter-form");
    const searchInput = $("#search");
    const btnReset = $("#btnReset");

    // ===== Toast Notification Helper =====
    const toast = (message, type = "info") => {
        const bg =
            type === "success"
                ? "bg-success"
                : type === "error"
                ? "bg-danger"
                : type === "warning"
                ? "bg-warning text-dark"
                : "bg-primary";
        const icon =
            type === "success"
                ? "✅"
                : type === "error"
                ? "❌"
                : type === "warning"
                ? "⚠️"
                : "ℹ️";
        const div = document.createElement("div");
        div.className =
            "toast align-items-center text-white show position-fixed top-0 end-0 m-3 " +
            bg;
        div.innerHTML = `<div class="d-flex"><div class="toast-body fw-semibold">${icon} ${message}</div></div>`;
        document.body.append(div);
        setTimeout(() => div.remove(), 2500);
    };

    // ===== Helpers error form (CLEANED UP & CONSOLIDATED) =====
    // Map field name di Laravel (key) ke ID elemen HTML (suffix)
    const fieldMap = {
        name: "name",
        email: "email",
        password: "password",
        id_role: "id_role", // Key Laravel 'id_role'
        foto: "foto",
    };

    // Helper untuk mendapatkan elemen input/select berdasarkan prefix modal dan key field
    function getInput(prefix, key) {
        // Logika custom untuk id_role dan foto
        if (key === "id_role") return $(`#${prefix}_role`); // Mengarah ke #add_role atau #edit_role
        if (key === "foto") return $(`#${prefix}_foto`); // Mengarah ke #add_foto atau #edit_foto
        return $(`#${prefix}_${fieldMap[key]}`); // Mengarah ke #add_name, #edit_email, dsb.
    }

    // Menghapus kelas is-invalid dan pesan error
    function clearErrors(prefix) {
        Object.keys(fieldMap).forEach((key) => {
            const input = getInput(prefix, key);
            // ErrorDiv menggunakan format #error_{prefix}_{key}
            const errorDiv = $(`#error_${prefix}_${key}`);

            if (input) input.classList.remove("is-invalid");
            if (errorDiv) errorDiv.textContent = "";
        });
    }

    // Menampilkan kelas is-invalid dan pesan error
    function showErrors(prefix, errors) {
        Object.entries(errors).forEach(([key, messages]) => {
            if (!fieldMap[key]) return;
            const input = getInput(prefix, key);
            const errorDiv = $(`#error_${prefix}_${key}`);

            if (input) input.classList.add("is-invalid");
            // Menampilkan pesan error pertama
            if (errorDiv) errorDiv.textContent = messages[0] ?? "";
        });
    }

    // ===== Table, filter, pagination =====
    const debounce = (fn, delay = 500) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    };

    async function loadTable(url = `${baseUrl}/user`) {
        // BUGFIX: sebelumnya selalu menambahkan "?...", sehingga jika `url` sudah
        // mengandung query string (misalnya dari link pagination "?page=2"),
        // hasilnya jadi "?page=2?search=x&ajax=1" (rusak/double "?").
        // Gunakan URL object + searchParams.set agar query digabung dengan benar.
        const urlObj = new URL(url, window.location.origin);
        const formParams = new URLSearchParams(
            filterForm ? new FormData(filterForm) : new FormData()
        );
        formParams.forEach((value, key) => urlObj.searchParams.set(key, value));
        urlObj.searchParams.set("ajax", "1");

        tableContainer.innerHTML = `<div class="py-5 text-muted text-center"><div class="spinner-border"></div><p>Memuat...</p></div>`;
        try {
            const res = await fetch(urlObj.toString(), {
                headers: { "X-Requested-With": "XMLHttpRequest" },
                credentials: "same-origin"
            });
            const html = await res.text();
            tableContainer.innerHTML = html;
            bindTableEvents();
        } catch (err) {
            console.error(err);
            toast("Gagal memuat tabel", "error");
        }
    }

    function bindTableEvents() {
        $$(".pagination a").forEach((a) =>
            a.addEventListener("click", (e) => {
                e.preventDefault();
                // Mengambil URL yang dimuat, tapi memastikan parameter search tetap ada
                const newUrl = new URL(a.href);
                const currentSearch = searchInput?.value;
                if (currentSearch) {
                    newUrl.searchParams.set("search", currentSearch);
                }
                loadTable(newUrl.href);
            })
        );

        $$(".btn-detail").forEach((b) =>
            b.addEventListener("click", () => openDetailModal(b.dataset.id))
        );
        $$(".btn-edit").forEach((b) =>
            b.addEventListener("click", () => openEditModal(b.dataset.id))
        );
        $$(".btn-delete").forEach((b) =>
            b.addEventListener("click", () =>
                openDeleteModal(b.dataset.id, b.dataset.name)
            )
        );
    }

    searchInput?.addEventListener(
        "input",
        debounce(() => loadTable())
    );
    btnReset?.addEventListener("click", (e) => {
        e.preventDefault();
        filterForm.reset();
        loadTable();
    });

    // ===== Tambah User (STORE) =====
    $("#formAddUser")?.addEventListener("submit", async (e) => {
        e.preventDefault();
        clearErrors("add");
        const data = new FormData(e.target);
        try {
            const res = await fetch(`${baseUrl}/user`, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrf },
                body: data,
            });
            const result = await res.json();

            if (res.ok && result.success) {
                bootstrap.Modal.getInstance($("#addUserModal")).hide();
                e.target.reset();

                const img = $("#previewFotoAdd");
                if (img) {
                    const def = img.dataset.default || img.getAttribute("src");
                    img.src = def;
                }

                toast("User berhasil ditambahkan", "success");
                loadTable();
            } else if (res.status === 422) {
                const errors = result.errors || {};
                console.log("VALIDATION ERROR ADD:", result);

                // Mengambil pesan error pertama yang ditemukan
                const firstKey = Object.keys(errors).find(
                    (k) => errors[k]?.length > 0
                );
                if (firstKey) {
                    toast(errors[firstKey][0], "error");
                } else {
                    // Jika error 422 tapi object errors kosong (misal karena Role tidak ada di DB)
                    toast(
                        result.message || "Validasi gagal. Cek input.",
                        "error"
                    );
                }

                showErrors("add", errors);
            } else {
                toast(result.message || "Gagal menambahkan user", "error");
            }
        } catch (err) {
            console.error(err);
            toast("Terjadi kesalahan server", "error");
        }
    });

    // ===== Edit: load data (PERBAIKAN FOTO DEFAULT) =====
    async function openEditModal(id) {
        try {
            clearErrors("edit");
            const res = await fetch(`${baseUrl}/user/${id}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const data = await res.json();

            $("#edit_id").value = data.id_user; // Pastikan ini mengarah ke hidden input name="id_user"
            $("#edit_name").value = data.name;
            $("#edit_email").value = data.email;
            $("#edit_role").value = data.id_role;

            const img = $("#previewFotoEdit");
            if (img) {
                // data.foto sudah dipastikan dari Controller berisi URL foto asli atau URL noimage.png
                img.src = data.foto;
                // Simpan URL yang sekarang sebagai 'current' (untuk fallback preview jika user membatalkan pilih file)
                img.dataset.current = data.foto;
            }

            const passwordInput = $("#edit_password");
            if (passwordInput) {
                passwordInput.type = "password";
                passwordInput.value = "";
            }

            // Reset file input label
            const fileInput = $("#edit_foto");
            if (fileInput) fileInput.value = "";

            new bootstrap.Modal($("#editUserModal")).show();
        } catch (err) {
            console.error(err);
            toast("Gagal memuat data user", "error");
        }
    }

    // ===== Edit: submit (UPDATE) =====
    $("#formEditUser")?.addEventListener("submit", async (e) => {
        e.preventDefault();
        clearErrors("edit");
        const id = $("#edit_id").value;
        const data = new FormData(e.target);
        data.append("_method", "PUT");

        // Cek apakah password kosong. Jika kosong, hapus field 'password' dari FormData agar tidak divalidasi/diupdate.
        if (!data.get("password")) {
            data.delete("password");
        }

        try {
            const res = await fetch(`${baseUrl}/user/${id}`, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrf },
                body: data,
            });
            const result = await res.json();

            if (res.ok && result.success) {
                bootstrap.Modal.getInstance($("#editUserModal")).hide();
                toast("User berhasil diperbarui", "success");
                loadTable();

                if ($("#detailUserModal")?.classList.contains("show")) {
                    openDetailModal(id);
                }
            } else if (res.status === 422) {
                const errors = result.errors || {};
                console.log("VALIDATION ERROR EDIT:", result);

                // Mengambil pesan error pertama yang ditemukan
                const firstKey = Object.keys(errors).find(
                    (k) => errors[k]?.length > 0
                );
                if (firstKey) {
                    toast(errors[firstKey][0], "error");
                } else {
                    // Jika error 422 tapi object errors kosong (misal karena Role tidak ada di DB)
                    toast(
                        result.message || "Validasi gagal. Cek input.",
                        "error"
                    );
                }

                showErrors("edit", errors);
            } else {
                toast(result.message || "Gagal memperbarui user", "error");
            }
        } catch (err) {
            console.error(err);
            toast("Gagal update user", "error");
        }
    });

    // ===== Hapus User (DESTROY) =====
    function openDeleteModal(id, name) {
        $("#delete_id").value = id;
        $("#delete_name_label").textContent = name;
        new bootstrap.Modal($("#deleteUserModal")).show();
    }

    $("#formDeleteUser")?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = $("#delete_id").value;
        const data = new FormData();
        data.append("_method", "DELETE");
        try {
            const res = await fetch(`${baseUrl}/user/${id}`, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrf },
                body: data,
            });
            const result = await res.json();
            if (res.ok && result.success) {
                bootstrap.Modal.getInstance($("#deleteUserModal")).hide();
                toast("User berhasil dihapus", "success");
                loadTable();
            } else toast("Gagal menghapus user", "error");
        } catch (err) {
            console.error(err);
            toast("Gagal menghapus user", "error");
        }
    });

    // ===== Detail User =====
    async function openDetailModal(id) {
        try {
            const res = await fetch(`${baseUrl}/user/${id}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const data = await res.json();

            $("#detail_foto").src = data.foto;
            $("#detail_name").textContent = data.name;
            $("#detail_email").textContent = data.email;
            $("#detail_role").textContent = data.role?.name ?? "-";

            new bootstrap.Modal($("#detailUserModal")).show();
        } catch (err) {
            console.error(err);
            toast("Gagal memuat detail user", "error");
        }
    }

    // ===== Preview foto ADD (OK) =====
    $("#add_foto")?.addEventListener("change", (e) => {
        const img = $("#previewFotoAdd");
        if (!img) return;
        const def = img.dataset.default || img.getAttribute("src");
        const file = e.target.files?.[0];
        img.src = file ? URL.createObjectURL(file) : def;
    });

    // ===== Preview foto EDIT (OK - menggunakan data-current yang diset di openEditModal) =====
    $("#edit_foto")?.addEventListener("change", (e) => {
        const img = $("#previewFotoEdit");
        if (!img) return;
        // Fallback: Gunakan data-current (foto user saat ini/noimage) jika tidak ada file yang dipilih
        const fallback =
            img.dataset.current ||
            img.dataset.default ||
            img.getAttribute("src");
        const file = e.target.files?.[0];
        img.src = file ? URL.createObjectURL(file) : fallback;
    });

    // ===== Toggle password (OK) =====
    $(".togglePasswordAdd")?.addEventListener("click", () => {
        const input = $("#add_password");
        const icon = $(".togglePasswordAdd i");
        input.type = input.type === "password" ? "text" : "password";
        icon.className =
            input.type === "password" ? "bi bi-eye-slash" : "bi bi-eye";
    });
    $(".togglePasswordEdit")?.addEventListener("click", () => {
        const input = $("#edit_password");
        const icon = $(".togglePasswordEdit i");
        input.type = input.type === "password" ? "text" : "password";
        icon.className =
            input.type === "password" ? "bi bi-eye-slash" : "bi bi-eye";
    });

    // ===== Reset modal ADD saat dibuka (OK) =====
    const addModalEl = $("#addUserModal");
    if (addModalEl) {
        addModalEl.addEventListener("show.bs.modal", () => {
            const addForm = $("#formAddUser");
            const img = $("#previewFotoAdd");
            if (img) {
                const def = img.dataset.default || img.getAttribute("src");
                img.src = def;
            }
            if (addForm) {
                addForm.reset();
                clearErrors("add");
                const fileInput = addForm.querySelector('input[type="file"]');
                if (fileInput) fileInput.value = "";

                const icon = $(".togglePasswordAdd i");
                if (icon) icon.className = "bi bi-eye-slash";
            }
        });
    }

    // BUGFIX: jika script ini dimuat tanpa "defer" / di bagian bawah <body>
    // (pola umum di Blade), event DOMContentLoaded sudah selesai duluan
    // sebelum listener ini terpasang, sehingga loadTable() awal tidak
    // pernah berjalan. Cek document.readyState dulu.
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => loadTable());
    } else {
        loadTable();
    }
})();
