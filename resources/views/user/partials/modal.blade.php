{{-- =========== Modal Tambah User============= --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formAddUser" method="POST" enctype="multipart/form-data">@csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- FOTO --}}
                    <div class="mb-3 text-center">
                        <img id="previewFotoAdd"
                            src="{{ asset('assets/img/noimage.png') }}" 
                            data-default="{{ asset('assets/img/noimage.png') }}"
                            class="rounded-circle shadow-sm"
                            width="100" height="100">
                        <input type="file" name="foto" id="add_foto" 
                            class="form-control mt-2" accept="image/*">
                         <div class="invalid-feedback d-block" id="error_add_foto"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" id="add_name" class="form-control" required>
                        <div class="invalid-feedback" id="error_add_name"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="add_email" class="form-control" required>
                        <div class="invalid-feedback" id="error_add_email"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="add_password" class="form-control" required>
                            <span class="input-group-text togglePasswordAdd" style="cursor:pointer;">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback d-block" id="error_add_password"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        {{-- Pastikan variabel $roles sudah dilewatkan dari Controller --}}
                        <select name="id_role" id="add_role" class="form-select" required>
                            <option value="" disabled selected>Pilih Role</option>
                            @foreach($roles as $r)
                            <option value="{{ $r->id_role }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="error_add_id_role"></div>
                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- =========== Modal Edit User============= --}}

<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            {{-- Menggunakan POST method dengan field tersembunyi _method=PUT karena FormData --}}
            <form id="formEditUser" method="POST" enctype="multipart/form-data">@csrf 
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {{-- Hidden input untuk ID user. Menggunakan ID yang sama dengan JS: edit_id --}}
                    {{-- Perbaikan: Name diubah menjadi id_user (jika di Controller/Model PK-nya adalah id_user) --}}
                    <input type="hidden" name="id_user" id="edit_id">
                    
                    {{-- FOTO --}}
                    <div class="mb-3 text-center">
                        <img id="previewFotoEdit"
                            src="{{ asset('assets/img/noimage.png') }}"
                            data-default="{{ asset('assets/img/noimage.png') }}"
                            class="rounded-circle shadow-sm"
                            width="100" height="100">
                        <input type="file" name="foto" id="edit_foto" class="form-control mt-2" accept="image/*">
                        <div class="invalid-feedback d-block" id="error_edit_foto"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                        <div class="invalid-feedback" id="error_edit_name"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                        <div class="invalid-feedback" id="error_edit_email"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password (Opsional)</label>
                        <div class="input-group">
                            <input type="password" name="password" id="edit_password" class="form-control">
                            <span class="input-group-text togglePasswordEdit" style="cursor:pointer;">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                        <small class="text-muted">Kosongkan bila tidak ingin mengubah password</small>
                        <div class="invalid-feedback d-block" id="error_edit_password"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        {{-- Pastikan variabel $roles sudah dilewatkan dari Controller --}}
                        <select name="id_role" id="edit_role" class="form-select" required>
                            @foreach($roles as $r)
                            <option value="{{ $r->id_role }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="error_edit_id_role"></div>
                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ============  Modal Detail User ============ --}}
<div class="modal fade" id="detailUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-person-badge"></i> Detail User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <img id="detail_foto" class="rounded-circle shadow-sm mb-3" width="100" height="100">
                <p><strong>Nama:</strong> <span id="detail_name"></span></p>
                <p><strong>Email:</strong> <span id="detail_email"></span></p>
                <p><strong>Role:</strong> <span id="detail_role"></span></p>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


{{-- =========  Modal Hapus User===============  --}}

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            {{-- Menggunakan POST method dengan field tersembunyi _method=DELETE karena FormData --}}
            <form id="formDeleteUser" method="POST">@csrf 
                <input type="hidden" name="id" id="delete_id">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash"></i> Hapus User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size:3rem;"></i>
                    <h5 class="mt-3">Yakin ingin menghapus user ini?</h5>
                    <p class="text-muted mb-0">User: <strong id="delete_name_label"></strong></p>
                </div>

                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>




