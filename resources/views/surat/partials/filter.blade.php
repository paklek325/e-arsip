<div class="filter-bar shadow-sm">
    <div class="row g-3 align-items-center w-100">

        {{-- SEARCH --}}
        <div class="col-xl-6 col-lg-6 col-md-12">
            <div class="position-relative">

                <div class="input-group search-group">

                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>

                    <label for="searchInput" class="visually-hidden">Cari Berkas</label>
                    <input
                        type="text"
                        id="searchInput"
                        name="search"
                        class="form-control"
                        placeholder="Cari Berkas"
                        autocomplete="off">

                </div>

                <button
                    type="button"
                    id="resetSearch"
                    class="filter-clear-btn d-none"
                    title="Hapus Pencarian">
                    <i class="bi bi-x"></i>
                </button>

            </div>
        </div>

        {{-- JENIS SURAT --}}
        @php($lockJenis = $lockJenis ?? null)
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="position-relative">

                {{-- Saat halaman terkunci (Surat Masuk/Keluar), dropdown ini
                     dinonaktifkan dan nilainya dipaksa agar filter tidak bisa
                     diubah. surat.js tetap membaca .value walau disabled. --}}
                <label for="jenis" class="visually-hidden">Jenis Surat</label>
                <select id="jenis" name="jenis" class="form-select pe-5" @disabled($lockJenis)>
                    <option value="">Jenis Surat</option>
                    <option value="Masuk" @selected($lockJenis === 'Masuk')>Surat Masuk</option>
                    <option value="Keluar" @selected($lockJenis === 'Keluar')>Surat Keluar</option>
                </select>

                @unless($lockJenis)
                <button
                    type="button"
                    id="resetJenis"
                    class="filter-clear-btn"
                    title="Reset Filter">
                    <i class="bi bi-x"></i>
                </button>
                @endunless

            </div>
        </div>

        {{-- SORT --}}
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="position-relative">

                <label for="sort" class="visually-hidden">Urutkan</label>
                <select id="sort" name="sort" class="form-select pe-5">
                    <option value="tanggal_terbaru" @selected(($currentSort ?? 'tanggal_terbaru') === 'tanggal_terbaru')>Data Terbaru</option>
                    <option value="tanggal_terlama" @selected(($currentSort ?? '') === 'tanggal_terlama')>Data Terlama</option>
                </select>

                <button
                    type="button"
                    id="resetSort"
                    class="filter-clear-btn"
                    title="Reset Sort">
                    <i class="bi bi-x"></i>
                </button>

            </div>
        </div>

    </div>
</div>