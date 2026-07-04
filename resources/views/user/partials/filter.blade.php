<form id="filter-form" class="filter-bar mb-3" onsubmit="return false;">
  <div class="d-flex align-items-center gap-2 w-100">
    <input type="text"
           name="search"
           id="search"
           class="form-control flex-grow-1"
           placeholder="Cari nama atau email..."
           value="{{ request('search') }}">
    <button type="button" class="btn btn-outline-secondary flex-shrink-0" id="btnReset">
      <i class="bi bi-arrow-counterclockwise"></i> Reset
    </button>
  </div>
</form>




