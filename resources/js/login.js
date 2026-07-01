document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const switchBtn = document.getElementById('theme-switch');
    const THEME_KEY = 'earsip-theme';

    // Muat tema tersimpan (light/dark)
    const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
    if (savedTheme === 'dark') {
        html.setAttribute('data-theme', 'dark');
    }

    // Toggle tema ketika tombol di-klik
    if (switchBtn) {
        switchBtn.addEventListener('click', () => {
            const isDark = html.getAttribute('data-theme') === 'dark';
            if (isDark) {
                html.removeAttribute('data-theme'); // kembali ke light
                localStorage.setItem(THEME_KEY, 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem(THEME_KEY, 'dark');
            }
        });
    }

    // Auto-hide notifikasi
    document.querySelectorAll('.notif-global').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(-50%) translateY(-20px)';
            setTimeout(() => alert.remove(), 600);
        }, 4000);
    });

    // Toggle show/hide password
    const passwordInput = document.getElementById('password-input');
    const togglePassword = document.getElementById('togglePassword');

    if (passwordInput && togglePassword) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Ganti icon eye / eye-slash
            togglePassword.classList.toggle('bi-eye');
            togglePassword.classList.toggle('bi-eye-slash');
        });
    }
});
