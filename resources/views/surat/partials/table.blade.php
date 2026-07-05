<div class="card shadow-sm border-0" id="tableContainer" data-base-url="{{ url('/surat') }}">
    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-bordered table-striped align-middle mb-0 table-hover text-nowrap table-custom">

                <thead class="table-light">
                    <tr class="text-center">

                        <th class="surat-col-no">No</th>
                        <th class="surat-col-nomor">Nomor Surat</th>
                        <th class="surat-col-kode">Kode</th>
                        <th class="surat-col-jenis">Jenis</th>
                        <th class="surat-col-perihal">Perihal</th>
                        <th class="surat-col-instansi">Instansi</th>
                        <th class="surat-col-aksi">Aksi</th>

                    </tr>
                </thead>

                <tbody>

@php
$romans = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
@endphp

@forelse (($surat ?? collect()) as $item)

@php
$bulanRoman = $item->tanggal_surat ? $romans[$item->tanggal_surat->month - 1] : '';
$tahun      = $item->tanggal_surat ? $item->tanggal_surat->year : '';
$kodeStr    = $item->kode_surat ?: ($item->kode?->kode ?? '');
$parts      = array_filter([$item->no_surat, $kodeStr, $item->instansi, $bulanRoman, $tahun]);
$nomorLengkap = implode('/', $parts);
@endphp

<tr>

    <td class="text-center text-muted fw-semibold">
        {{ ($surat->currentPage() - 1) * $surat->perPage() + $loop->iteration }}
    </td>

    {{-- NOMOR SURAT (format lengkap dirakit dari field) --}}
    <td class="fw-semibold text-break">
        {{ $nomorLengkap ?: $item->no_surat }}
    </td>

    {{-- KODE SURAT --}}
    <td class="text-center">
        @if($item->jenis_surat === 'Keluar')
            @if($item->kode)
                <span class="badge bg-primary">{{ $item->kode->kode }}</span>
                @if($item->kode->description)
                    <div class="small text-muted">{{ $item->kode->description }}</div>
                @endif
            @elseif($item->kode_surat)
                <span class="badge bg-secondary">{{ $item->kode_surat }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        @else
            @if($item->kode_surat)
                <span class="badge bg-secondary">{{ $item->kode_surat }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        @endif
    </td>

    {{-- JENIS --}}
    <td class="text-center">
        @if($item->jenis_surat === 'Keluar')
            <span class="badge rounded-pill bg-success">
                <i class="bi bi-box-arrow-up-right me-1"></i>Keluar
            </span>
        @else
            <span class="badge rounded-pill bg-info">
                <i class="bi bi-box-arrow-in-down me-1"></i>Masuk
            </span>
        @endif
    </td>

    {{-- PERIHAL --}}
    <td class="text-break">
        {{ Str::limit($item->perihal, 50) }}
    </td>

    {{-- INSTANSI --}}
    <td class="text-break">
        {{ $item->instansi ?: '-' }}
    </td>

    {{-- AKSI --}}
    <td>
        <div class="d-flex justify-content-center gap-1">

            <button class="btn btn-sm btn-info text-white btn-view"
                    data-id="{{ $item->id }}" title="Detail">
                <i class="bi bi-eye"></i>
            </button>

            <button class="btn btn-sm btn-warning text-white btn-edit"
                    data-id="{{ $item->id }}" title="Edit">
                <i class="bi bi-pencil-square"></i>
            </button>

            <button class="btn btn-sm btn-danger btn-delete"
                    data-id="{{ $item->id }}" title="Hapus">
                <i class="bi bi-trash"></i>
            </button>

        </div>
    </td>

</tr>

@empty

<tr>
    <td colspan="7" class="text-center py-5">
        <div class="text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            Tidak ada data surat.
        </div>
    </td>
</tr>

@endforelse
                </tbody>

            </table>

        </div>
    </div>

    {{-- Footer --}}
    <div class="card-footer bg-white border-top py-3">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            @if(isset($surat) && method_exists($surat,'total'))
                <small class="text-muted">
                    Menampilkan
                    <strong>{{ $surat->firstItem() ?? 0 }}</strong>
                    -
                    <strong>{{ $surat->lastItem() ?? 0 }}</strong>
                    dari
                    <strong>{{ $surat->total() }}</strong>
                    data surat
                </small>
            @endif

            <div>
                @if(isset($surat) && method_exists($surat,'links'))
                    {{ $surat->appends(request()->query())->links() }}
                @endif
            </div>

        </div>

    </div>

</div>
