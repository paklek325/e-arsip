{{-- ====================================================== 
📄 MODALS — Tambah / Edit / Hapus / Detail Surat
Sinkron dengan SuratController & JS (preview + print + multi download)
====================================================== --}}

{{-- 1. MODAL TAMBAH SURAT --}}
<div class="modal fade" id="addSuratModal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form id="addSuratForm" enctype="multipart/form-data" method="POST" class="modal-content">
      @csrf

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="bi bi-plus-circle me-2"></i> Tambah Surat Baru
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        {{-- ALERT TAMBAH (UNTUK DUPLIKAT / ERROR VALIDASI) --}}
        <div id="addSuratAlert" class="alert d-none mb-3" role="alert"></div>

        <div class="row g-3">

          <div class="col-md-6">
            <label for="add_no_surat" class="form-label">Nomor Surat <span class="text-danger">*</span></label>
            <input type="text" name="no_surat" id="add_no_surat"
                   class="form-control" required>
          </div>

          <div class="col-md-6">
            <label for="jenis_surat_add" class="form-label">Jenis Surat <span class="text-danger">*</span></label>
            <select id="jenis_surat_add" name="jenis_surat" class="form-select" required>
              <option value="">-- Pilih Jenis --</option>
              <option value="Masuk">Masuk</option>
              <option value="Keluar">Keluar</option>
            </select>
          </div>

          <div class="col-md-12" id="kode-container-add">
            <label for="add_kode_surat" class="form-label">Kode Surat</label>
            <input type="text" name="kode_surat" id="add_kode_surat"
                   class="form-control"
                   placeholder="Akan aktif jika jenis surat 'Keluar'" disabled>
            <small class="text-muted d-block mt-1" id="kode-info-add">
              Wajib jika jenis surat <strong>Keluar</strong>.
            </small>
          </div>

          <div class="col-md-6">
            <label for="add_tanggal" class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="date" name="tanggal_surat" id="add_tanggal"
                     class="form-control" required>
            </div>
          </div>

          <div class="col-md-6">
            <label for="add_perihal" class="form-label">Perihal <span class="text-danger">*</span></label>
            <input type="text" name="perihal" id="add_perihal"
                   class="form-control" required>
          </div>

          {{-- INSTANSI --}}
          <div class="col-md-6">
            <label for="add_instansi" class="form-label">Instansi <span class="text-danger">*</span></label>
            <input type="text" name="instansi" id="add_instansi"
                   class="form-control" required>
          </div>

          <div class="col-md-6">
            <label for="add_pengirim" class="form-label">Pengirim</label>
            <input type="text" name="pengirim" id="add_pengirim"
                   class="form-control">
          </div>

          <div class="col-md-6">
            <label for="add_penerima" class="form-label">Penerima</label>
            <input type="text" name="penerima" id="add_penerima"
                   class="form-control">
          </div>

          <div class="col-md-12">
            <label for="file_surat_add" class="form-label">
              Lampiran File <span class="text-danger">*</span>
            </label>
            <input
              type="file"
              name="file_surat[]"
              multiple
              id="file_surat_add"
              class="form-control"
              required>
            <small class="text-muted d-block mt-1">
              Bisa beberapa file sekaligus.
            </small>
          </div>

        </div>
      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Batal
        </button>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i> Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- 2. MODAL EDIT SURAT --}}
