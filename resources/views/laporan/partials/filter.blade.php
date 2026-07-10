<form id="filter_form"
      action="{{ route('laporan.generate') }}"
      data-export-url="{{ route('laporan.export') }}"
      data-print-url="{{ route('laporan.print') }}"
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
        <label class="form-label fw-semibold" for="bulan_trigger">
            Bulan
            <span class="badge bg-secondary fw-normal ms-1 badge-opsional">Opsional</span>
        </label>

        @php
            $months = [
                1=>'Januari',  2=>'Februari', 3=>'Maret',    4=>'April',
                5=>'Mei',      6=>'Juni',     7=>'Juli',     8=>'Agustus',
                9=>'September',10=>'Oktober', 11=>'November',12=>'Desember',
            ];
            $selectedBulan = old('bulan');
            $selectedLabel = $selectedBulan && isset($months[(int)$selectedBulan])
                ? $months[(int)$selectedBulan]
                : '-- Semua Bulan --';
        @endphp

        {{-- Wrapper custom dropdown. Select asli tetap ada (disembunyikan)
             supaya seluruh logic di laporan.js (form submit, FormData, event
             change) tidak perlu diubah sama sekali. --}}
        <div class="custom-select-wrap" id="bulan_select_wrap">

            <button type="button"
                    class="custom-select-trigger"
                    id="bulan_trigger"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                    aria-controls="bulan_panel">
                <span class="custom-select-value" id="bulan_trigger_label">{{ $selectedLabel }}</span>
                <i class="bi bi-chevron-down custom-select-caret"></i>
            </button>

            {{-- Select asli: sumber kebenaran nilai form, disembunyikan visual --}}
            <select name="bulan" id="bulan" class="custom-select-native" tabindex="-1" aria-hidden="true">
                <option value="">-- Semua Bulan --</option>
                @foreach($months as $num => $name)
                    <option value="{{ $num }}" {{ (string)$selectedBulan === (string)$num ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            {{-- Backdrop khusus mobile (bottom-sheet) --}}
            <div class="custom-select-backdrop" id="bulan_backdrop"></div>

            <div class="custom-select-panel" id="bulan_panel" role="listbox" aria-labelledby="bulan_trigger" tabindex="-1">
                <div class="custom-select-panel-header">
                    <span>Pilih Bulan</span>
                    <button type="button" class="custom-select-close" id="bulan_panel_close" aria-label="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <ul class="custom-select-list">
                    <li class="custom-select-option {{ !$selectedBulan ? 'is-selected' : '' }}"
                        data-value="" role="option" aria-selected="{{ !$selectedBulan ? 'true' : 'false' }}">
                        -- Semua Bulan --
                    </li>
                    @foreach($months as $num => $name)
                        <li class="custom-select-option {{ (string)$selectedBulan === (string)$num ? 'is-selected' : '' }}"
                            data-value="{{ $num }}" role="option"
                            aria-selected="{{ (string)$selectedBulan === (string)$num ? 'true' : 'false' }}">
                            {{ $name }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="form-text">Kosongkan untuk rekap seluruh tahun.</div>
    </div>

    <button type="button" id="btn_reset_filter" class="btn btn-outline-secondary w-100">
        <i class="bi bi-x me-1"></i> Reset Filter
    </button>

</form>