// =======================================================
// ⚡ ANTI-FLICKER — Terapkan tema SEBELUM DOM siap
// -------------------------------------------------------
// CATATAN: Inline script di <head> layouts/app.blade.php sudah lebih awal
// menjalankan hal yang sama (lihat baris data-ui-changing & data-theme di
// app.blade.php). IIFE di sini berfungsi sebagai FALLBACK — jika tampilan.js
// pernah dimuat tanpa melalui layout (mis. dev/testing) tema tetap diterapkan.
// Tidak ada efek negatif dari double-set karena nilai yang sama.
// =======================================================
(function () {
    const THEME_KEY = "earsip-theme";
    const saved = localStorage.getItem(THEME_KEY) || "light";

    // Matikan semua transisi sesaat agar penerapan tema awal tidak "flick"
    document.documentElement.setAttribute("data-theme-changing", "");
    document.documentElement.setAttribute("data-theme", saved);
})();

document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;
    const sidebar = document.querySelector(".pc-sidebar");
    const header = document.querySelector(".pc-header");
    const container = document.querySelector(".pc-container");
    const themeSwitch =
        document.getElementById("theme-switch") ||
        document.getElementById("theme-toggle");
    const mobileToggle = document.getElementById("mobile-collapse");
    const sidebarHide = document.getElementById("sidebar-hide");

    // ====== LocalStorage Keys ======
    const THEME_KEY = "earsip-theme";
    const SIDEBAR_KEY = "earsip-sidebar";

    // Lepas flag anti-flicker setelah frame pertama selesai render,
    // supaya transisi normal (hover, toggle, dll) tetap smooth setelahnya.
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.documentElement.removeAttribute("data-theme-changing");
            document.documentElement.removeAttribute("data-ui-changing");
        });
    });

    // =======================================================
    // 🔧 Buat Overlay Sidebar (Kalau Belum Ada)
    // =======================================================
    let sidebarOverlay = document.querySelector(".sidebar-overlay");
    if (!sidebarOverlay) {
        sidebarOverlay = document.createElement("div");
        sidebarOverlay.className = "sidebar-overlay";
        document.body.appendChild(sidebarOverlay);
    }

    // =======================================================
    // 🌙 DARK MODE HANDLER
    // -------------------------------------------------------
    // Icon disamakan dengan halaman login: sun + moon + thumb
    // (toggle switch), bukan ganti innerHTML jadi 1 icon tunggal.
    // Pastikan markup tombol di Blade memakai struktur yang sama
    // seperti di login_blade.php, contoh:
    //
    //   <button id="theme-switch" type="button" aria-label="Toggle Theme">
    //       <i class="bi bi-sun-fill sun"></i>
    //       <i class="bi bi-moon-fill moon"></i>
    //       <span class="toggle-thumb"></span>
    //   </button>
    //
    // CSS (warna icon, posisi thumb) sudah ada di layout.css, jadi
    // di sini JS hanya perlu set atribut data-theme — TIDAK boleh
    // mengganti innerHTML tombol seperti versi lama.
    // =======================================================
    function applyTheme(theme, { instant = false } = {}) {
        if (instant) {
            // Matikan transisi global sesaat agar tidak ada flick
            // saat tema diterapkan ulang (mis. navigasi antar menu).
            document.documentElement.setAttribute("data-theme-changing", "");
        }

        document.documentElement.setAttribute("data-theme", theme);
        body.classList.toggle("dark-mode", theme === "dark");
        localStorage.setItem(THEME_KEY, theme);

        if (instant) {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    document.documentElement.removeAttribute(
                        "data-theme-changing"
                    );
                });
            });
        }
    }

    // Terapkan tema dari localStorage saat awal load.
    // Atribut data-theme sudah dipasang lebih awal (IIFE di atas)
    // untuk mencegah flicker; di sini cukup sinkronkan state JS-nya.
    const savedTheme = localStorage.getItem(THEME_KEY) || "light";
    applyTheme(savedTheme);

    // Tombol toggle tema (id: theme-switch / theme-toggle).
    // Menggunakan throttling waktu agar event ganda (touchend diikuti click)
    // tidak memicu perubahan tema dua kali yang membuat tombol terhambat/flick.
    if (themeSwitch) {
        let lastToggleTime = 0;
        const toggleThemeAction = (e) => {
            const now = Date.now();
            if (now - lastToggleTime < 350) {
                // Abaikan jika dipicu kurang dari 350ms (mencegah double trigger)
                e.preventDefault();
                e.stopPropagation();
                return;
            }
            lastToggleTime = now;

            e.preventDefault();
            e.stopPropagation();
            const current = localStorage.getItem(THEME_KEY) || "light";
            const nextTheme = current === "dark" ? "light" : "dark";
            applyTheme(nextTheme);
        };
        themeSwitch.addEventListener("touchend", toggleThemeAction, { passive: false });
        themeSwitch.addEventListener("click", toggleThemeAction);
    }

    // =======================================================
    // 🧭 SIDEBAR TOGGLE (DESKTOP)
    // =======================================================
    if (sidebar && sidebarHide) {
        sidebarHide.addEventListener("click", (e) => {
            e.preventDefault();
            const isCollapsed = body.classList.toggle("sidebar-collapsed");
            sidebar.classList.toggle("collapsed", isCollapsed);
            document.documentElement.classList.toggle(
                "pc-sidebar-collapsed",
                isCollapsed
            );

            sidebar.style.transform = isCollapsed
                ? "translateX(-100%)"
                : "translateX(0)";

            if (header) header.style.left = isCollapsed ? "0" : "260px";
            if (container)
                container.style.marginLeft = isCollapsed ? "0" : "260px";

            localStorage.setItem(
                SIDEBAR_KEY,
                isCollapsed ? "collapsed" : "open"
            );
        });
    }

    // =======================================================
    // 📱 SIDEBAR TOGGLE (MOBILE)
    // =======================================================
    function toggleMobileSidebar() {
        const isOpen = sidebar?.classList.contains('show');
        if (isOpen) {
            sidebar?.classList.remove('show');
            sidebarOverlay?.classList.remove('active');
        } else {
            sidebar?.classList.add('show');
            sidebarOverlay?.classList.add('active');
        }
    }

    mobileToggle?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleMobileSidebar();
    });
    // Touch support — pastikan sidebar bisa terbuka dengan tap cepat di iOS
    mobileToggle?.addEventListener('touchend', (e) => {
        e.preventDefault();
        toggleMobileSidebar();
    }, { passive: false });
    sidebarOverlay?.addEventListener('click', () => {
        sidebar?.classList.remove('show');
        sidebarOverlay?.classList.remove('active');
    });
    // Touch di overlay juga menutup sidebar
    sidebarOverlay?.addEventListener('touchend', (e) => {
        e.preventDefault();
        sidebar?.classList.remove('show');
        sidebarOverlay?.classList.remove('active');
    }, { passive: false });

    // =======================================================
    // 🔄 RESPONSIVE FIX (Resize)
    // =======================================================
    function handleResize() {
        if (!sidebar || !header || !container) return;

        if (window.innerWidth <= 992) {
            // Mode Mobile
            body.classList.remove("sidebar-collapsed");
            document.documentElement.classList.remove("pc-sidebar-collapsed");
            sidebar.classList.remove("collapsed", "show");
            sidebarOverlay?.classList.remove("active");
            sidebar.style.removeProperty("transform");
            header.style.left = "0";
            container.style.marginLeft = "0";
        } else {
            // Mode Desktop
            sidebar.classList.remove("show");
            sidebarOverlay?.classList.remove("active");

            const isCollapsed =
                localStorage.getItem(SIDEBAR_KEY) === "collapsed";
            body.classList.toggle("sidebar-collapsed", isCollapsed);
            document.documentElement.classList.toggle(
                "pc-sidebar-collapsed",
                isCollapsed
            );
            sidebar.style.transform = isCollapsed
                ? "translateX(-100%)"
                : "translateX(0)";
            header.style.left = isCollapsed ? "0" : "260px";
            container.style.marginLeft = isCollapsed ? "0" : "260px";
        }
    }

    window.addEventListener("resize", handleResize);

    // =======================================================
    // 🧩 RESTORE SIDEBAR STATE (Saat Halaman Dibuka)
    // =======================================================
    if (sidebar) {
        const savedSidebar = localStorage.getItem(SIDEBAR_KEY);

        if (window.innerWidth > 992) {
            const isCollapsed = savedSidebar === "collapsed";
            body.classList.toggle("sidebar-collapsed", isCollapsed);
            sidebar.classList.toggle("collapsed", isCollapsed);
            document.documentElement.classList.toggle(
                "pc-sidebar-collapsed",
                isCollapsed
            );

            sidebar.style.transform = isCollapsed
                ? "translateX(-100%)"
                : "translateX(0)";

            if (header) header.style.left = isCollapsed ? "0" : "260px";
            if (container)
                container.style.marginLeft = isCollapsed ? "0" : "260px";
        } else {
            // Mode mobile tidak pernah collapsed lewat class ini
            document.documentElement.classList.remove("pc-sidebar-collapsed");
            if (header) header.style.left = "0";
            if (container) container.style.marginLeft = "0";
        }
    }

    handleResize();

    // =======================================================
    // 🧩 HIGHLIGHT MENU AKTIF
    // =======================================================
    const currentURL = window.location.pathname;
    document.querySelectorAll(".pc-item").forEach((item) => {
        const link = item.querySelector(".pc-link");
        const href = link?.getAttribute("href");
        if (href && currentURL.startsWith(href)) {
            item.classList.add("active");
        } else {
            item.classList.remove("active");
        }
    });

    // =======================================================
    // 💬 BOOTSTRAP TOOLTIPS
    // =======================================================
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        new bootstrap.Tooltip(el);
    });

    // =======================================================
    // 📱 AUTO TABLE RESPONSIVE (data-label untuk card mobile)
    // =======================================================
    function applyTableLabels(table) {
        const thead = table.querySelector("thead");
        if (!thead) return;
        const headers = thead.querySelectorAll(
            "tr:last-child th, tr:last-child td"
        );
        if (!headers.length) return;
        const labels = Array.from(headers).map((th) =>
            th.textContent.trim().replace(/\s+/g, " ")
        );
        table.querySelectorAll("tbody tr").forEach((row) => {
            const cells = row.querySelectorAll("td");
            if (cells.length === 1 && cells[0].hasAttribute("colspan")) return;
            cells.forEach((cell, i) => {
                if (labels[i]) cell.setAttribute("data-label", labels[i]);
            });
        });
    }

    function processAllTables(root) {
        (root || document)
            .querySelectorAll(".table-responsive table")
            .forEach(applyTableLabels);
    }

    processAllTables(document);

    // Expose supaya script lain bisa memanggil langsung
    window.EArsipApplyTableLabels = processAllTables;

    // MutationObserver: proses LANGSUNG saat DOM berubah
    const tableObserver = new MutationObserver(() => {
        processAllTables(document);
    });
    tableObserver.observe(document.body, { childList: true, subtree: true });

    // =======================================================
    // 🗄️ BACKUP PAGE — Logic (hanya aktif jika ada #formBackup)
    // =======================================================
    const formBackup = document.getElementById('formBackup');
    if (formBackup) {
        const sectionSurat   = document.getElementById('section-surat');
        const sectionPeserta = document.getElementById('section-peserta');

        function updateBackupSections() {
            const tipe = document.querySelector('input[name="tipe"]:checked')?.value ?? 'semua';
            sectionSurat?.classList.toggle('backup-card-dimmed', !(tipe === 'semua' || tipe === 'surat'));
            sectionPeserta?.classList.toggle('backup-card-dimmed', !(tipe === 'semua' || tipe === 'peserta_didik'));
        }

        document.querySelectorAll('input[name="tipe"]').forEach(el =>
            el.addEventListener('change', updateBackupSections)
        );
        updateBackupSections();

        // Visual feedback pill — Radio & Checkbox
        // - Radio  : hapus .checked dari seluruh grup dulu, pasang di yang aktif
        // - Checkbox: toggle .checked sesuai state cb.checked
        function syncPillChecked(input) {
            if (input.type === 'radio') {
                // Hapus .checked semua pill dalam grup yang sama
                document.querySelectorAll(
                    `.backup-check-pill input[name="${input.name}"]`
                ).forEach(r => r.closest('.backup-check-pill')?.classList.remove('checked'));
            }
            input.closest('.backup-check-pill')?.classList.toggle('checked', input.checked);
        }

        // Inisialisasi state awal (radio yang pre-checked)
        document.querySelectorAll('.backup-check-pill input').forEach(inp => {
            if (inp.checked) syncPillChecked(inp);
            inp.addEventListener('change', () => syncPillChecked(inp));
        });

        // Submit Form — stream ZIP download
        const btnGen     = document.getElementById('btnGenerate');
        const btnText    = document.getElementById('btnGenerateText');
        const btnSpinner = document.getElementById('btnGenerateSpinner');

        formBackup.addEventListener('submit', async (e) => {
            e.preventDefault();
            btnGen.disabled = true;
            btnText.textContent = 'Memproses...';
            btnSpinner.classList.remove('d-none');

            try {
                const formData = new FormData(formBackup);
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                const res = await fetch(formBackup.action, {
                    method : 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    body   : formData,
                });

                if (!res.ok) {
                    const data = await res.json().catch(() => ({ message: 'Terjadi kesalahan server.' }));
                    window.AppToast?.(data.message ?? 'Gagal membuat backup.', 'error');
                    return;
                }

                const blob  = await res.blob();
                const url   = URL.createObjectURL(blob);
                const a     = document.createElement('a');
                const disp  = res.headers.get('Content-Disposition') ?? '';
                const matchFile = disp.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                a.download  = matchFile ? matchFile[1].replace(/['"]/g, '') : 'backup_earsip.zip';
                a.href = url;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => URL.revokeObjectURL(url), 10000);
                window.AppToast?.('Backup ZIP berhasil diunduh!', 'success');
            } catch (err) {
                console.error(err);
                window.AppToast?.('Gagal menghubungi server.', 'error');
            } finally {
                btnGen.disabled = false;
                btnText.innerHTML = 'Generate &amp; Download ZIP';
                btnSpinner.classList.add('d-none');
            }
        });
    }
});