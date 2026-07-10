{{-- =======================================================
🎓 FILTER BAR — Peserta Didik (AJAX, tanpa AI Search)
======================================================= --}}

<div class="filter-bar shadow-sm border-0 rounded-3 p-3 mb-3">
    <form method="GET"
          action="{{ route('peserta-didik.index') }}"
          class="row g-2 align-items-center w-100"
          id="filterForm">

        {{-- 🔍 Pencarian (full width di semua ukuran) --}}
        <div class="col-12">
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

        {{-- 🎓 Rombel — col-6 di mobile, col-3 di desktop --}}
        <div class="col-6 col-md-3 position-relative">
            <select name="rombel" id="rombel" class="form-select filter-select">
                <option value="">Rombel</option>
                <option value="A" {{ request('rombel') == 'A' ? 'selected' : '' }}>A</option>
                <option value="B" {{ request('rombel') == 'B' ? 'selected' : '' }}>B</option>
            </select>
            <button type="button"
                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 reset-input-and-submit"
                    data-target="#rombel" title="Reset rombel">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>

        {{-- ✅ Status — col-6 di mobile, col-3 di desktop --}}
        <div class="col-6 col-md-3 position-relative">
            <select name="status" id="statusFilter" class="form-select filter-select">
                <option value="">Status</option>
                <option value="lengkap"       {{ request('status') == 'lengkap'       ? 'selected' : '' }}>✅ Lengkap</option>
                <option value="belum lengkap" {{ request('status') == 'belum lengkap' ? 'selected' : '' }}>⚠️ Belum Lengkap</option>
            </select>
            <button type="button"
                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 reset-input-and-submit"
                    data-target="#statusFilter" title="Reset status">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>

        {{-- 📅 Urutan Angkatan — col-6 di mobile, col-3 di desktop --}}
        <div class="col-6 col-md-3 position-relative">
            <select name="sort_angkatan" id="sortAngkatan" class="form-select filter-select">
                <option value="">Angkatan</option>
                <option value="terbaru" {{ request('sort_angkatan') == 'terbaru' ? 'selected' : '' }}>📅 Terbaru</option>
                <option value="terlama" {{ request('sort_angkatan') == 'terlama' ? 'selected' : '' }}>📆 Terlama</option>
            </select>
            <button type="button"
                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 reset-sort-and-submit"
                    data-target="#sortAngkatan" title="Reset urutan angkatan">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>

        {{-- 🔤 Urutan Nama — col-6 di mobile, col-3 di desktop --}}
        <div class="col-6 col-md-3 position-relative">
            <select name="sort_nama" id="sortNama" class="form-select filter-select">
                <option value="">Nama</option>
                <option value="az" {{ request('sort_nama') == 'az' ? 'selected' : '' }}>🔤 A - Z</option>
                <option value="za" {{ request('sort_nama') == 'za' ? 'selected' : '' }}>🔡 Z - A</option>
            </select>
            <button type="button"
                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 reset-sort-and-submit"
                    data-target="#sortNama" title="Reset urutan nama">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>

    </form>
</div>