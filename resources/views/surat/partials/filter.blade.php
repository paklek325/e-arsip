<div class="filter-bar shadow-sm">
    <div class="row g-3 align-items-center w-100">

        {{-- SEARCH --}}
        <div class="col-xl-9 col-lg-9 col-md-12">
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