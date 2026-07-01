# Use Case — Modul Laporan

Rekap statistik surat masuk dan keluar dengan kemampuan export ke berbagai format.

---

```mermaid
flowchart LR
    A1["👤 Staf"]
    A2["👤 Kepala Staf"]
    SYS2["⚙️ Sistem"]

    subgraph SYS["Sistem E-Arsip — Modul Laporan"]
        direction TB

        subgraph FILTER["Konfigurasi Laporan"]
            UC1("Pilih Tipe: Tahunan / Bulanan")
            UC2("Pilih Tahun")
            UC3("Pilih Bulan")
            UC4("Pilih Jenis: Masuk / Keluar / Semua")
        end

        subgraph GENERATE["Generate & Tampilkan"]
            UC5("Generate Laporan Tahunan")
            UC6("Generate Laporan Bulanan")
            UC7("Lihat Tabel Rekapitulasi")
        end

        subgraph EXPORT["Export"]
            UC8("Export PDF Landscape F4")
            UC9("Export Excel XLSX")
            UC10("Export Word DOC")
        end

        subgraph NAV["Navigasi"]
            UC11("Navigasi ke Daftar Surat dengan Filter")
        end

        UC5 -.->|"«include»"| UC1
        UC5 -.->|"«include»"| UC2
        UC6 -.->|"«include»"| UC1
        UC6 -.->|"«include»"| UC2
        UC6 -.->|"«include»"| UC3
        UC5 -.->|"«include»"| UC7
        UC6 -.->|"«include»"| UC7
        UC4 -.->|"«extend»"| UC5
        UC4 -.->|"«extend»"| UC6
        UC7 -.->|"«include»"| UC11
    end

    A1 --> UC1
    A1 --> UC2
    A1 --> UC3
    A1 --> UC4
    A1 --> UC5
    A1 --> UC6
    A1 --> UC7
    A1 --> UC8
    A1 --> UC9
    A1 --> UC10
    A1 --> UC11

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
    A2 --> UC11

    SYS2 -->|"query aggregasi"| UC5
    SYS2 -->|"query aggregasi"| UC6
```

---

## Deskripsi Use Case

| Use Case | Aktor | Deskripsi |
|---|---|---|
| **Pilih Tipe Tahunan/Bulanan** | Staf, Kepala Staf | Toggle antara rekap per bulan (tahunan) atau rekap per hari (bulanan) |
| **Pilih Tahun** | Staf, Kepala Staf | Input tahun (contoh: 2026) |
| **Pilih Bulan** | Staf, Kepala Staf | Hanya aktif jika tipe = Bulanan (1–12) |
| **Pilih Jenis** | Staf, Kepala Staf | Filter opsional: Masuk / Keluar / Semua |
| **Generate Laporan Tahunan** | Staf, Kepala Staf | Tabel 12 bulan × (masuk, keluar, total) untuk satu tahun |
| **Generate Laporan Bulanan** | Staf, Kepala Staf | Tabel per hari × (masuk, keluar, total) untuk satu bulan |
| **Lihat Tabel Rekapitulasi** | Staf, Kepala Staf | Tampilkan hasil rekap dengan baris total di bagian bawah |
| **Export PDF** | Staf, Kepala Staf | Generate PDF ukuran F4 landscape via DomPDF |
| **Export Excel** | Staf, Kepala Staf | Download file `.xlsx` via Maatwebsite Excel |
| **Export Word** | Staf, Kepala Staf | Download file `.doc` |
| **Navigasi ke Daftar Surat** | Staf, Kepala Staf | Klik angka pada tabel → buka `/surat?jenis_surat=X&bulan=Y&tahun=Z` |

## Aturan Bisnis

- Laporan Tahunan: wajib isi **Tahun** saja
- Laporan Bulanan: wajib isi **Tahun** + **Bulan**
- Navigasi ke Surat mempertahankan filter bulan & tahun di URL agar tabel surat langsung terfilter
- Export menggunakan filter yang sedang aktif (bukan seluruh data)