<div class="modal fade" id="editSuratModal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form id="editSuratForm" enctype="multipart/form-data" method="POST" class="modal-content">
      @csrf
      @method('PUT')
      <input type="hidden" name="id" id="edit_id">

      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title">
          <i class="bi bi-pencil-square me-2"></i> Edit Surat
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        {{-- ALERT EDIT --}}
        <div id="editSuratAlert" class="alert d-none mb-3" role="alert"></div>

        <div class="row g-3">

          <div class="col-md-6">
            <label for="edit_no_surat" class="form-label">Nomor Surat <span class="text-danger">*</span></label>
            <input type="text" id="edit_no_surat" name="no_surat"
                   class="form-control" required>
          </div>

          <div class="col-md-6">
            <label for="edit_jenis" class="form-label">Jenis Surat <span class="text-danger">*</span></label>
            <select id="edit_jenis" name="jenis_surat" class="form-select" required>
              <option value="Masuk">Masuk</option>
              <option value="Keluar">Keluar</option>
            </select>
          </div>

          <div class="col-md-12" id="kode-container-edit">
            <label for="kode_input_edit" class="form-label">Kode Surat</label>
            <input type="text" id="kode_input_edit" name="kode_surat"
                   class="form-control"
                   placeholder="Akan aktif jika jenis surat 'Keluar'" disabled>
            <small class="text-muted d-block mt-1" id="kode-info-edit">
              Wajib jika jenis surat <strong>Keluar</strong>.
            </small>
          </div>

          <div class="col-md-6">
            <label for="edit_tanggal" class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="date" id="edit_tanggal" name="tanggal_surat"
                     class="form-control" required>
            </div>
          </div>

          <div class="col-12">
            <label for="edit_perihal" class="form-label">Perihal <span class="text-danger">*</span></label>
            <textarea id="edit_perihal" name="perihal"
                      rows="2" class="form-control" required></textarea>
          </div>

          {{-- INSTANSI --}}
          <div class="col-md-6">
            <label for="edit_instansi" class="form-label">Instansi <span class="text-danger">*</span></label>
            <input type="text" id="edit_instansi" name="instansi"
                   class="form-control" required>
          </div>

          <div class="col-md-6">
            <label for="edit_pengirim" class="form-label">Pengirim</label>
            <input type="text" id="edit_pengirim" name="pengirim"
                   class="form-control">
          </div>

          <div class="col-md-6">
            <label for="edit_penerima" class="form-label">Penerima</label>
            <input type="text" id="edit_penerima" name="penerima"
                   class="form-control">
          </div>

          <div class="col-12">
            <label class="form-label">File Lama</label>
            <div id="edit_file_list" class="list-group mb-1"></div>
            <small class="text-muted">
              Tekan tombol <i class="bi bi-trash"></i> untuk menandai file yang akan dihapus
              ketika Anda menekan tombol <strong>Update</strong>.
            </small>
          </div>

          <div class="col-12">
            <label for="edit_file" class="form-label">Tambah File Baru (opsional)</label>
            <input
              type="file"
              id="edit_file"
              name="file_surat[]"
              multiple
              class="form-control">
            <small class="text-muted d-block mt-1">
              File baru akan ditambahkan ke daftar file lama.
            </small>
          </div>

        </div>
      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Batal
        </button>
        <button type="submit" class="btn btn-warning text-white">
          <i class="bi bi-save me-1"></i> Update
        </button>
      </div>
    </form>
  </div>
</div>

{{-- 3. MODAL HAPUS SURAT --}}
<div class="modal fade" id="deleteSuratModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="deleteSuratForm" method="POST" class="modal-content">
      @csrf
      @method('DELETE')
      <input type="hidden" id="deleteSuratId" name="id">

      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="bi bi-trash3 me-2"></i> Hapus Surat
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">
        <p>
          Apakah Anda yakin ingin menghapus surat dengan Nomor Surat:
          <span id="delete_no_surat_text" class="fw-bold text-danger text-break"></span>?
        </p>
        <p class="text-muted small mb-0">
          Tindakan ini <strong>tidak dapat dibatalkan</strong>.
        </p>
      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Batal
        </button>
        <button type="submit" class="btn btn-danger">
          <i class="bi bi-trash me-1"></i> Hapus
        </button>
      </div>
    </form>
  </div>
