# Use Case — Modul Kode Surat

Master data kode klasifikasi untuk surat keluar. Wajib diisi sebelum membuat surat keluar.

---

```mermaid
flowchart LR
    A2["👤 Kepala Staf"]
    A1["👤 Staf"]
    SYS2["⚙️ Sistem"]

    subgraph SYS["Sistem E-Arsip — Modul Kode Surat"]
        direction TB

        UC1("Lihat Daftar Kode Surat")
        UC2("Tambah Kode Surat")
        UC3("Edit Kode Surat")
        UC4("Hapus Kode Surat")
        UC5("Lihat Detail Kode")

        UC6("Cek Duplikat Kode")
        UC7("Auto-update Surat Terkait")
        UC8("Nullify Kode pada Surat Terkait")
        UC9("Sediakan List Kode untuk Dropdown Surat")

        UC2 -.->|"«include»"| UC6
        UC3 -.->|"«include»"| UC6
        UC3 -.->|"«include»"| UC7
        UC4 -.->|"«include»"| UC8
    end

    A2 --> UC1
    A2 --> UC2
    A2 --> UC3
    A2 --> UC4
    A2 --> UC5

    A1 --> UC1

    SYS2 --> UC6
    SYS2 --> UC7
    SYS2 --> UC8
    SYS2 --> UC9
```

---

## Deskripsi Use Case

| Use Case | Aktor | Deskripsi |
|---|---|---|
| **Lihat Daftar Kode Surat** | Staf, Kepala Staf | Tabel kode surat (read-only untuk Staf) |
| **Tambah Kode Surat** | Kepala Staf | Form: kode (maks 10 char, unik) + deskripsi |
| **Edit Kode Surat** | Kepala Staf | Ubah kode atau deskripsi |
| **Hapus Kode Surat** | Kepala Staf | Hapus kode dari master data |
| **Lihat Detail Kode** | Kepala Staf | Lihat detail satu kode beserta surat yang menggunakannya |
| **Cek Duplikat Kode** | Sistem | Validasi keunikan kode via AJAX real-time saat input |
| **Auto-update Surat Terkait** | Sistem | Jika kode diubah → semua surat keluar yang pakai kode lama ikut diperbarui |
| **Nullify Kode pada Surat** | Sistem | Jika kode dihapus → field kode pada surat terkait menjadi `null` |
| **Sediakan List untuk Dropdown** | Sistem | Endpoint `/surat/kode-surat-keluar` → JSON list kode aktif untuk form surat |

## Aturan Bisnis

- Hanya **Kepala Staf** (`id_role = 1`) yang bisa tambah/edit/hapus kode
- Kode bersifat unik, maks **10 karakter** (contoh: `S-U`, `S-P`, `S-T`)
- Perubahan kode bersifat **cascade** ke seluruh surat keluar yang menggunakannya
- Penghapusan kode **tidak** menghapus surat — hanya memutus relasi (set `null`)
- Kode ini hanya berlaku untuk **Surat Keluar**, Surat Masuk tidak memerlukan kode
