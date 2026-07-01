# Activity Diagram — Autentikasi

---

## Login

```mermaid
flowchart TD
    START([Mulai]) --> A1

    subgraph USER["👤 Pengguna"]
        A1[Buka halaman /login]
        A2[Isi email dan password]
        A3[Klik tombol Login]
        A8[Lihat pesan error]
        A9[Akses aplikasi]
        A10[Klik tombol Logout]
    end

    subgraph SYSTEM["⚙️ Sistem"]
        B1{Form kosong?}
        B2[Tampilkan validasi input]
        B3[Cari email di tabel users]
        B4{Email ditemukan?}
        B5[Tampilkan error: akun tidak ditemukan]
        B6{Password cocok?}
        B7[Tampilkan error: password salah]
        B8[Buat sesi login]
        B9[Baca role dari tabel roles]
        B10[Redirect ke /dashboard]
        B11[Hapus sesi]
        B12[Redirect ke /login]
    end

    A1 --> A2 --> A3 --> B1
    B1 -->|Ya| B2 --> A8 --> A2
    B1 -->|Tidak| B3 --> B4
    B4 -->|Tidak| B5 --> A8 --> A2
    B4 -->|Ya| B6
    B6 -->|Tidak| B7 --> A8 --> A2
    B6 -->|Ya| B8 --> B9 --> B10 --> A9

    A9 --> A10 --> B11 --> B12
    B12 --> STOP([Selesai])
```

---

## Ringkasan Alur

| Langkah | Pelaku | Keterangan |
|---|---|---|
| Buka /login | Pengguna | Redirect otomatis dari `/` |
| Isi form | Pengguna | Email + password |
| Validasi kosong | Sistem | Client-side + server-side |
| Cari akun | Sistem | Query tabel `users` by email |
| Verifikasi password | Sistem | `bcrypt` check via `Hash::check()` |
| Buat sesi | Sistem | Laravel session driver |
| Baca role | Sistem | Eager load `roles` untuk akses kontrol |
| Redirect dashboard | Sistem | `/dashboard` |
| Logout | Pengguna | `POST /logout` |
| Hapus sesi | Sistem | `Auth::logout()` + invalidate token |