</div>
{{-- ======================================================
4. MODAL DETAIL SURAT
====================================================== --}}
<div class="modal fade" id="viewSuratModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Detail Surat
                </h5>

                <button class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- ===========================
                    INFORMASI SURAT
                ============================ --}}
                <div class="card border-0 shadow-sm mb-3">

                    <div class="card-header bg-light fw-bold">

                        <i class="bi bi-envelope-paper me-2"></i>

                        Informasi Surat

                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <small class="text-muted">Nomor Surat</small>
                                <div id="view_no_surat" class="fw-semibold"></div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Kode Surat</small>
                                <div id="view_kode"></div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Jenis Surat</small>
                                <div id="view_jenis"></div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Tanggal Surat</small>
                                <div id="view_tanggal"></div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Instansi</small>
                                <div id="view_instansi"></div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Pengirim</small>
                                <div id="view_pengirim"></div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Penerima</small>
                                <div id="view_penerima"></div>
                            </div>

                            <div class="col-12">
                                <small class="text-muted">Perihal</small>
                                <div id="view_perihal"></div>
                            </div>

                            <div class="col-12">
                                <small class="text-muted">Keterangan</small>
                                <div id="view_keterangan"></div>
                            </div>

                        </div>

                    </div>

                </div>

                {{-- ===========================
                    INFORMASI ARSIP
                ============================ --}}
                <div class="card border-0 shadow-sm mb-3">

                    <div class="card-header bg-light fw-bold">

                        <i class="bi bi-clock-history me-2"></i>

                        Informasi Arsip

                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <small class="text-muted">
                                    Ditambahkan Oleh
                                </small>

                                <div id="view_created_by"
                                     class="fw-semibold"></div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">
                                    Tanggal Dibuat
                                </small>

                                <div id="view_created_at"></div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">
                                    Terakhir Diubah Oleh
                                </small>

                                <div id="view_updated_by"
                                     class="fw-semibold"></div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">
                                    Tanggal Diubah
                                </small>

                                <div id="view_updated_at"></div>
                            </div>

                            <div class="col-md-12">
                                <small class="text-muted">
                                    Status
                                </small>

                                <div id="view_status"></div>
                            </div>

                        </div>

                    </div>

                </div>

                {{-- ===========================
                    LAMPIRAN
                ============================ --}}
                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-light d-flex justify-content-between">

                        <strong>

                            <i class="bi bi-paperclip me-2"></i>

                            Lampiran

                        </strong>

                        <small id="lampiran_count"></small>

                    </div>

                    <div id="view_file"
                         class="list-group list-group-flush">
                    </div>

                </div>

            </div>

            <div class="modal-footer bg-light">

                <button id="btnPrintSelected"
                        class="btn btn-primary">

                    <i class="bi bi-printer me-1"></i>

                    Print

                </button>

                <button id="btnDownloadSelected"
                        class="btn btn-success">

                    <i class="bi bi-download me-1"></i>

                    Download Terpilih

                </button>

                <button id="btnDownloadAll"
                        class="btn btn-outline-success">

                    <i class="bi bi-download me-1"></i>

                    Download Semua

                </button>

                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Tutup

                </button>

            </div>

        </div>
    </div>
</div>

{{-- ===========================
    MODAL PREVIEW FILE (gambar/dokumen lampiran)
    Dipakai oleh openPreview() di resources/js/surat.js
============================ --}}
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-truncate">
                    <i class="bi bi-eye me-2"></i>
                    <span id="preview_filename_label">Pratinjau File</span>
                </h5>

                <button class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body position-relative p-0" style="min-height: 200px;">

                <div id="file-loading-spinner"
                     class="d-flex justify-content-center align-items-center"
                     style="min-height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                </div>

                <div id="filePreviewBody" class="text-center p-2"></div>

            </div>

            <div class="modal-footer bg-light">

                <a id="previewDownloadLink"
                   href="#"
                   class="btn btn-success"
                   target="_blank">

                    <i class="bi bi-download me-1"></i>

                    Download

                </a>

                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Tutup

                </button>

            </div>

        </div>
    </div>
</div>