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
        B2[Query total surat masuk dan keluar dari tabel surat]
        B3[Query total peserta didik per rombel dari tabel peserta_didik]
        B4[Query total peserta didik per jenis kelamin]
        B5[Query total kode arsip dari tabel kode]
        B6[Query data bar chart: agregasi surat masuk vs keluar per tahun]
        B7[Query data donut chart: peserta didik per rombel per tahun angkatan]
        B8[Query data line chart: tren surat bulanan 12 bulan tahun berjalan]
        B9{Role = Kepala Staf?}
        B10[Query total user per role dari tabel users]
        B11[Query user terbaru dari tabel users]
        B12[Render halaman dashboard]
    end

    A1 --> B1 --> B2 --> B3 --> B4 --> B5 --> B6 --> B7 --> B8 --> B9
    B9 -->|Ya| B10 --> B11 --> B12
    B9 -->|Tidak| B12
    B12 --> A2 --> A3
    A3 -->|Staf| A4 --> STOP1([Selesai])
    A3 -->|Kepala Staf| A5 --> STOP2([Selesai])
```

---

## Ringkasan Konten per Role

| Elemen | Staf | Kepala Staf |
|---|:---:|:---:|
| Kartu Surat (total + badge masuk/keluar) | ✅ | ✅ |
| Kartu Peserta Didik (total + badge rombel + badge L/P) | ✅ | ✅ |
| Kartu Kode Arsip | ✅ | ✅ |
| Kartu Rekap (shortcut laporan) | ✅ | ✅ |
| Kartu Users (total + breakdown role) | ❌ | ✅ |
| Bar Chart Statistik Surat (filter tahun) | ✅ | ✅ |
| Donut Chart Peserta Didik per Rombel (filter tahun) | ✅ | ✅ |
| Line Chart Tren Surat Bulanan | ✅ | ✅ |
| Tabel User Terbaru | ❌ | ✅ |

---

## Variabel Controller Dashboard

| Variabel | Deskripsi | Role |
|---|---|---|
| `$totalSurat` | Total semua surat | Semua |
| `$totalSuratMasuk` | Total surat masuk (tahun berjalan) | Semua |
| `$totalSuratKeluar` | Total surat keluar (tahun berjalan) | Semua |
| `$totalSuratMasukAll` | Total surat masuk keseluruhan | Semua |
| `$totalSuratKeluarAll` | Total surat keluar keseluruhan | Semua |
| `$totalPesertaDidikAll` | Total seluruh peserta didik | Semua |
| `$peserta_didikPerRombelAll` | Jumlah peserta didik per rombel | Semua |
| `$peserta_didikLakiAll` | Jumlah peserta didik laki-laki | Semua |
| `$peserta_didikPerempuanAll` | Jumlah peserta didik perempuan | Semua |
| `$peserta_didikPerTahun` | Data peserta didik per tahun angkatan (filter donut) | Semua |
| `$totalKode` | Total kode arsip | Semua |
| `$trenSuratMasuk` | Array tren bulanan surat masuk | Semua |
| `$trenSuratKeluar` | Array tren bulanan surat keluar | Semua |
| `$trenSuratPerTahun` | Data tren surat per tahun (filter bar chart) | Semua |
| `$totalUser` | Total semua user | Kepala Staf |
| `$totalKepala` | Jumlah user role Kepala Staf | Kepala Staf |
| `$totalStaf` | Jumlah user role Staf | Kepala Staf |
| `$recentUsers` | Daftar user terbaru (untuk tabel) | Kepala Staf |

---

## Chart Dashboard (3 Chart)

| Chart | Tipe | Deskripsi | Filter |
|---|---|---|---|
| **Statistik Surat** | Bar Chart | Perbandingan surat masuk vs keluar | Input tahun |
| **Statistik Peserta Didik** | Donut Chart | Distribusi per rombel, badge gender L/P | Input tahun angkatan |
| **Tren Surat Bulanan** | Line Chart | Tren 12 bulan tahun berjalan, dua garis (masuk & keluar) | — |

> **Dark mode:** Observer `MutationObserver` memantau atribut `data-theme` pada tag `<html>`. Saat tema berubah, warna axis, legend, dan tooltip semua chart diperbarui secara otomatis.
