# Use Case — Dashboard

Halaman beranda yang menampilkan statistik dan ringkasan data aplikasi.

---

```mermaid
flowchart LR
    A1["👤 Staf"]
    A2["👤 Kepala Staf"]
    SYS2["⚙️ Sistem"]

    subgraph SYS["Sistem E-Arsip — Dashboard"]
        direction TB

        subgraph UMUM["Akses Semua Role"]
            UC1("Lihat Total Surat Masuk")
            UC2("Lihat Total Surat Keluar")
            UC3("Lihat Total Siswa per Rombel")
            UC4("Lihat Grafik Tren Surat Bulanan")
        end

        subgraph KEPALA["Khusus Kepala Staf"]
            UC5("Lihat Total Semua User")
            UC6("Lihat Statistik per Role")
            UC7("Lihat Daftar 5 User Terbaru")
        end

        subgraph SISTEM["Proses Sistem"]
            UC8("Query Aggregasi Surat")
            UC9("Query Aggregasi Siswa per Rombel")
            UC10("Query Grafik 12 Bulan Terakhir")
            UC11("Query User Terbaru")
        end

        UC1 -.->|"«include»"| UC8
        UC2 -.->|"«include»"| UC8
        UC3 -.->|"«include»"| UC9
        UC4 -.->|"«include»"| UC10
        UC5 -.->|"«include»"| UC8
        UC6 -.->|"«include»"| UC8
        UC7 -.->|"«include»"| UC11
    end

    A1 --> UC1
    A1 --> UC2
    A1 --> UC3
    A1 --> UC4

    A2 --> UC1
    A2 --> UC2
    A2 --> UC3
    A2 --> UC4
    A2 --> UC5
    A2 --> UC6
    A2 --> UC7

    SYS2 --> UC8
    SYS2 --> UC9
    SYS2 --> UC10
    SYS2 --> UC11
```

---

## Deskripsi Use Case

| Use Case | Aktor | Deskripsi |
|---|---|---|
| **Lihat Total Surat Masuk** | Staf, Kepala Staf | Kartu statistik jumlah total surat masuk |
| **Lihat Total Surat Keluar** | Staf, Kepala Staf | Kartu statistik jumlah total surat keluar |
| **Lihat Total Siswa per Rombel** | Staf, Kepala Staf | Kartu statistik jumlah siswa rombel A dan B |
| **Lihat Grafik Tren Surat Bulanan** | Staf, Kepala Staf | Chart line surat masuk & keluar per bulan dalam tahun berjalan |
| **Lihat Total Semua User** | Kepala Staf | Kartu statistik total akun pengguna (kolom ke-5) |
| **Lihat Statistik per Role** | Kepala Staf | Breakdown jumlah Kepala Staf vs Staf |
| **Lihat 5 User Terbaru** | Kepala Staf | Tabel user yang paling baru dibuat (nama, role, waktu) |

## Perbedaan Tampilan Berdasarkan Role

| Elemen | Staf | Kepala Staf |
|---|---|---|
| Kartu statistik surat | ✅ 2 kartu | ✅ 2 kartu |
| Kartu statistik siswa | ✅ 2 kartu (Rombel A + B) | ✅ 2 kartu (Rombel A + B) |
| Kartu statistik user | ❌ | ✅ 1 kartu |
| Grafik tren surat | ✅ | ✅ |
| Tabel user terbaru | ❌ | ✅ |
| Total kartu di baris atas | 4 kartu | 5 kartu |
