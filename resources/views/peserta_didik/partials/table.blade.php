<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        {{-- Desktop: tabel biasa (≥576px) --}}
        <div class="table-responsive no-mobile-card d-none d-sm-block">
            <table class="table table-bordered table-striped align-middle mb-0 table-hover table-custom">
                <thead class="table-light">
                    <tr class="text-center">
                        <th class="pd-col-no">No</th>
                        <th class="pd-col-nama">Nama Peserta Didik</th>
                        <th class="pd-col-jk">Jenis Kelamin</th>
                        <th class="pd-col-tahun">Tahun Angkatan</th>
                        <th class="pd-col-rombel">Rombel</th>
                        <th class="pd-col-status">Status</th>
                        <th class="pd-col-aksi">Aksi</th>
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
                                {{ $item->jenis_kelamin === 'L' ? 'L' : ($item->jenis_kelamin === 'P' ? 'Pr' : '-') }}
                            </td>
                            <td class="text-center">{{ $item->tahun_angkatan ?? '-' }}</td>
                            <td class="text-center">{{ $item->rombel ?? '-' }}</td>
                            <td class="text-center">
                                @if(strtolower((string) $item->status) === 'lengkap')
                                    <span class="badge rounded-pill badge-status-lengkap">
                                        <i class="bi bi-check-circle me-1"></i>Lengkap
                                    </span>
                                @else
                                    <span class="badge rounded-pill badge-status-belum">
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
                                    <button class="btn btn-sm btn-danger btn-delete-peserta-didik" data-id="{{ $item->id_peserta_didik }}" data-nama="{{ $item->nama_peserta_didik }}" title="Hapus">
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

        {{-- Mobile: kartu per peserta didik (≤575px) --}}
        <div class="d-block d-sm-none px-2 pt-2 pb-1">
            @forelse (($peserta_didik ?? collect()) as $item)
            <div class="mb-2 rounded-3 border overflow-hidden" style="background:var(--card-bg,#fff)">
                {{-- Nama saja --}}
                <div class="px-3 pt-2 pb-1">
                    <span class="fw-semibold" style="font-size:.88rem">{{ $item->nama_peserta_didik }}</span>
                </div>
                {{-- Semua badge sejajar satu baris --}}
                @php $bs = 'display:inline-flex;align-items:center;height:1.4rem;font-size:.68rem;font-weight:500;padding:0 .5em;line-height:1;border-radius:.3rem;border:none;white-space:nowrap;flex-shrink:0'; @endphp
                <div class="d-flex align-items-center gap-1 px-3 pb-2" style="overflow-x:auto;flex-wrap:nowrap;scrollbar-width:none">
                    @if(strtolower((string) $item->status) === 'lengkap')
                        <span style="{{ $bs }};background:#22c55e;color:#fff">Lengkap</span>
                    @else
                        <span style="{{ $bs }};background:#f59e0b;color:#fff">Belum Lengkap</span>
                    @endif
                    @if($item->jenis_kelamin === 'L')
                        <span style="{{ $bs }};background:#cfe2ff;color:#084298">Laki-laki</span>
                    @elseif($item->jenis_kelamin === 'P')
                        <span style="{{ $bs }};background:#f8d7da;color:#842029">Perempuan</span>
                    @endif
                    @if($item->tahun_angkatan)
                        <span style="{{ $bs }};background:#6c757d;color:#fff">{{ $item->tahun_angkatan }}</span>
                    @endif
                    @if($item->rombel)
                        <span style="{{ $bs }};background:#0ea5e9;color:#fff">{{ $item->rombel }}</span>
                    @endif
                </div>
                {{-- Baris tombol: border atas tipis --}}
                <div class="d-flex border-top" style="border-color:var(--border-color,#dee2e6)!important">
                    <button class="btn btn-sm btn-view-peserta-didik flex-fill py-2 border-0 rounded-0 text-info" data-id="{{ $item->id_peserta_didik }}" title="Detail" style="font-size:.8rem;background:transparent">
                        <i class="bi bi-eye me-1"></i>Detail
                    </button>
                    <div style="width:1px;background:var(--border-color,#dee2e6)"></div>
                    <button class="btn btn-sm btn-edit-peserta-didik flex-fill py-2 border-0 rounded-0 text-warning" data-id="{{ $item->id_peserta_didik }}" title="Edit" style="font-size:.8rem;background:transparent">
                        <i class="bi bi-pencil-square me-1"></i>Edit
                    </button>
                    <div style="width:1px;background:var(--border-color,#dee2e6)"></div>
                    <button class="btn btn-sm btn-delete-peserta-didik flex-fill py-2 border-0 rounded-0 text-danger" data-id="{{ $item->id_peserta_didik }}" data-nama="{{ $item->nama_peserta_didik }}" title="Hapus" style="font-size:.8rem;background:transparent">
                        <i class="bi bi-trash me-1"></i>Hapus
                    </button>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                Tidak ada data peserta didik ditemukan.
            </div>
            @endforelse
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
