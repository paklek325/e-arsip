# Use Case — Modul Siswa

Mengelola data pribadi siswa dan dokumen pendaftaran/pendidikan mereka.

---

```mermaid
flowchart LR
    A1["👤 Staf"]
    A2["👤 Kepala Staf"]
    SYS2["⚙️ Sistem"]

    subgraph SYS["Sistem E-Arsip — Modul Siswa"]
        direction TB

        subgraph BACA["Baca"]
            UC1("Lihat Daftar Siswa")
            UC2("Lihat Detail Siswa")
            UC3("Filter & Cari Siswa")
        end

        subgraph TULIS["Tulis"]
            UC4("Tambah Data Siswa")
            UC5("Edit Data Siswa")
            UC6("Hapus Data Siswa")
        end

        subgraph DOKUMEN["Dokumen"]
            UC7("Upload Dokumen per Field")
            UC8("Download Dokumen Satuan")
            UC9("Download Semua Dokumen ZIP")
            UC10("Download Dokumen Terpilih ZIP")
        end

        subgraph SISTEM["Otomasi Sistem"]
            UC11("Cek Duplikat Nama + Tanggal Lahir")
            UC12("Hitung Status Kelengkapan Dokumen")
            UC13("Hapus File Saat Siswa Dihapus")
        end

        UC1 -.->|"«include»"| UC3
        UC2 -.->|"«include»"| UC8
        UC2 -.->|"«include»"| UC9
        UC2 -.->|"«include»"| UC10
        UC4 -.->|"«include»"| UC11
        UC4 -.->|"«extend»"| UC7
        UC5 -.->|"«extend»"| UC7
        UC6 -.->|"«include»"| UC13
        UC2 -.->|"«include»"| UC12
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
```

---

## Deskripsi Use Case

| Use Case | Aktor | Deskripsi |
|---|---|---|
| **Lihat Daftar Siswa** | Staf, Kepala Staf | Tabel siswa dengan pagination dan badge status kelengkapan |
| **Lihat Detail Siswa** | Staf, Kepala Staf | Modal detail berisi data pribadi + daftar 8 jenis dokumen |
| **Filter & Cari Siswa** | Staf, Kepala Staf | Cari berdasarkan nama, NIS, rombel, angkatan |
| **Tambah Data Siswa** | Staf, Kepala Staf | Form: nama, TTL, jenis kelamin, angkatan, rombel, alamat |
| **Edit Data Siswa** | Staf, Kepala Staf | Ubah data pribadi dan upload/hapus dokumen |
| **Hapus Data Siswa** | Staf, Kepala Staf | Hapus siswa beserta semua file dari storage |
| **Upload Dokumen per Field** | Staf, Kepala Staf | Upload per jenis: PPDB, KK, Akte, KTP, KTS, Foto, Ijazah SMP, Ijazah SMA |
| **Download Dokumen Satuan** | Staf, Kepala Staf | Unduh satu file dokumen |
| **Download Semua Dokumen ZIP** | Staf, Kepala Staf | Zip seluruh dokumen satu siswa |
| **Download Dokumen Terpilih ZIP** | Staf, Kepala Staf | Zip dokumen yang dicentang |
| **Cek Duplikat** | Sistem | Deteksi siswa ganda berdasarkan nama + tanggal lahir via AJAX |
| **Hitung Status Kelengkapan** | Sistem | "Lengkap" jika semua 8 file ada, "Belum Lengkap" jika ada yang kosong |
| **Hapus File Saat Dihapus** | Sistem | Otomatis hapus semua file dari disk saat record siswa dihapus |

## 8 Jenis Dokumen Siswa

| # | Nama Field | Keterangan |
|---|---|---|
| 1 | `ppdb` | Formulir PPDB |
| 2 | `kk` | Kartu Keluarga |
| 3 | `akte` | Akta Kelahiran |
| 4 | `ktp` | KTP Orang Tua |
| 5 | `kts` | Kartu Tanda Siswa |
| 6 | `foto` | Pas Foto |
| 7 | `ijazah_smp` | Ijazah SMP/MTs |
| 8 | `ijazah_sma` | Ijazah SMA (saat lulus) |
