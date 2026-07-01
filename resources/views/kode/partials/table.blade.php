<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-center table-hover mb-0 table-custom">
        <thead class="table-header text-center">
          <tr>
            <th width="50">No</th>
            <th>Kode</th>
            <th>Deskripsi</th>
            <th width="160">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($data as $i => $row)
            <tr>
              <td>{{ ($data->currentPage() - 1) * $data->perPage() + $i + 1 }}</td>
              <td>{{ $row->kode }}</td>
              <td>{{ $row->description ?? '-' }}</td>
              <td>
                <button type="button" class="btn btn-sm btn-info text-white btn-detail" data-id="{{ $row->id_kode }}" title="Lihat Detail">
                  <i class="bi bi-info-circle"></i>
                </button>
                <button type="button" class="btn btn-sm btn-warning text-white btn-edit" data-id="{{ $row->id_kode }}" title="Edit Data">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="{{ $row->id_kode }}" data-kode="{{ $row->kode }}" title="Hapus Data">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">Tidak ada data</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-footer bg-light border-top py-2">
    <div class="d-flex justify-content-end align-items-center">
      {{ $data->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>





