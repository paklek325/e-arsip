<form id="filter_form"
      action="{{ route('laporan.generate') }}"
      data-export-url="{{ route('laporan.export') }}"
      method="GET"
      novalidate>

    <input type="hidden" name="tipe" id="tipe_rekap" value="Tahun">

    <div class="mb-3">
        <label class="form-label fw-semibold" for="tahun">Tahun</label>
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
        <label class="form-label fw-semibold" for="bulan">
            Bulan
            <span class="badge bg-secondary fw-normal ms-1 badge-opsional">Opsional</span>
        </label>
        <select name="bulan" id="bulan" class="form-select">
            <option value="">-- Semua Bulan --</option>
            @php
                $months = [
                    1=>'Januari',  2=>'Februari', 3=>'Maret',    4=>'April',
                    5=>'Mei',      6=>'Juni',     7=>'Juli',     8=>'Agustus',
                    9=>'September',10=>'Oktober', 11=>'November',12=>'Desember',
                ];
                $selectedBulan = old('bulan');
            @endphp
            @foreach($months as $num => $name)
                <option value="{{ $num }}" {{ (string)$selectedBulan === (string)$num ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Kosongkan untuk rekap seluruh tahun.</div>
    </div>

    <button type="button" id="btn_reset_filter" class="btn btn-outline-secondary w-100">
        <i class="bi bi-x-lg me-1"></i> Reset Filter
    </button>

</form>
