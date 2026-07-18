/**
 * user.js
 * ──────────────────────────────────────────────────────────
 * MENU  : Master User (/user)
 * FILE  : resources/js/user.js
 * SCOPE : Halaman user saja (guard: #page-user)
 */
(() => {
    "use strict";

    if (!document.querySelector("#page-user")) return;

    const metaBase = document
        .querySelector('meta[name="base-url"]')
        ?.getAttribute("content")
        ?.replace(/\/$/, "") ?? "";
    // Gunakan origin browser agar selalu cocok di hosting, abaikan APP_URL
    const baseUrl = window.location.origin === new URL(metaBase || window.location.href).origin
        ? metaBase
        : window.location.origin;
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

    const toast = (message, type = "info") => window.AppToast(message, type);

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
    const debounce = window.debounce;

    const tableGuard = window.createFetchGuard(); // cegah race condition search/filter tabel user

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

        const { signal, isStale } = tableGuard.start();

        tableContainer.innerHTML = `<div class="py-5 text-muted text-center"><div class="spinner-border"></div><p>Memuat...</p></div>`;
        try {
            const res = await fetch(urlObj.toString(), {
                headers: { "X-Requested-With": "XMLHttpRequest" },
                credentials: "same-origin",
                signal,
            });
            if (isStale()) return; // ada pencarian/filter lebih baru menyusul

            const html = await res.text();
            if (isStale()) return;

            tableContainer.innerHTML = html;
            bindTableEvents();
        } catch (err) {
            if (err.name === "AbortError" || isStale()) return;
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
        if (croppedFileAdd) {
            data.delete("foto");
            data.append("foto", croppedFileAdd, "foto.jpg");
        }
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
                croppedFileAdd = null;

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
            croppedFileEdit = null;
            clearErrors("edit");
            const res = await fetch(`${baseUrl}/user/${id}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const data = await res.json();

            $("#edit_id").value = data.id_user;
            $("#edit_name").value = data.name;
            $("#edit_email").value = data.email;
            $("#edit_role").value = data.id_role;

            // Kepala Staf: kunci email dan role — hanya nama & password yang bisa diubah
            const isKepalaStaf = data.role?.name === "Kepala Staf";

            const emailInput = $("#edit_email");
            if (emailInput) {
                emailInput.readOnly = isKepalaStaf;
                emailInput.classList.toggle("bg-light", isKepalaStaf);
                emailInput.classList.toggle("text-muted", isKepalaStaf);
            }

            const roleSelect = $("#edit_role");
            if (roleSelect) {
                roleSelect.disabled = isKepalaStaf;
            }

            // Tampilkan/sembunyikan info badge
            let note = document.getElementById("edit_kepala_note");
            if (!note) {
                note = document.createElement("div");
                note.id = "edit_kepala_note";
                note.className = "alert alert-info py-2 px-3 mb-0 mt-2 small";
                note.innerHTML = '<i class="bi bi-shield-lock-fill me-1"></i>Kepala Staf: email dan role tidak dapat diubah.';
                roleSelect?.closest(".mb-3")?.after(note);
            }
            note.style.display = isKepalaStaf ? "block" : "none";

            const img = $("#previewFotoEdit");
            if (img) {
                const def = img.dataset.default; // jaga default placeholder dari HTML
                const src = data.foto || def;
                img.src = src;
                img.onerror = function () { this.src = def; this.onerror = null; };
                img.dataset.current = src;
                // data-default tidak di-overwrite — tetap = placeholder default
            }

            const passwordInput = $("#edit_password");
            if (passwordInput) {
                passwordInput.type = "password";
                passwordInput.value = "";
            }

            // Reset file input label
            const fileInput = $("#edit_foto");
            if (fileInput) fileInput.value = "";

            bootstrap.Modal.getOrCreateInstance($("#editUserModal")).show();
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
        if (croppedFileEdit) {
            data.delete("foto");
            data.append("foto", croppedFileEdit, "foto.jpg");
        }

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
                croppedFileEdit = null;
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
        bootstrap.Modal.getOrCreateInstance($("#deleteUserModal")).show();
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
            } else toast(result.message || "Gagal menghapus user", "error");
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

            bootstrap.Modal.getOrCreateInstance($("#detailUserModal")).show();
        } catch (err) {
            console.error(err);
            toast("Gagal memuat detail user", "error");
        }
    }

    const DEFAULT_FOTO = `${baseUrl}/assets/img/default_staf.png`;

    const MAX_FOTO_MB = 4;
    const MAX_FOTO_BYTES = MAX_FOTO_MB * 1024 * 1024;
    const ALLOWED_FOTO_TYPES = ["image/jpeg", "image/jpg", "image/png"];

    function validateFoto(file, errorElId) {
        const errEl = document.getElementById(errorElId);
        if (!file) { if (errEl) errEl.textContent = ""; return true; }
        if (!ALLOWED_FOTO_TYPES.includes(file.type)) {
            if (errEl) errEl.textContent = "Format harus JPG atau PNG.";
            return false;
        }
        if (file.size > MAX_FOTO_BYTES) {
            if (errEl) errEl.textContent = `Ukuran file maksimal ${MAX_FOTO_MB}MB. File Anda: ${(file.size / 1024 / 1024).toFixed(1)}MB.`;
            return false;
        }
        if (errEl) errEl.textContent = "";
        return true;
    }

    // ===== Crop modal state =====
    let cropper = null;
    let croppedFileAdd = null;
    let croppedFileEdit = null;
    let activeCropTarget = null; // 'add' | 'edit'

    const cropModalEl = $("#cropFotoModal");
    const cropImage = $("#cropImage");

    function openCropModal(file, target) {
        activeCropTarget = target;
        const url = URL.createObjectURL(file);
        cropImage.src = url;
        const modal = new bootstrap.Modal(cropModalEl);
        cropModalEl.addEventListener("shown.bs.modal", () => {
            if (cropper) { cropper.destroy(); cropper = null; }
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: "move",
                autoCropArea: 0.85,
                movable: true,
                zoomable: true,
                rotatable: false,
                scalable: false,
                cropBoxMovable: false,
                cropBoxResizable: false,
                highlight: false,
                background: false,
            });
        }, { once: true });
        modal.show();
    }

    $("#btnApplyCrop")?.addEventListener("click", () => {
        if (!cropper) return;
        cropper.getCroppedCanvas({ width: 400, height: 400 }).toBlob((blob) => {
            if (!blob) return;
            const file = new File([blob], "foto.jpg", { type: "image/jpeg" });
            const objUrl = URL.createObjectURL(blob);
            if (activeCropTarget === "add") {
                croppedFileAdd = file;
                const img = $("#previewFotoAdd");
                if (img) img.src = objUrl;
            } else {
                croppedFileEdit = file;
                const img = $("#previewFotoEdit");
                if (img) img.src = objUrl;
            }
            bootstrap.Modal.getInstance(cropModalEl)?.hide();
        }, "image/jpeg", 0.92);
    });

    function cancelCrop() {
        if (activeCropTarget === "add") {
            const fi = $("#add_foto");
            const img = $("#previewFotoAdd");
            if (fi) fi.value = "";
            if (img) img.src = img.dataset.default || img.src;
            croppedFileAdd = null;
        } else {
            const fi = $("#edit_foto");
            const img = $("#previewFotoEdit");
            if (fi) fi.value = "";
            if (img) img.src = img.dataset.current || img.dataset.default || img.src;
            croppedFileEdit = null;
        }
        bootstrap.Modal.getInstance(cropModalEl)?.hide();
    }

    $("#btnCancelCrop")?.addEventListener("click", cancelCrop);

    cropModalEl?.addEventListener("hidden.bs.modal", () => {
        if (cropper) { cropper.destroy(); cropper = null; }
    });

    // ===== Preview foto ADD =====
    $("#add_foto")?.addEventListener("change", (e) => {
        const img = $("#previewFotoAdd");
        if (!img) return;
        const def = img.dataset.default || img.getAttribute("src");
        const file = e.target.files?.[0];
        if (!validateFoto(file, "error_add_foto")) {
            e.target.value = "";
            img.src = def;
            croppedFileAdd = null;
            return;
        }
        if (file) openCropModal(file, "add");
    });

    // ===== Preview foto EDIT =====
    $("#edit_foto")?.addEventListener("change", (e) => {
        const img = $("#previewFotoEdit");
        if (!img) return;
        const fallback = img.dataset.current || img.dataset.default || img.getAttribute("src");
        const file = e.target.files?.[0];
        if (!validateFoto(file, "error_edit_foto")) {
            e.target.value = "";
            img.src = fallback;
            croppedFileEdit = null;
            return;
        }
        if (file) openCropModal(file, "edit");
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

    // ===== Reset modal ADD saat dibuka =====
    const addModalEl = $("#addUserModal");
    if (addModalEl) {
        addModalEl.addEventListener("show.bs.modal", () => {
            croppedFileAdd = null;
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

    // ===== Reset croppedFileEdit saat modal EDIT dibuka & ditutup =====
    const editModalEl = $("#editUserModal");
    if (editModalEl) {
        // Saat modal edit dibuka: buang sisa crop dari sesi sebelumnya
        editModalEl.addEventListener("show.bs.modal", () => {
            croppedFileEdit = null;
            const fi = $("#edit_foto");
            if (fi) fi.value = "";
        });

        // Saat modal edit ditutup (dismiss/batal): pastikan state bersih
        editModalEl.addEventListener("hidden.bs.modal", () => {
            croppedFileEdit = null;
            const fi = $("#edit_foto");
            const img = $("#previewFotoEdit");
            if (fi) fi.value = "";
            // Kembalikan foto ke foto yang sedang tersimpan di server
            if (img && img.dataset.current) img.src = img.dataset.current;
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