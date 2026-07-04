  <form id="filter_form"
        action="{{ route('laporan.generate') }}"
        data-export-url="{{ route('laporan.export') }}"
        method="GET"
        class="filter-bar"
        novalidate>

    {{-- Tipe dihitung otomatis dari bulan (hidden) --}}
    <input type="hidden" name="tipe" id="tipe_rekap" value="Tahun">

    <div class="mb-3">
      <label class="form-label fw-semibold">Tahun</label>
      <input type="number"
             name="tahun"
             id="tahun"
             class="form-control"
             min="1900"
             max="{{ date('Y') + 1 }}"
             data-default-year="{{ date('Y') }}"
             value="{{ old('tahun', date('Y')) }}"
             required>
    </div>

    <div class="mb-3">
      <label class="form-label fw-semibold">
        Bulan
        <span class="badge bg-secondary fw-normal ms-1">Opsional</span>
      </label>
      <select name="bulan" id="bulan" class="form-control">
        <option value="">-- Semua Bulan (Rekap Tahunan) --</option>
        @php
          $months = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
          ];
          $selectedBulan = old('bulan');
        @endphp
        @foreach($months as $num => $name)
          <option value="{{ $num }}" {{ (string)$selectedBulan === (string)$num ? 'selected' : '' }}>
            {{ $name }}
          </option>
        @endforeach
      </select>
      <small class="text-muted">Kosongkan untuk melihat rekap seluruh tahun.</small>
    </div>

    <button type="button" id="btn_reset_filter" class="btn btn-secondary w-100 mt-1">
      <i class="bi bi-x-lg me-1"></i> Reset Filter
    </button>

  </form>
