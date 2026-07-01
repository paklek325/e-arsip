  <form id="filter_form"
        action="{{ route('laporan.generate') }}"
        data-export-url="{{ route('laporan.export') }}"
        method="GET"
        class="filter-bar"
        novalidate>

    <div class="mb-3">
      <label class="form-label">Tahun</label>
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
      <label class="form-label">Tipe Rekap</label>
      <select name="tipe" id="tipe_rekap" class="form-control" required>
        <option value="">-- Pilih Tipe --</option>
        <option value="Tahun" {{ old('tipe') == 'Tahun' ? 'selected' : '' }}>Tahunan</option>
        <option value="Bulan" {{ old('tipe') == 'Bulan' ? 'selected' : '' }}>Bulanan</option>
      </select>
    </div>


    <div class="mb-3" id="bulan_group" style="display:none;">
      <label class="form-label">Bulan (Opsional)<a href="#" data-bs-toggle="tooltip" class="ms-2" data-bs-title="Kosongkan untuk rekap setahun penuh."><i class="bi bi-info-circle"></i></a></label>
      <select name="bulan" id="bulan" class="form-control">
        <option value="">-- Semua Bulan --</option>
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
      {{-- <small class="text-muted">Kosongkan untuk rekap setahun penuh.</small> --}}
    </div>
      <button type="button" id="btn_reset_filter" class="btn btn-secondary" style="height: 41px;margin-top: 7px;">
        <i class="bi bi-x-lg"></i></i> Reset Filter
      </button>
  </form>





