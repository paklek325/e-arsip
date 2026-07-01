# Activity Diagram — Dashboard

---

## Load Dashboard

```mermaid
flowchart TD
    START([Mulai]) --> A1

    subgraph USER["👤 Pengguna"]
        A1[Akses halaman /dashboard]
        A2[Lihat statistik dan grafik]
        A3{Role pengguna?}
        A4[Lihat 4 kartu statistik]
        A5[Lihat 5 kartu statistik + tabel user terbaru]
    end

    subgraph SYSTEM["⚙️ Sistem"]
        B1[Verifikasi sesi dan role]
        B2[Query total surat masuk dari tabel surat]
        B3[Query total surat keluar dari tabel surat]
        B4[Query total siswa rombel A dari tabel siswa]
        B5[Query total siswa rombel B dari tabel siswa]
        B6[Query data grafik: agregasi surat per bulan tahun ini]
        B7{Role = Kepala Staf?}
        B8[Query total user per role dari tabel users]
        B9[Query 5 user terbaru dari tabel users]
        B10[Render halaman dashboard]
    end

    A1 --> B1 --> B2 --> B3 --> B4 --> B5 --> B6 --> B7
    B7 -->|Ya| B8 --> B9 --> B10
    B7 -->|Tidak| B10
    B10 --> A2 --> A3
    A3 -->|Staf| A4 --> STOP1([Selesai])
    A3 -->|Kepala Staf| A5 --> STOP2([Selesai])
```

---

## Ringkasan Konten per Role

| Elemen | Staf | Kepala Staf |
|---|:---:|:---:|
| Kartu Total Surat Masuk | ✅ | ✅ |
| Kartu Total Surat Keluar | ✅ | ✅ |
| Kartu Total Siswa Rombel A | ✅ | ✅ |
| Kartu Total Siswa Rombel B | ✅ | ✅ |
| Kartu Total User | ❌ | ✅ |
| Grafik Tren Surat Bulanan | ✅ | ✅ |
| Tabel 5 User Terbaru | ❌ | ✅ |
