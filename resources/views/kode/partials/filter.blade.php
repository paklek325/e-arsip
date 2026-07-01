<form id="filter-form" class="mb-3" onsubmit="return false;">
  <div class="row g-2 align-items-center">

    {{-- 🔍 Input Pencarian --}}
    <div class="col-md-4">
      <input type="text" 
             name="search" 
             id="search" 
             class="form-control" 
             placeholder="Cari kode atau deskripsi..."
             value="{{ request('search') }}">
    </div>

    {{-- 🔄 Tombol Reset --}}
    <div class="col-md-2">
      <button type="button" class="btn btn-secondary w-100" id="btnReset">
        <i class="bi bi-arrow-counterclockwise"></i> Reset
      </button>
    </div>

  </div>
</form>





