<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0 table-hover text-nowrap table-custom">
                <thead class="table-light">
                    <tr class="text-center">
                        <th style="width:50px;">No</th>
                        <th style="min-width:200px;">Nama Peserta Didik</th>
                        <th style="min-width:110px;">Jenis Kelamin</th>
                        <th style="min-width:110px;">Tahun Angkatan</th>
                        <th style="min-width:90px;">Rombel</th>
                        <th style="min-width:140px;">Status</th>
                        <th style="width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
@forelse (($peserta_didik ?? collect()) as $item)
                        <tr>
                            <td class="text-center text-muted small fw-semibold">
                                {{ ($peserta_didik->currentPage() - 1) * $peserta_didik->perPage() + $loop->iteration }}
                            </td>
                            <td class="fw-semibold nama-peserta-didik">{{ $item->nama_peserta_didik }}</td>
                            <td class="text-center">
                                {{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : ($item->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                            </td>
                            <td class="text-center">{{ $item->tahun_angkatan ?? '-' }}</td>
                            <td class="text-center">{{ $item->rombel ?? '-' }}</td>
                            <td class="text-center">
                                @if(strtolower((string) $item->status) === 'lengkap')
                                    <span class="badge rounded-pill" style="background:#22c55e;color:#fff;font-size:0.75rem;padding:0.35em 0.75em;">
                                        <i class="bi bi-check-circle me-1"></i>Lengkap
                                    </span>
                                @else
                                    <span class="badge rounded-pill" style="background:#f59e0b;color:#fff;font-size:0.75rem;padding:0.35em 0.75em;">
                                        <i class="bi bi-exclamation-circle me-1"></i>Belum Lengkap
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center gap-1 flex-nowrap">
                                    <button class="btn btn-sm btn-info text-white btn-view-peserta-didik" data-id="{{ $item->id_peserta_didik }}" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning text-white btn-edit-peserta-didik" data-id="{{ $item->id_peserta_didik }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-delete-peserta-didik" data-id="{{ $item->id_peserta_didik }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                    Tidak ada data peserta didik ditemukan.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-light border-top py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            @if(isset($peserta_didik) && method_exists($peserta_didik,'total'))
            <small class="text-muted">
                Menampilkan {{ $peserta_didik->firstItem() ?? 0 }}–{{ $peserta_didik->lastItem() ?? 0 }} dari {{ $peserta_didik->total() }} data
            </small>
            @endif
            <div>
@if(isset($peserta_didik) && method_exists($peserta_didik,'links'))
    {{ $peserta_didik->appends(request()->query())->links() }}
@endif
            </div>
        </div>
    </div>
</div>