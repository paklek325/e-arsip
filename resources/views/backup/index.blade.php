@extends('layouts.app')

@section('title', 'Backup Data — E-Arsip')



@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.index') }}"><i class="bi bi-house-door-fill"></i>Home</a>
    </li>
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Backup Data</li>
</ol>
@endsection

@section('content')
<div class="py-2" id="page-backup">

    {{-- ─── PAGE HEADER (Sejajar dengan Halaman Master Lain) ──────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-database-down fs-5 text-primary"></i>
            <h5 class="fw-bold text-primary mb-0">Backup Data</h5>
        </div>
    </div>

    {{-- ─── STATISTIK (Sejajar 2 Kolom di Desktop) ─────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6 col-md-6 col-12">
            <div class="backup-stat-card backup-stat-surat">
                <div class="backup-stat-icon">
                    <i class="bi bi-envelope-paper"></i>
                </div>
                <div class="backup-stat-body">
                    <p class="backup-stat-label">Total Arsip Surat</p>
                    <div class="backup-stat-value">{{ number_format($countSuratMasuk + $countSuratKeluar) }} Data Surat</div>
                    <div class="d-flex gap-2 mt-1 flex-wrap">
                        <span class="backup-badge backup-badge-masuk">
                            <i class="bi bi-box-arrow-in-down me-1"></i>Masuk: {{ $countSuratMasuk }}
                        </span>
                        <span class="backup-badge backup-badge-keluar">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Keluar: {{ $countSuratKeluar }}
                        </span>
                    </div>
                    <small class="backup-stat-size d-block mt-1">Lampiran berkas: {{ $statSurat['count'] }} file ({{ $statSurat['size'] }})</small>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-12">
            <div class="backup-stat-card backup-stat-peserta">
                <div class="backup-stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="backup-stat-body">
                    <p class="backup-stat-label">Total Peserta Didik</p>
                    <div class="backup-stat-value">{{ number_format($countPeserta) }} Siswa</div>
                    <div class="mt-1">
                        <span class="backup-badge backup-badge-peserta">
                            <i class="bi bi-person-check me-1"></i>Peserta Didik
                        </span>
                    </div>
                    <small class="backup-stat-size d-block mt-1">Dokumen terlampir: {{ $statPeserta['count'] }} file ({{ $statPeserta['size'] }})</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── FORM BACKUP ────────────────────────────────────────────────── --}}
    <form id="formBackup" method="POST" action="{{ route('backup.generate') }}">
        @csrf

        <div class="row g-4">

            {{-- ── Kolom Kiri: Pilihan Tipe & Surat ── --}}
            <div class="col-lg-6">

                {{-- Tipe Backup --}}
                <div class="backup-card mb-4">
                    <div class="backup-card-header">
                        <i class="bi bi-sliders2"></i>
                        <span>Tipe Backup</span>
                    </div>
                    <div class="backup-card-body">
                        <div class="backup-tipe-grid">
                            <label class="backup-tipe-option" for="tipe_semua">
                                <input type="radio" name="tipe" id="tipe_semua" value="semua" checked>
                                <div class="backup-tipe-card">
                                    <i class="bi bi-archive"></i>
                                    <span>Semua Data</span>
                                    <small>Surat Masuk, Keluar &amp; Siswa</small>
                                </div>
                            </label>
                            <label class="backup-tipe-option" for="tipe_surat">
                                <input type="radio" name="tipe" id="tipe_surat" value="surat">
                                <div class="backup-tipe-card">
                                    <i class="bi bi-envelope-paper"></i>
                                    <span>Arsip Surat</span>
                                    <small>Surat Masuk &amp; Keluar</small>
                                </div>
                            </label>
                            <label class="backup-tipe-option" for="tipe_peserta">
                                <input type="radio" name="tipe" id="tipe_peserta" value="peserta_didik">
                                <div class="backup-tipe-card">
                                    <i class="bi bi-people"></i>
                                    <span>Peserta Didik</span>
                                    <small>Dokumen siswa</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Filter Surat --}}
                <div class="backup-card" id="section-surat">
                    <div class="backup-card-header">
                        <i class="bi bi-envelope-paper"></i>
                        <span>Filter Arsip Surat</span>
                        <span class="backup-optional-badge">opsional</span>
                    </div>
                    <div class="backup-card-body">

                        {{-- Jenis Surat --}}
                        <div class="mb-3">
                            <span class="form-label d-block fw-semibold">Jenis Surat</span>
                            <div class="d-flex gap-2 flex-wrap">
                                <label class="backup-check-pill">
                                    <input type="radio" name="jenis_surat" value="semua" checked>
                                    <span><i class="bi bi-collection me-1"></i>Semua</span>
                                </label>
                                <label class="backup-check-pill">
                                    <input type="radio" name="jenis_surat" value="Masuk">
                                    <span><i class="bi bi-box-arrow-in-down me-1"></i>Masuk</span>
                                </label>
                                <label class="backup-check-pill">
                                    <input type="radio" name="jenis_surat" value="Keluar">
                                    <span><i class="bi bi-box-arrow-up-right me-1"></i>Keluar</span>
                                </label>
                            </div>
                        </div>

                        {{-- Tahun Surat --}}
                        <div>
                            <span class="form-label d-block fw-semibold">Tahun Surat</span>
                            @if($tahunSurat->isEmpty())
                                <div class="text-muted small fst-italic">Belum ada data surat.</div>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($tahunSurat as $ts)
                                    <label class="backup-check-pill">
                                        <input type="checkbox" name="tahun_surat[]" value="{{ $ts }}">
                                        <span>{{ $ts }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle me-1"></i>Kosongkan untuk mengambil semua tahun.
                                </small>
                            @endif
                        </div>

                    </div>
                </div>

            </div>

            {{-- ── Kolom Kanan: Peserta Didik & Action ── --}}
            <div class="col-lg-6">

                <div class="backup-card" id="section-peserta">
                    <div class="backup-card-header">
                        <i class="bi bi-people"></i>
                        <span>Filter Peserta Didik</span>
                        <span class="backup-optional-badge">opsional</span>
                    </div>
                    <div class="backup-card-body">

                        {{-- Tahun Angkatan --}}
                        <div class="mb-3">
                            <span class="form-label d-block fw-semibold">Tahun Angkatan</span>
                            @if($tahunPeserta->isEmpty())
                                <div class="text-muted small fst-italic">Belum ada data angkatan.</div>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($tahunPeserta as $tp)
                                    <label class="backup-check-pill">
                                        <input type="checkbox" name="tahun_peserta[]" value="{{ $tp }}">
                                        <span>{{ $tp }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle me-1"></i>Kosongkan untuk mengambil semua angkatan.
                                </small>
                            @endif
                        </div>

                        {{-- Rombel --}}
                        <div>
                            <span class="form-label d-block fw-semibold">Rombel</span>
                            @if($rombelList->isEmpty())
                                <div class="text-muted small fst-italic">Belum ada data rombel.</div>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($rombelList as $r)
                                    <label class="backup-check-pill">
                                        <input type="checkbox" name="rombel[]" value="{{ $r }}">
                                        <span>Rombel {{ $r }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle me-1"></i>Kosongkan untuk mengambil semua rombel.
                                </small>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- Tombol Generate --}}
                <div class="backup-generate-box mt-4">
                    <button type="button" class="btn btn-backup-generate" id="btnGenerate" onclick="jalankanBackup()">
                        <i class="bi bi-cloud-arrow-down-fill me-2" id="btnIcon"></i>
                        <span id="btnGenerateText">Generate &amp; Download ZIP</span>
                        <span id="btnGenerateSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </button>
                    <p class="text-muted small mt-2 mb-0" id="infoText">
                        <i class="bi bi-clock me-1"></i>
                        Proses mungkin memerlukan beberapa detik tergantung jumlah file.
                    </p>
                    <div id="backupAlert" class="mt-3 d-none"></div>
                </div>

            </div>
        </div>

    </form>

</div>

@push('scripts')
<script>
function jalankanBackup() {
    const btn      = document.getElementById('btnGenerate');
    const icon     = document.getElementById('btnIcon');
    const txt      = document.getElementById('btnGenerateText');
    const spinner  = document.getElementById('btnGenerateSpinner');
    const infoText = document.getElementById('infoText');
    const alertBox = document.getElementById('backupAlert');
    const form     = document.getElementById('formBackup');

    // Reset alert
    alertBox.className = 'mt-3 d-none';
    alertBox.innerHTML = '';

    // Set loading state
    btn.disabled = true;
    icon.className = 'd-none';
    spinner.classList.remove('d-none');
    txt.textContent = 'Sedang membuat ZIP...';
    infoText.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Harap tunggu, jangan tutup halaman ini.';

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    })
    .then(async res => {
        const data = await res.json().catch(() => null);

        if (!res.ok || !data || !data.success) {
            throw new Error((data && data.message)
                ? data.message
                : 'Terjadi kesalahan pada server (status ' + res.status + ').');
        }

        // Sukses: arahkan browser ke endpoint download GET.
        // Browser yang handle stream ZIP — tidak kena timeout fetch().
        const downloadUrl = '{{ route("backup.download", ":token") }}'
            .replace(':token', encodeURIComponent(data.token));

        setTimeout(() => { window.location.href = downloadUrl; }, 300);

        setTimeout(() => {
            txt.textContent = 'Generate & Download ZIP';
            icon.className  = 'bi bi-cloud-arrow-down-fill me-2';
            spinner.classList.add('d-none');
            btn.disabled = false;
            infoText.innerHTML = '<i class="bi bi-check-circle me-1 text-success"></i>Berhasil diunduh!';
        }, 1800);
    })
    .catch(err => {
        alertBox.className = 'mt-3 alert alert-danger';
        alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>'
            + (err.message || 'Terjadi kesalahan. Coba lagi.');

        txt.textContent = 'Generate & Download ZIP';
        icon.className  = 'bi bi-cloud-arrow-down-fill me-2';
        spinner.classList.add('d-none');
        btn.disabled = false;
        infoText.innerHTML = '<i class="bi bi-clock me-1"></i>Proses mungkin memerlukan beberapa detik tergantung jumlah file.';
    });
}
</script>
@endpush
@endsection
