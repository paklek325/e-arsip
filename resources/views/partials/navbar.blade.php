<header class="pc-header">
    <div class="header-wrapper">

        {{-- ── Kiri: Toggle Sidebar ─────────────────────────────────────── --}}
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">

                {{-- Desktop: klik kiri untuk collapse/expand sidebar --}}
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide" aria-label="Toggle Sidebar">
                        <i class="bi bi-list fs-5"></i>
                    </a>
                </li>

                {{-- Mobile: klik untuk slide-in sidebar --}}
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse" aria-label="Buka Menu">
                        <i class="bi bi-list fs-5"></i>
                    </a>
                </li>

            </ul>
        </div>

        {{-- ── Kanan: Aksi header ───────────────────────────────────────── --}}
        <div class="ms-auto">
            <ul class="list-unstyled">

                {{-- Dark / Light Mode Toggle --}}
                <li class="pc-h-item px-2">
                    <button id="theme-switch" type="button" aria-label="Ganti tema" title="Ganti tema">
                        <i class="bi bi-sun-fill sun"></i>
                        <i class="bi bi-moon-fill moon"></i>
                        <span class="toggle-thumb"></span>
                    </button>
                </li>

                @auth
                    @php
                        $authUser = auth()->user();
                        $fotoUrl = $authUser->foto
                            ? asset('storage/foto_admin/' . $authUser->foto)
                            : asset('assets/img/default_staf.png');
                        $roleName = ucwords($authUser->role->name ?? 'User');
                    @endphp

                    {{-- User Profile Dropdown --}}
                    <li class="dropdown pc-h-item header-user-profile">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                            <img src="{{ $fotoUrl }}" onerror="this.src='{{ asset('assets/img/default_staf.png') }}'"
                                alt="Foto {{ $authUser->name }}" class="user-avatar">
                        </a>

                        <div
                            class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown p-0 overflow-hidden">

                            {{-- Header dropdown: foto + nama + role + tombol logout --}}
                            <div class="dropdown-header d-flex align-items-center gap-3 dropdown-user-header">
                                <img src="{{ $fotoUrl }}"
                                    onerror="this.src='{{ asset('assets/img/default_staf.png') }}'"
                                    alt="{{ $authUser->name }}" class="img-radius wid-35 dropdown-user-avatar">
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="text-white mb-0 text-truncate">{{ $authUser->name }}</h6>
                                    <small class="text-white text-opacity-75">{{ $roleName }}</small>
                                </div>
                                {{-- Tombol Logout di sebelah foto --}}
                                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0"
                                    onsubmit="try{
                                      ['eac_user','eac_history','eac_messages_html','eac_panel_open']
                                      .forEach(k => sessionStorage.removeItem(k));
                                  }catch(_){}">
                                    @csrf
                                    <button type="submit" class="btn-logout-header" title="Keluar">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </form>
                            </div>

                            {{-- Body: menu --}}
                            <div class="dropdown-body">

                                <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#navProfilModal">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-gear fs-6 text-primary"></i>
                                        <span>Profil Saya</span>
                                    </span>
                                    <i class="bi bi-chevron-right text-muted small"></i>
                                </a>

                            </div>
                        </div>
                    </li>
                @endauth

            </ul>
        </div>

    </div>
</header>

