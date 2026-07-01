# Use Case — Modul Surat

Mengelola surat masuk dan surat keluar sekolah beserta file lampirannya.

---

```mermaid
flowchart LR
    A1["👤 Staf"]
    A2["👤 Kepala Staf"]
    SYS2["⚙️ Sistem"]

    subgraph SYS["Sistem E-Arsip — Modul Surat"]
        direction TB

        subgraph BACA["Baca"]
            UC1("Lihat Daftar Surat")
            UC2("Lihat Detail Surat")
            UC3("Filter & Cari Surat")
            UC4("Filter dari Laporan")
        end

        subgraph TULIS["Tulis"]
            UC5("Tambah Surat Masuk")
            UC6("Tambah Surat Keluar")
            UC7("Edit Surat")
            UC8("Hapus Surat")
        end

        subgraph UNDUH["Unduh"]
            UC9("Download File Satuan")
            UC10("Download Semua File ZIP")
            UC11("Download File Terpilih ZIP")
        end

        subgraph SISTEM["Otomasi Sistem"]
            UC12("Validasi Duplikat Nomor Surat")
            UC13("Auto-fill Kode Surat Keluar")
            UC14("Hapus File Lampiran")
        end

        UC1 -.->|"«include»"| UC3
        UC2 -.->|"«include»"| UC9
        UC2 -.->|"«include»"| UC10
        UC2 -.->|"«include»"| UC11
        UC5 -.->|"«include»"| UC12
        UC6 -.->|"«include»"| UC12
        UC6 -.->|"«include»"| UC13
        UC8 -.->|"«include»"| UC14
        UC4 -.->|"«extend»"| UC1
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

    SYS2 --> UC12
    SYS2 --> UC13
    SYS2 --> UC14
```

---

## Deskripsi Use Case

| Use Case | Aktor | Deskripsi |
|---|---|---|
| **Lihat Daftar Surat** | Staf, Kepala Staf | Tabel surat dengan pagination, ditampilkan via AJAX |
| **Lihat Detail Surat** | Staf, Kepala Staf | Modal detail berisi semua field + daftar file lampiran |
| **Filter & Cari Surat** | Staf, Kepala Staf | Dropdown jenis (Masuk/Keluar), sorting, dan kolom search bebas |
| **Filter dari Laporan** | Staf, Kepala Staf | Navigasi dari halaman Laporan membawa params `bulan`, `tahun`, `jenis_surat` |
| **Tambah Surat Masuk** | Staf, Kepala Staf | Form: no surat, tanggal, perihal, instansi, pengirim, lampiran |
| **Tambah Surat Keluar** | Staf, Kepala Staf | Sama seperti Masuk + wajib pilih Kode Surat |
| **Edit Surat** | Staf, Kepala Staf | Ubah semua field, bisa tambah/hapus file lampiran |
| **Hapus Surat** | Staf, Kepala Staf | Hapus surat beserta semua file dari storage |
| **Download File Satuan** | Staf, Kepala Staf | Unduh satu file lampiran |
| **Download Semua File ZIP** | Staf, Kepala Staf | Zip seluruh lampiran satu surat |
| **Download File Terpilih ZIP** | Staf, Kepala Staf | Zip file-file yang dicentang |
| **Validasi Duplikat No Surat** | Sistem | Cek keunikan nomor surat per kombinasi instansi + tanggal |
| **Auto-fill Kode Surat Keluar** | Sistem | Dropdown kode surat di-fetch dari `/surat/kode-surat-keluar` |
| **Hapus File Lampiran** | Sistem | Otomatis hapus file dari disk saat surat dihapus |

## Aturan Bisnis

- Nomor surat harus unik (cek via AJAX real-time saat input)
- Surat Keluar **wajib** memiliki Kode Surat
- Minimal **1 file lampiran** saat tambah surat
- Kolom search mencakup: `no_surat`, `perihal`, `instansi`, `pengirim`, `penerima`, `keterangan`
- Filter dari Laporan menjaga params URL tetap intact selama berada di halaman Surat
