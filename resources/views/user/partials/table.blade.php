<div class="user-card">
  <div class="table-responsive">
    <table class="table table-user mb-0 text-center">
      <thead class="text-center align-middle">
        <tr>
          <th width="50">No</th>
          <th width="70">Foto</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th>Dibuat Pada</th>
          <th width="150">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $u)
        <tr>
          <td class="fw-medium">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>

          <td>
            <img src="{{ $u->foto ? asset('storage/foto_admin/' . $u->foto) : asset('assets/img/default_staf.png') }}"
                 onerror="this.src='{{ asset('assets/img/default_staf.png') }}'"
                 class="user-avatar"
                 alt="Foto {{ $u->name }}">
          </td>

          <td class="fw-medium text-start">{{ $u->name }}</td>
          <td class="text-muted">{{ $u->email }}</td>
          <td>
            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-1 rounded-pill"
                  style="font-size:0.75rem;">
              {{ $u->role?->name ?? '-' }}
            </span>
          </td>
          <td class="text-muted" style="font-size:0.82rem;white-space:nowrap;">
            {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
          </td>
          <td>
            <div class="d-flex gap-1 justify-content-center">
              <button class="btn btn-info btn-sm text-white btn-detail"
                      data-id="{{ $u->id_user }}" title="Detail"
                      data-bs-toggle="tooltip">
                <i class="bi bi-eye"></i>
              </button>
              <button class="btn btn-warning btn-sm text-white btn-edit"
                      data-id="{{ $u->id_user }}" title="Edit"
                      data-bs-toggle="tooltip">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-danger btn-sm btn-delete"
                      data-id="{{ $u->id_user }}" data-name="{{ $u->name }}"
                      title="Hapus" data-bs-toggle="tooltip">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="py-5 text-center">
            <div class="text-muted">
              <i class="bi bi-people" style="font-size:2rem;opacity:.4;"></i>
              <p class="mt-2 mb-0">Belum ada data user</p>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($users->hasPages())
  <div class="user-card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">
      Menampilkan <strong>{{ $users->firstItem() }}</strong>–<strong>{{ $users->lastItem() }}</strong>
      dari <strong>{{ $users->total() }}</strong> user
    </small>
    {{ $users->links() }}
  </div>
  @endif
</div>




