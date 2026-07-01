// vite.config.js
import { defineConfig } from "file:///C:/laragon/www/e-arsip/node_modules/vite/dist/node/index.js";
import laravel from "file:///C:/laragon/www/e-arsip/node_modules/laravel-vite-plugin/dist/index.js";
var vite_config_default = defineConfig({
  plugins: [
    laravel({
      input: [
        "resources/css/app.css",
        // Semua entry JS yang dipanggil via @vite([...]) di layout
        // harus terdaftar di sini agar ikut masuk manifest saat `npm run build`.
        "resources/js/app.js",
        "resources/js/tampilan.js",
        "resources/js/surat.js",
        "resources/js/kode.js",
        "resources/js/user.js",
        "resources/js/dashboard.js",
        "resources/js/peserta-didik.js",
        "resources/js/laporan.js",
        "resources/js/responsif.js",
        "resources/js/chat.js"
      ],
      refresh: true
    })
  ]
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJDOlxcXFxsYXJhZ29uXFxcXHd3d1xcXFxlLWFyc2lwXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCJDOlxcXFxsYXJhZ29uXFxcXHd3d1xcXFxlLWFyc2lwXFxcXHZpdGUuY29uZmlnLmpzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy9DOi9sYXJhZ29uL3d3dy9lLWFyc2lwL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XG5pbXBvcnQgbGFyYXZlbCBmcm9tICdsYXJhdmVsLXZpdGUtcGx1Z2luJztcblxuZXhwb3J0IGRlZmF1bHQgZGVmaW5lQ29uZmlnKHtcbiAgICBwbHVnaW5zOiBbXG4gICAgICAgIGxhcmF2ZWwoe1xuICAgICAgICAgICAgaW5wdXQ6IFtcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9hcHAuY3NzJyxcbiAgICAgICAgICAgICAgICAvLyBTZW11YSBlbnRyeSBKUyB5YW5nIGRpcGFuZ2dpbCB2aWEgQHZpdGUoWy4uLl0pIGRpIGxheW91dFxuICAgICAgICAgICAgICAgIC8vIGhhcnVzIHRlcmRhZnRhciBkaSBzaW5pIGFnYXIgaWt1dCBtYXN1ayBtYW5pZmVzdCBzYWF0IGBucG0gcnVuIGJ1aWxkYC5cbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL2FwcC5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy90YW1waWxhbi5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9zdXJhdC5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9rb2RlLmpzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL3VzZXIuanMnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvanMvZGFzaGJvYXJkLmpzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL3Blc2VydGEtZGlkaWsuanMnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvanMvbGFwb3Jhbi5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9yZXNwb25zaWYuanMnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvanMvY2hhdC5qcycsXG4gICAgICAgICAgICBdLFxuICAgICAgICAgICAgcmVmcmVzaDogdHJ1ZSxcbiAgICAgICAgfSksXG4gICAgXSxcbn0pO1xuXHJcbiJdLAogICJtYXBwaW5ncyI6ICI7QUFBNFAsU0FBUyxvQkFBb0I7QUFDelIsT0FBTyxhQUFhO0FBRXBCLElBQU8sc0JBQVEsYUFBYTtBQUFBLEVBQ3hCLFNBQVM7QUFBQSxJQUNMLFFBQVE7QUFBQSxNQUNKLE9BQU87QUFBQSxRQUNIO0FBQUE7QUFBQTtBQUFBLFFBR0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxNQUNKO0FBQUEsTUFDQSxTQUFTO0FBQUEsSUFDYixDQUFDO0FBQUEsRUFDTDtBQUNKLENBQUM7IiwKICAibmFtZXMiOiBbXQp9Cg==
