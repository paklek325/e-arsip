/* =======================================================
   📱 AUTO-TABLE-RESPONSIVE.JS
   -------------------------------------------------------
   Tujuan:
   Membuat SEMUA tabel di dalam .table-responsive otomatis
   bisa berubah jadi "card" di mobile (≤576px) TANPA perlu
   menambahkan atribut data-label satu-satu di Blade.

   Cara kerja:
   1. Saat halaman dimuat, untuk setiap <table> di dalam
      .table-responsive, ambil teks tiap <th> di <thead>.
   2. Salin teks itu ke atribut data-label pada <td> yang
      sejajar kolomnya di setiap <tr> pada <tbody>.
   3. Pasang MutationObserver supaya kalau tabel diisi ulang
      lewat AJAX/DataTable (mis. fungsi loadTable() di halaman
      Surat), data-label otomatis dipasang ulang.

   Cara pakai:
   Cukup tambahkan satu baris ini di layout master Blade
   (misalnya di layouts/app.blade.php, sebelum </body>):

     <script src="{{ asset('js/auto-table-responsive.js') }}"></script>

   Tidak perlu mengubah file Blade lain sama sekali.
======================================================= */

(function () {
  'use strict';

  function applyLabels(table) {
    const thead = table.querySelector('thead');
    if (!thead) return;

    const headerCells = thead.querySelectorAll('tr:last-child th, tr:last-child td');
    if (!headerCells.length) return;

    const labels = Array.from(headerCells).map(function (th) {
      // Ambil teks bersih, buang ikon/elemen kosong
      return th.textContent.trim().replace(/\s+/g, ' ');
    });

    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(function (row) {
      const cells = row.querySelectorAll('td');

      // Lewati baris kosong/"Tidak ada data" (biasanya 1 td dengan colspan)
      if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
        return;
      }

      cells.forEach(function (cell, index) {
        if (labels[index]) {
          cell.setAttribute('data-label', labels[index]);
        }
      });
    });
  }

  function processAllTables(root) {
    const scope = root || document;
    const tables = scope.querySelectorAll('.table-responsive table');
    tables.forEach(applyLabels);
  }

  // Jalankan saat DOM pertama kali siap
  document.addEventListener('DOMContentLoaded', function () {
    processAllTables(document);
  });

  // Pantau perubahan DOM (mis. tabel di-reload via AJAX seperti
  // loadTable() di halaman Surat, atau DataTables redraw)
  const observer = new MutationObserver(function (mutations) {
    let needsReprocess = false;

    for (const m of mutations) {
      if (m.type === 'childList' && m.addedNodes.length) {
        needsReprocess = true;
        break;
      }
    }

    if (needsReprocess) {
      // Debounce kecil supaya tidak diproses berkali-kali saat
      // banyak perubahan terjadi sangat berdekatan
      clearTimeout(window.__earsipTableLabelTimer);
      window.__earsipTableLabelTimer = setTimeout(function () {
        processAllTables(document);
      }, 50);
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    const containers = document.querySelectorAll('.table-responsive');
    containers.forEach(function (container) {
      observer.observe(container, { childList: true, subtree: true });
    });
  });

})();