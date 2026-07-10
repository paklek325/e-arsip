{{-- ==========================
      MODAL TAMBAH PESERTA DIDIK (FINAL)
=========================== --}}
<div class="modal fade" id="modalTambahPesertaDidik" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">

     <div class="modal-header bg-primary text-white">
  <h5 class="modal-title fw-bold">Tambah<span class="d-none d-sm-inline"> Peserta Didik</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      {{-- ALERT (AJAX) --}}
      <div class="alert alert-danger d-none mx-3 mt-3" id="alertTambahPesertaDidik"></div>

      <form id="formTambahPesertaDidik" enctype="multipart/form-data" autocomplete="off">
        @csrf

        <div class="modal-body">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nama Peserta Didik</label>
              <input type="text" name="nama_peserta_didik" id="add_nama_peserta_didik" class="form-control" required>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Jenis Kelamin</label>
              <select name="jenis_kelamin" id="add_jenis_kelamin" class="form-select" required>
                <option value="">-- Pilih --</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Tahun Angkatan</label>
              <input type="text" name="tahun_angkatan" class="form-control" placeholder="Contoh: 2026 atau 2025/2026" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Tempat Lahir</label>
              <input type="text" name="tempat_lahir" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Lahir</label>
              <input type="date" name="tanggal_lahir" id="add_tanggal_lahir" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Rombel</label>
              <select name="rombel" id="add_rombel" class="form-select" required>
                <option value="">-- Pilih Rombel --</option>
                <option value="A">A</option>
                <option value="B">B</option>
              </select>
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold">Alamat</label>
              <textarea name="alamat" id="add_alamat" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <hr>

          {{-- FIELDS UPLOAD --}}
          @php
            $fields = [
              'file_ppdb' => 'Formulir PPDB',
              'file_kk' => 'Kartu Keluarga',
              'file_akte' => 'Akte Kelahiran',
              'file_ktp' => 'KTP Orang Tua',
              'file_kts' => 'Kartu Peserta Didik',
              'file_foto' => 'Foto 3x4',
              'file_ijazah_smp' => 'Ijazah SMP',
              'file_ijazah_sma' => 'Ijazah SMA <span class="badge bg-secondary fw-normal ms-1">Opsional</span>',
            ];
          @endphp

          <div class="row g-3">
            @foreach ($fields as $key => $label)
              <div class="col-md-6">
                <div class="file-item">
                  <div class="form-check">
                    <input class="form-check-input file-check"
                          type="checkbox"
                          id="add_check_{{ $key }}"
                          data-target="#add_{{ $key }}">
                    <label class="form-check-label fw-semibold" for="add_check_{{ $key }}">{!! $label !!}</label>
                  </div>
                  <input type="file" name="{{ $key }}" id="add_{{ $key }}"
                         class="form-control mt-2 d-none file-input" disabled
                         accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </div>
              </div>
            @endforeach
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Simpan</button>
        </div>

      </form>
    </div>
  </div>
</div>




{{-- ==========================
      MODAL EDIT PESERTA DIDIK (FINAL)
=========================== --}}
<div class="modal fade" id="modalEditPesertaDidik" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Edit Peserta Didik</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      {{-- ALERT --}}
      <div class="alert alert-danger d-none mx-3 mt-3" id="alertEditPesertaDidik"></div>

      <form id="formEditPesertaDidik" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <input type="hidden" id="edit_id" name="id">

        <div class="modal-body">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nama Peserta Didik</label>
              <input type="text" name="nama_peserta_didik" id="edit_nama_peserta_didik" class="form-control">
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Jenis Kelamin</label>
              <select name="jenis_kelamin" id="edit_jenis_kelamin" class="form-select">
                <option value="">-- Pilih --</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Tahun Angkatan</label>
              <input type="text" name="tahun_angkatan" id="edit_tahun_angkatan" class="form-control" placeholder="Contoh: 2026 atau 2025/2026">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Tempat Lahir</label>
              <input type="text" name="tempat_lahir" id="edit_tempat_lahir" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Lahir</label>
              <input type="date" name="tanggal_lahir" id="edit_tanggal_lahir" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Rombel</label>
              <select name="rombel" id="edit_rombel" class="form-select">
                <option value="">-- Pilih Rombel --</option>
                <option value="A">A</option>
                <option value="B">B</option>
              </select>
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold">Alamat</label>
              <textarea name="alamat" id="edit_alamat" rows="2" class="form-control"></textarea>
            </div>
          </div>

          <hr>

          {{-- DOWNLOAD ALL --}}
          <div id="edit-download-all-container" class="mb-3 p-3 bg-white rounded shadow-sm"></div>

          {{-- FILE FIELDS --}}
          <div class="row g-3">
            @foreach ($fields as $key => $label)
              <div class="col-md-6">
                <div class="file-item">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input file-check"
                           id="edit_check_{{ $key }}" data-target="#edit_{{ $key }}">
                    <label class="form-check-label fw-semibold" for="edit_check_{{ $key }}">{!! $label !!}</label>
                  </div>
                  <input type="file" name="{{ $key }}"
                         id="edit_{{ $key }}"
                         class="form-control mt-2 d-none file-input"
                         data-server-file="0"
                         accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </div>
              </div>
            @endforeach
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Perbarui</button>
        </div>

      </form>

    </div>
  </div>