@auth

    {{-- =========== Modal Profil Saya (popup, tidak pindah halaman) ============= --}}
    <div class="modal fade" id="navProfilModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formProfilSaya" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-person-circle"></i> Profil Saya</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        {{-- FOTO --}}
                        <div class="mb-3 text-center">
                            <div class="foto-preview-wrapper mx-auto mb-2">
                                <img id="previewFotoProfil" src="{{ $fotoUrl }}"
                                    data-default="{{ asset('assets/img/default_staf.png') }}"
                                    onerror="this.src='{{ asset('assets/img/default_staf.png') }}'; this.onerror=null;">
                            </div>
                            <input type="file" name="foto" id="profil_foto" class="form-control mt-2"
                                accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Format JPG/PNG, maks. 4MB</small>
                            <div class="invalid-feedback d-block" id="error_profil_foto"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" id="profil_name" class="form-control"
                                value="{{ $authUser->name }}" required>
                            <div class="invalid-feedback" id="error_profil_name"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="profil_email" class="form-control"
                                value="{{ $authUser->email }}" required>
                            <div class="invalid-feedback" id="error_profil_email"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control bg-body-secondary" value="{{ $roleName }}"
                                readonly>
                            <div class="form-text">Role tidak dapat diubah sendiri.</div>
                        </div>

                        <div class="mb-1">
                            <label class="form-label">Password Baru (Opsional)</label>
                            <div class="input-group">
                                <input type="password" name="password" id="profil_password" class="form-control"
                                    placeholder="Kosongkan jika tidak ingin mengubah">
                                <span class="input-group-text togglePasswordProfil">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                            </div>
                            <small class="text-muted">Minimal 3 karakter. Kosongkan jika tidak ingin mengubah.</small>
                            <div class="invalid-feedback d-block" id="error_profil_password"></div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanProfil"><i class="bi bi-save"></i>
                            Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =========  Modal Crop Foto Profil ================ --}}
    <div class="modal fade" id="cropFotoProfilModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-crop-dialog">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0"><i class="bi bi-crop me-1"></i>Atur Posisi Foto</h6>
                </div>
                <div class="modal-body p-0 crop-body">
                    <img id="cropImageProfil" class="crop-img">
                </div>
                <div class="modal-footer py-2 gap-2 justify-content-between">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Drag untuk atur posisi, scroll untuk
                        zoom</small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" id="btnCancelCropProfil">Batal</button>
                        <button type="button" class="btn btn-primary btn-sm" id="btnApplyCropProfil">
                            <i class="bi bi-check-lg"></i> Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @once
        @push('styles')
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
        @endpush

        @push('scripts')
            <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
            <script>
                (() => {
                    "use strict";

                    const $ = (s) => document.querySelector(s);

                    const modalEl = $("#navProfilModal");
                    const cropModalEl = $("#cropFotoProfilModal");
                    const form = $("#formProfilSaya");
                    if (!modalEl || !form) return;

                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ?? "";

                    const profilModal = new bootstrap.Modal(modalEl);
                    const cropModal = new bootstrap.Modal(cropModalEl);

                    const fotoInput = $("#profil_foto");
                    const preview = $("#previewFotoProfil");
                    const cropImage = $("#cropImageProfil");
                    const applyBtn = $("#btnApplyCropProfil");

                    let cropper = null;
                    let croppedFile = null;
                    let reopenProfil = false;

                    const fieldMap = {
                        name: "name",
                        email: "email",
                        password: "password",
                        foto: "foto"
                    };

                    function clearErrors() {
                        Object.keys(fieldMap).forEach((key) => {
                            const input = key === "foto" ? fotoInput : $(`#profil_${key}`);
                            const errorDiv = $(`#error_profil_${key}`);
                            input?.classList.remove("is-invalid");
                            if (errorDiv) errorDiv.textContent = "";
                        });
                    }

                    function showErrors(errors) {
                        Object.entries(errors || {}).forEach(([key, messages]) => {
                            if (!fieldMap[key]) return;
                            const input = key === "foto" ? fotoInput : $(`#profil_${key}`);
                            const errorDiv = $(`#error_profil_${key}`);
                            input?.classList.add("is-invalid");
                            if (errorDiv) errorDiv.textContent = messages[0] ?? "";
                        });
                    }

                    // ── Reset setiap modal dibuka ───────────────────────────────
                    modalEl.addEventListener("show.bs.modal", () => {
                        clearErrors();
                        fotoInput.value = "";
                        croppedFile = null;
                    });

                    // ── Pilih foto baru → buka modal crop ───────────────────────
                    fotoInput?.addEventListener("change", (e) => {
                        const file = e.target.files[0];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            cropImage.src = ev.target.result;
                            reopenProfil = true;
                            profilModal.hide();
                            cropModal.show();
                        };
                        reader.readAsDataURL(file);
                    });

                    cropModalEl?.addEventListener("shown.bs.modal", () => {
                        cropper?.destroy();
                        cropper = new Cropper(cropImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: "move",
                            autoCropArea: 0.9,
                            restore: false,
                            guides: false,
                            center: true,
                            highlight: false,
                            cropBoxMovable: false,
                            cropBoxResizable: false,
                            toggleDragModeOnDblclick: false,
                        });
                    });

                    applyBtn?.addEventListener("click", () => {
                        if (!cropper) return;
                        cropper.getCroppedCanvas({
                            width: 400,
                            height: 400
                        }).toBlob((blob) => {
                            croppedFile = new File([blob], "profil-foto.jpg", {
                                type: "image/jpeg"
                            });
                            preview.src = URL.createObjectURL(blob);
                            cropModal.hide();
                        }, "image/jpeg", 0.92);
                    });

                    $("#btnCancelCropProfil")?.addEventListener("click", () => {
                        fotoInput.value = "";
                        croppedFile = null;
                        cropModal.hide();
                    });

                    cropModalEl?.addEventListener("hidden.bs.modal", () => {
                        cropper?.destroy();
                        cropper = null;
                        if (reopenProfil) {
                            reopenProfil = false;
                            profilModal.show();
                        }
                    });

                    // ── Toggle lihat password (pola sama seperti togglePasswordAdd/Edit) ──
                    document.querySelector(".togglePasswordProfil")?.addEventListener("click", function() {
                        const input = $("#profil_password");
                        const icon = this.querySelector("i");
                        const isPassword = input.type === "password";
                        input.type = isPassword ? "text" : "password";
                        icon.className = isPassword ? "bi bi-eye" : "bi bi-eye-slash";
                    });

                    // ── Submit AJAX (popup, tidak pindah halaman) ───────────────
                    form.addEventListener("submit", async (e) => {
                        e.preventDefault();
                        clearErrors();

                        const btn = $("#btnSimpanProfil");
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

                        const data = new FormData(form);
                        data.delete("foto");
                        if (croppedFile) data.append("foto", croppedFile);
                        data.append("_method", "PUT");

                        try {
                            const res = await fetch("{{ route('profil.update') }}", {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": csrf,
                                    "X-Requested-With": "XMLHttpRequest",
                                },
                                body: data,
                            });
                            const result = await res.json();

                            if (res.ok && result.success) {
                                const u = result.user ?? {};
                                document.querySelectorAll(".user-avatar, .dropdown-user-avatar").forEach((img) => {
                                    img.src = u.foto_url ?? preview.dataset.default;
                                });
                                document.querySelectorAll(".dropdown-user-header h6").forEach((el) => {
                                    el.textContent = u.name ?? el.textContent;
                                });

                                $("#profil_password").value = "";
                                window.AppToast?.(result.message ?? "Profil berhasil diperbarui", "success");
                                profilModal.hide();
                            } else if (res.status === 422) {
                                showErrors(result.errors);
                                window.AppToast?.(result.message ?? "Validasi gagal", "error");
                            } else {
                                window.AppToast?.(result.message ?? "Gagal memperbarui profil", "error");
                            }
                        } catch (err) {
                            console.error(err);
                            window.AppToast?.("Terjadi kesalahan server", "error");
                        } finally {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-save"></i> Simpan';
                        }
                    });
                })
                ();
            </script>
        @endpush
    @endonce
@endauth
