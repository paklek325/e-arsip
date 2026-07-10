<div class="filter-bar">
  <form id="filter-form" onsubmit="return false;">
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text"
             name="search"
             id="search"
             class="form-control"
             placeholder="Cari kode atau deskripsi..."
             value="{{ request('search') }}">
    </div>
  </form>
</div>