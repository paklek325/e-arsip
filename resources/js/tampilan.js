// ===========================================
// 🌓 E-ARSIP — THEME + SIDEBAR HANDLER (FINAL UNIFIED)
// ===========================================

// =======================================================
// ⚡ ANTI-FLICKER — Terapkan tema SEBELUM DOM siap
// -------------------------------------------------------
// Dijalankan langsung saat script ini dieksekusi (bukan menunggu
// DOMContentLoaded), supaya saat pindah menu/halaman, tema dark
// sudah aktif sebelum konten ke-render → tidak ada "flick" putih.
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
        sidebar?.classList.toggle("show");
        sidebarOverlay?.classList.toggle("active");
    }

    mobileToggle?.addEventListener("click", (e) => {
        e.preventDefault();
        toggleMobileSidebar();
    });
    sidebarOverlay?.addEventListener("click", () => {
        sidebar?.classList.remove("show");
        sidebarOverlay?.classList.remove("active");
    });

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

    // Expose supaya script lain (mis. surat.js setelah reload AJAX)
    // bisa langsung memasang data-label TANPA menunggu debounce
    // MutationObserver di bawah → menghindari "flick" tata letak
    // kartu mobile (label muncul telat sesaat setelah render).
    window.EArsipApplyTableLabels = processAllTables;

    const tableObserver = new MutationObserver(() => {
        clearTimeout(window.__earsipTableLabelTimer);
        window.__earsipTableLabelTimer = setTimeout(
            () => processAllTables(document),
            50
        );
    });
    tableObserver.observe(document.body, { childList: true, subtree: true });
});