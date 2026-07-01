import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                // Semua entry JS yang dipanggil via @vite([...]) di layout
                // harus terdaftar di sini agar ikut masuk manifest saat `npm run build`.
                'resources/js/app.js',
                'resources/js/tampilan.js',
                'resources/js/surat.js',
                'resources/js/kode.js',
                'resources/js/user.js',
                'resources/js/dashboard.js',
                'resources/js/peserta-didik.js',
                'resources/js/laporan.js',
                'resources/js/responsif.js',
                'resources/js/chat.js',
            ],
            refresh: true,
        }),
    ],
});