</div>




{{-- ==========================
      MODAL HAPUS PESERTA DIDIK
=========================== --}}
<div class="modal fade" id="modalHapusPesertaDidik" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">

      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold">Hapus Peserta Didik</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="formHapusPesertaDidik">
        @csrf
        @method("DELETE")

        <input type="hidden" id="hapus_id">

        <div class="modal-body text-center">
          <i class="bi bi-exclamation-triangle-fill text-danger pd-delete-icon"></i>
          <h5 class="fw-bold mt-3">Yakin ingin menghapus peserta_didik ini?</h5>
          <p class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan.</p>
          <p id="hapus_nama_peserta_didik" class="fw-bold text-danger"></p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Ya, Hapus</button>
        </div>
      </form>

    </div>
  </div>
</div>




{{-- ==========================
      MODAL DETAIL PESERTA DIDIK (FINAL)
=========================== --}}
<div class="modal fade" id="modalDetailPesertaDidik" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">

      <div class="modal-header bg-info text-white">
        <h5 class="modal-title fw-bold">Detail Peserta Didik</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="row g-3">
          <div class="col-md-6"><label class="form-label fw-semibold text-muted small mb-1">Nama Peserta Didik</label><p id="detail_nama_peserta_didik" class="detail-box"></p></div>
          <div class="col-md-3"><label class="form-label fw-semibold text-muted small mb-1">Jenis Kelamin</label><p id="detail_jenis_kelamin" class="detail-box"></p></div>
          <div class="col-md-3"><label class="form-label fw-semibold text-muted small mb-1">Tahun Angkatan</label><p id="detail_tahun_angkatan" class="detail-box"></p></div>
          <div class="col-md-6"><label class="form-label fw-semibold text-muted small mb-1">Tempat Lahir</label><p id="detail_tempat_lahir" class="detail-box"></p></div>
          <div class="col-md-6"><label class="form-label fw-semibold text-muted small mb-1">Tanggal Lahir</label><p id="detail_tanggal_lahir" class="detail-box"></p></div>
          <div class="col-md-6"><label class="form-label fw-semibold text-muted small mb-1">Rombel</label><p id="detail_rombel" class="detail-box"></p></div>
          <div class="col-md-12"><label class="form-label fw-semibold text-muted small mb-1">Alamat</label><p id="detail_alamat" class="detail-box"></p></div>
        </div>

        <hr>

        {{-- tombol unduh semua & unduh terpilih --}}
        <div id="detail-download-all-container" class="mb-2 p-2 bg-white rounded shadow-sm"></div>
        <div id="detail-download-selected-container" class="mb-3 p-2 bg-white rounded shadow-sm"></div>

        <div class="row g-3">
          @foreach ($fields as $key => $label)
            <div class="col-md-6">
              <div class="file-item">
                <div class="d-flex justify-content-between align-items-start">
                  <span class="fw-semibold">{!! $label !!}</span>

                  <div class="form-check mt-1">
                    <input class="form-check-input file-select-checkbox"
                           type="checkbox"
                           id="detail_select_{{ $key }}"
                           data-field="{{ $key }}">
                    <label class="form-check-label small text-muted" for="detail_select_{{ $key }}">
                      Pilih (ZIP)
                    </label>
                  </div>
                </div>

                <div id="detail_{{ $key }}" class="d-none mt-2"></div>

                <span id="detail_{{ $key }}_none" class="text-muted small mt-2 d-block">Tidak ada file</span>
              </div>
            </div>
          @endforeach
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>




{{-- ==========================
      MODAL PREVIEW FILE
=========================== --}}
<div class="modal fade" id="modalPreviewFile" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title text-truncate">
          <i class="bi bi-eye me-2"></i>
          <span id="pd_preview_filename_label">Pratinjau File</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body position-relative p-0 modal-loader-body">
        <div id="pd-file-loading-spinner"
             class="justify-content-center align-items-center modal-loader-flex">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memuat...</span>
          </div>
        </div>
        <div id="pd-preview-container" class="text-center p-2"></div>
      </div>

      <div class="modal-footer bg-light">
        <a id="pd_previewDownloadLink" href="#" class="btn btn-success" target="_blank">
          <i class="bi bi-download me-1"></i> Download
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>