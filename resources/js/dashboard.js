/**
 * dashboard.js
 * ─────────────────────────────────────────────────────────────
 * MENU   : Dashboard (/)
 * FILE   : resources/js/dashboard.js
 *
 * Berisi logika ringan untuk halaman dashboard:
 *  1. Animasi angka counter pada card statistik
 *  2. Navigasi saat card diklik (clickable-card)
 *
 * Chart (bar, donut, line) diinisialisasi langsung di @push('scripts')
 * dalam dashboard.blade.php karena membutuhkan data PHP (Blade variable).
 * ─────────────────────────────────────────────────────────────
 */

document.addEventListener("DOMContentLoaded", () => {

    // ── [Card Statistik] Animasi angka count-up ───────────────────────────
    // Target elemen : .dashboard-number (ada di setiap card stat)
    // Cara kerja    : baca data-count dari atribut HTML, animasikan dari 0
    //                 ke nilai target dalam ~800ms menggunakan setInterval 16ms
    document.querySelectorAll(".dashboard-number").forEach(el => {
        const target = +el.getAttribute("data-count") || 0;
        let count = 0;
        const step = target === 0 ? 0 : Math.ceil(target / (800 / 16));

        if (!step) { el.textContent = target.toLocaleString(); return; }

        const counter = setInterval(() => {
            count += step;
            if (count >= target) { count = target; clearInterval(counter); }
            el.textContent = count.toLocaleString();
        }, 16);
    });

    // ── [Card Statistik] Klik card → pindah halaman ───────────────────────
    // Target elemen : .clickable-card (semua card dengan atribut data-url)
    // Pengecualian  : badge .badge-surat-link di card Surat pakai
    //                 onclick="event.stopPropagation()" agar tidak ikut trigger ini
    document.querySelectorAll(".clickable-card").forEach(card => {
        card.addEventListener("click", () => {
            const url = card.dataset.url;
            if (url) window.location.href = url;
        });
    });

});
