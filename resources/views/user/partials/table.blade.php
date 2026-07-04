<div class="card shadow-sm border-0 animate-fade-in">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped align-middle text-center mb-0">
        <thead class="table-light text-center align-middle">
          <tr>
            <th width="50" class="text-center">No</th>
            <th width="70" class="text-center">Foto</th>
            <th class="text-center">Nama</th>
            <th class="text-center">Email</th>
            <th class="text-center">Role</th>
            <th class="text-center">Dibuat Pada</th>
            <th width="180" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $u)
          <tr>
            <td>{{ $loop->iteration }}</td>

            {{-- FOTO USER --}}
            <td>
              <img
                src="{{ $u->foto ? asset('assets/foto_admin/' . $u->foto) : asset('assets/img/default_staf.png') }}"
                onerror="this.src='{{ asset('assets/img/default_staf.png') }}'"
                style="width:42px;height:42px;object-fit:cover;object-position:center;box-shadow:0 0 0 2px rgba(255,255,255,0.35),0 1px 4px rgba(0,0,0,0.4);"
                class="rounded-circle"
              >
            </td>

            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->role->name ?? '-' }}</td>

            <td>
              {{ $u->created_at ? $u->created_at->format('d M Y H:i') : '-' }}
            </td>

            <td>
              <button class="btn btn-info btn-sm text-white btn-detail"
                data-id="{{ $u->id_user }}">
                <i class="bi bi-eye"></i>
              </button>

              <button class="btn btn-warning btn-sm text-white btn-edit"
                data-id="{{ $u->id_user }}">
                <i class="bi bi-pencil"></i>
              </button>

              <button class="btn btn-danger btn-sm btn-delete"
                data-id="{{ $u->id_user }}"
                data-name="{{ $u->name }}">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-muted py-4 text-center">
              Tidak ada data user
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>




