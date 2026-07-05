{{-- =======================================================
🎓 FILTER BAR — Peserta Didik (AJAX, tanpa AI Search)
======================================================= --}}

<div class="filter-bar shadow-sm border-0 rounded-3 p-3 mb-3">
    <form method="GET"
          action="{{ route('peserta-didik.index') }}"
          class="row g-2 align-items-center w-100"
          id="filterForm">

        {{-- 🔍 Pencarian --}}
        <div class="col-12 col-md-4">
            <div class="input-group search-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text"
                       name="search"
                       id="searchInput"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Cari nama, rombel, angkatan..."
                       autocomplete="off">
                <button type="button"
                        id="resetSearch"
                        class="btn btn-light border {{ request('search') ? '' : 'd-none' }}"
                        title="Hapus Pencarian">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>

        {{-- 🎓 Rombel --}}
        <div class="col-6 col-md-2 position-relative">
            <select name="rombel" id="rombel" class="form-select pe-5">
                <option value="">Semua Rombel</option>
                <option value="A" {{ request('rombel') == 'A' ? 'selected' : '' }}>A</option>
                <option value="B" {{ request('rombel') == 'B' ? 'selected' : '' }}>B</option>
            </select>
            <button type="button"
                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 reset-input-and-submit"
                    data-target="#rombel" title="Reset rombel">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>

        {{-- ✅ Status --}}
        <div class="col-6 col-md-2 position-relative">
            <select name="status" id="statusFilter" class="form-select pe-5">
                <option value="">Semua Status</option>
                <option value="lengkap"       {{ request('status') == 'lengkap'       ? 'selected' : '' }}>✅ Lengkap</option>
                <option value="belum lengkap" {{ request('status') == 'belum lengkap' ? 'selected' : '' }}>⚠️ Belum Lengkap</option>
            </select>
            <button type="button"
                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 reset-input-and-submit"
                    data-target="#statusFilter" title="Reset status">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>

        {{-- 📅 Urutan Angkatan --}}
        <div class="col-6 col-md-2 position-relative">
            <select name="sort_angkatan" id="sortAngkatan" class="form-select pe-5">
                <option value="">Urutan Angkatan</option>
                <option value="terbaru" {{ request('sort_angkatan') == 'terbaru' ? 'selected' : '' }}>📅 Terbaru</option>
                <option value="terlama" {{ request('sort_angkatan') == 'terlama' ? 'selected' : '' }}>📆 Terlama</option>
            </select>
            <button type="button"
                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 reset-sort-and-submit"
                    data-target="#sortAngkatan" title="Reset urutan angkatan">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>

        {{-- 🔤 Urutan Nama --}}
        <div class="col-6 col-md-2 position-relative">
            <select name="sort_nama" id="sortNama" class="form-select pe-5">
                <option value="">Urutan Nama</option>
                <option value="az" {{ request('sort_nama') == 'az' ? 'selected' : '' }}>🔤 A - Z</option>
                <option value="za" {{ request('sort_nama') == 'za' ? 'selected' : '' }}>🔡 Z - A</option>
            </select>
            <button type="button"
                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 reset-sort-and-submit"
                    data-target="#sortNama" title="Reset urutan nama">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>


          <!--
     {{-- Tombol Reset Semua (opsional) --}}
        <div class="col-12 col-md-auto mt-2 mt-md-0">
            <button type="button" id="resetAllBtn" class="btn btn-outline-danger w-100">
                <i class="bi bi-arrow-counterclockwise"></i> Reset Semua
            </button>
        </div> -->

    </form>
</div>