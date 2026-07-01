{{-- ======================================================
📄 MODALS — Tambah / Edit / Hapus / Detail Kode Surat
    Ditambahkan dukungan Bootstrap validation feedback.
====================================================== --}}

<div class="modal fade" id="addKodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formAddKode" method="POST">@csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Kode</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {{-- ID: add_kode | NAME: kode --}}
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" id="add_kode" class="form-control"
                               placeholder="Misal: S-P" maxlength="10" required>
                        {{-- Pesan error (invalid-feedback) akan di-inject di sini oleh JS --}}
                    </div>

                    {{-- ID: add_description | NAME: description --}}
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="add_description" class="form-control"
                                  rows="3" placeholder="Keterangan kode surat..."></textarea>
                        {{-- Pesan error (invalid-feedback) akan di-inject di sini oleh JS --}}
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="editKodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formEditKode" method="POST">@csrf
                {{-- ID: edit_id digunakan untuk mendapatkan ID yang akan diupdate --}}
                <input type="hidden" id="edit_id"> 

                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Kode</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {{-- ID: edit_kode | NAME: kode --}}
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" id="edit_kode" name="kode" class="form-control" maxlength="10" required>
                        {{-- Pesan error (invalid-feedback) akan di-inject di sini oleh JS --}}
                    </div>

                    {{-- ID: edit_description | NAME: description --}}
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                        {{-- Pesan error (invalid-feedback) akan di-inject di sini oleh JS --}}
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="bi bi-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteKodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formDeleteKode" method="POST">@csrf
            <input type="hidden" id="delete_id">

            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash3"></i> Hapus Kode</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <p>Yakin ingin menghapus kode ini?</p>
                    <h6 id="delete_kode_label" class="text-danger fw-bold"></h6>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="detailKodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-info-circle"></i> Detail Kode</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div><strong>Kode:</strong> <div id="detail_kode" class="text-dark"></div></div>
                <div class="mt-2"><strong>Deskripsi:</strong> <div id="detail_description" class="text-dark"></div></div>
            </div>

            <div class="modal-footer bg-light">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>




