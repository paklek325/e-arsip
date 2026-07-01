<div class="card shadow-sm border-0" id="tableContainer" data-base-url="{{ url('/surat') }}">
    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-bordered table-striped align-middle mb-0 table-hover text-nowrap table-custom">

                <thead class="table-light">
                    <tr class="text-center">

                        <th style="width:60px;">
                            No
                        </th>

                        <th style="min-width:170px;">
                            Nomor Surat
                        </th>

                        <th style="min-width:170px;">
                            Kode Surat
                        </th>

                        <th style="width:110px;">
                            Jenis
                        </th>

                        <th style="min-width:260px;">
                            Perihal
                        </th>

                        <th style="min-width:220px;">
                            Instansi
                        </th>

                        <th style="width:140px;">
                            Tanggal Surat
                        </th>

                        <th style="width:120px;">
                            Aksi
                        </th>

                    </tr>
                </thead>

                <tbody>

@forelse (($surat ?? collect()) as $item)

<tr>

    <td class="text-center text-muted fw-semibold">

        {{ ($surat->currentPage() - 1) * $surat->perPage() + $loop->iteration }}

    </td>

    <td class="fw-semibold text-break">

        {{ $item->no_surat }}

        </td>

    {{-- =========================
        KODE SURAT
    ========================== --}}
    <td>

        @if($item->jenis_surat === 'Keluar')

            @if($item->kode)

                <span class="badge bg-primary">
                    {{ $item->kode->kode }}
                </span>

                <div class="small text-muted mt-1">
                    {{ $item->kode->description }}
                </div>

            @else

                <span class="text-muted">
                    -
                </span>

            @endif

        @else

            @if($item->kode_surat)

                <span class="badge bg-secondary">
                    {{ $item->kode_surat }}
                </span>

            @else

                <span class="text-muted">
                    -
                </span>

            @endif

        @endif

    </td>

    {{-- =========================
        JENIS SURAT
    ========================== --}}
    <td class="text-center">

        @if($item->jenis_surat == 'Masuk')

            <span class="badge rounded-pill bg-info">
                <i class="bi bi-box-arrow-in-down me-1"></i>
                Masuk
            </span>

        @else

            <span class="badge rounded-pill bg-success">
                <i class="bi bi-box-arrow-up-right me-1"></i>
                Keluar
            </span>

        @endif

    </td>

    {{-- =========================
        PERIHAL
    ========================== --}}
    <td class="text-break">

        {{ Str::limit($item->perihal,60) }}

    </td>

    {{-- =========================
        INSTANSI
    ========================== --}}
    <td class="text-break">

        {{ $item->instansi ?: '-' }}

    </td>

    {{-- =========================
        TANGGAL SURAT
    ========================== --}}
    <td class="text-center">

        {{ optional($item->tanggal_surat)->translatedFormat('d M Y') }}

    </td>

    {{-- =========================
        AKSI
    ========================== --}}
    <td>

        <div class="d-flex justify-content-center gap-1">

            <button
                class="btn btn-sm btn-info text-white btn-view"
                data-id="{{ $item->id }}"
                title="Detail">

                <i class="bi bi-eye"></i>

            </button>

            <button
                class="btn btn-sm btn-warning text-white btn-edit"
                data-id="{{ $item->id }}"
                title="Edit">

                <i class="bi bi-pencil-square"></i>

            </button>

            <button
                class="btn btn-sm btn-danger btn-delete"
                data-id="{{ $item->id }}"
                title="Hapus">

                <i class="bi bi-trash"></i>

            </button>

        </div>

    </td>

</tr>

@empty

<tr>

    <td colspan="8" class="text-center py-5">

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