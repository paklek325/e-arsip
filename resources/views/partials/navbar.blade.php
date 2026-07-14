<header class="pc-header">
    <div class="header-wrapper">

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
                        <span class="toggle-thumb">
                            <i class="bi bi-sun-fill sun"></i>
                            <i class="bi bi-moon-stars-fill moon"></i>
                        </span>
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
                        <a class="pc-head-link dropdown-toggle arrow-none me-0 d-flex align-items-center gap-2" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false" style="height:auto; width:auto; padding: 4px 8px; border-radius: 10px;">
                            <img src="{{ $fotoUrl }}" onerror="this.src='{{ asset('assets/img/default_staf.png') }}'"
                                alt="Foto {{ $authUser->name }}" class="user-avatar"
                                width="35" height="35"
                                loading="eager" fetchpriority="high" decoding="sync">
                            {{-- Nama & role — selalu tampil, termasuk di mobile --}}
                            <div class="d-flex flex-column" style="line-height:1; min-width:0;">
                                <span class="navbar-username">{{ $authUser->name }}</span>
                                <span class="navbar-user-role">{{ $roleName }}</span>
                            </div>
                            <i class="bi bi-chevron-down" style="font-size:.6rem; color:#9ca3af; margin-left:2px;"></i>
                        </a>

                        <div id="userProfileDropdown"
                            class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown p-0 overflow-hidden">

                            {{-- Header dropdown: foto + nama + role + tombol logout --}}
                            <div class="dropdown-header d-flex align-items-center gap-3 dropdown-user-header">
                                <img src="{{ $fotoUrl }}"
                                    onerror="this.src='{{ asset('assets/img/default_staf.png') }}'"
                                    alt="{{ $authUser->name }}" class="img-radius wid-35 dropdown-user-avatar"
                                    width="35" height="35"
                                    loading="lazy" decoding="async">
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
                                        <i class="bi bi-pencil-square fs-6 text-primary"></i>
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
                                    onerror="this.src='{{ asset('assets/img/default_staf.png') }}'; this.onerror=null;"
                                    width="100" height="100"
                                    loading="lazy" decoding="async" alt="Pratinjau Foto Profil">
                            </div>
                            <input type="file" name="foto" id="profil_foto" class="form-control mt-2"
                                accept="image/jpeg,image/png,image/jpg" aria-label="Pilih Foto Profil">
                            <small class="text-muted">Format JPG/PNG, maks. 4MB</small>
                            <div class="invalid-feedback d-block" id="error_profil_foto"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="profil_name">Nama</label>
                            <input type="text" name="name" id="profil_name" class="form-control"
                                value="{{ $authUser->name }}" required autocomplete="name">
                            <div class="invalid-feedback" id="error_profil_name"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="profil_email">Email</label>
                            <input type="email" name="email" id="profil_email" class="form-control"
                                value="{{ $authUser->email }}" required autocomplete="email">
                            <div class="invalid-feedback" id="error_profil_email"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="profil_role">Role</label>
                            <input type="text" id="profil_role" name="role" class="form-control bg-body-secondary" value="{{ $roleName }}"
                                readonly autocomplete="off">
                            <div class="form-text">Role tidak dapat diubah sendiri.</div>
                        </div>

                        <div class="mb-1">
                            <label class="form-label" for="profil_password">Password Baru (Opsional)</label>
                            <div class="input-group">
                                <input type="password" name="password" id="profil_password" class="form-control"
                                    placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="new-password">
                                <span class="input-group-text togglePasswordProfil" style="cursor: pointer;">
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
                    let pendingShowCrop = false;

                    // Bootstrap bisa gagal membersihkan modal-open dari <body> saat
                    // transisi antar-modal dipotong (tap cepat di HP). Fungsi ini
                    // memastikan body bersih setelah semua modal benar-benar tertutup.
                    function cleanupBodyModal() {
                        if (!document.querySelector('.modal.show')) {
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }
                    }

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

                    // ── Setelah navProfilModal sepenuhnya tertutup ───────────────
                    // Buka cropModal hanya setelah animasi close selesai agar
                    // Bootstrap bisa membersihkan state-nya terlebih dahulu.
                    modalEl.addEventListener("hidden.bs.modal", () => {
                        if (pendingShowCrop) {
                            pendingShowCrop = false;
                            cropModal.show();
                        } else {
                            cleanupBodyModal();
                        }
                    });

                    // ── Pilih foto baru → buka modal crop ───────────────────────
                    fotoInput?.addEventListener("change", (e) => {
                        const file = e.target.files[0];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            cropImage.src = ev.target.result;
                            reopenProfil = true;
                            pendingShowCrop = true;
                            profilModal.hide();
                            // cropModal.show() dipanggil di hidden.bs.modal agar
                            // animasi close navProfilModal selesai dulu.
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
                        } else {
                            cleanupBodyModal();
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