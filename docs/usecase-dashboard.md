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
            UC1("Lihat Kartu Surat")
            UC2("Lihat Kartu Peserta Didik")
            UC3("Lihat Kartu Kode Arsip")
            UC4("Lihat Kartu Rekap")
            UC5("Lihat Bar Chart Statistik Surat")
            UC6("Lihat Donut Chart Peserta Didik")
            UC7("Lihat Line Chart Tren Surat Bulanan")
        end

        subgraph KEPALA["Khusus Kepala Staf"]
            UC8("Lihat Kartu Users")
            UC9("Lihat Breakdown Role User")
            UC10("Lihat Tabel User Terbaru")
        end

        subgraph SISTEM["Proses Sistem"]
            UC11("Query Agregasi Surat")
            UC12("Query Agregasi Peserta Didik per Rombel")
            UC13("Query Tren Surat 12 Bulan")
            UC14("Query Data per Tahun untuk Filter Chart")
            UC15("Query User dan Role")
        end

        UC1 -.->|"«include»"| UC11
        UC2 -.->|"«include»"| UC12
        UC5 -.->|"«include»"| UC11
        UC5 -.->|"«include»"| UC14
        UC6 -.->|"«include»"| UC12
        UC6 -.->|"«include»"| UC14
        UC7 -.->|"«include»"| UC13
        UC8 -.->|"«include»"| UC15
        UC9 -.->|"«include»"| UC15
        UC10 -.->|"«include»"| UC15
    end

    A1 --> UC1
    A1 --> UC2
    A1 --> UC3
    A1 --> UC4
    A1 --> UC5
    A1 --> UC6
    A1 --> UC7

    A2 --> UC1
    A2 --> UC2
    A2 --> UC3
    A2 --> UC4
    A2 --> UC5
    A2 --> UC6
    A2 --> UC7
    A2 --> UC8
    A2 --> UC9
    A2 --> UC10

    SYS2 --> UC11
    SYS2 --> UC12
    SYS2 --> UC13
    SYS2 --> UC14
    SYS2 --> UC15
```

---

## Deskripsi Use Case

| Use Case | Aktor | Deskripsi |
|---|---|---|
| **Lihat Kartu Surat** | Staf, Kepala Staf | Kartu menampilkan total surat dengan badge masuk & keluar (clickable link) |
| **Lihat Kartu Peserta Didik** | Staf, Kepala Staf | Kartu menampilkan total peserta didik dengan badge per rombel dan badge L/P |
| **Lihat Kartu Kode Arsip** | Staf, Kepala Staf | Kartu menampilkan total kode arsip yang terdaftar |
| **Lihat Kartu Rekap** | Staf, Kepala Staf | Shortcut ke halaman laporan (bukan angka statistik) |
| **Lihat Bar Chart Statistik Surat** | Staf, Kepala Staf | Bar chart perbandingan surat masuk vs keluar dengan filter input tahun |
| **Lihat Donut Chart Peserta Didik** | Staf, Kepala Staf | Donut chart distribusi per rombel dengan filter tahun angkatan dan badge gender L/P |
| **Lihat Line Chart Tren Surat Bulanan** | Staf, Kepala Staf | Line chart tren 12 bulan tahun berjalan, dua garis (masuk & keluar) |
| **Lihat Kartu Users** | Kepala Staf | Kartu menampilkan total user dengan breakdown Kepala Staf vs Staf |
| **Lihat Breakdown Role User** | Kepala Staf | Informasi jumlah per role di dalam kartu Users |
| **Lihat Tabel User Terbaru** | Kepala Staf | Tabel user yang paling baru dibuat (nama, role, waktu) |

## Perbedaan Tampilan Berdasarkan Role

| Elemen | Staf | Kepala Staf |
|---|---|---|
| Kartu Surat | ✅ | ✅ |
| Kartu Peserta Didik | ✅ | ✅ |
| Kartu Kode Arsip | ✅ | ✅ |
| Kartu Rekap | ✅ | ✅ |
| Kartu Users | ❌ | ✅ |
| Bar Chart Statistik Surat | ✅ | ✅ |
| Donut Chart Peserta Didik | ✅ | ✅ |
| Line Chart Tren Surat Bulanan | ✅ | ✅ |
| Tabel user terbaru | ❌ | ✅ |
| Total kartu di baris atas | 4 kartu | 5 kartu |
