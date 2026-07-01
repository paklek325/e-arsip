# Use Case — Modul User

Manajemen akun pengguna sistem. **Hanya dapat diakses oleh Kepala Staf.**

---

```mermaid
flowchart LR
    A2["👤 Kepala Staf"]
    SYS2["⚙️ Sistem"]

    subgraph SYS["Sistem E-Arsip — Modul User"]
        direction TB

        UC1("Lihat Daftar User")
        UC2("Lihat Detail User")
        UC3("Tambah User Baru")
        UC4("Edit Data User")
        UC5("Hapus User")
        UC6("Upload Foto Profil")

        UC7("Validasi Email Unik")
        UC8("Enkripsi Password bcrypt")
        UC9("Assign Role")
        UC10("Hapus Foto dari Storage")

        UC3 -.->|"«include»"| UC7
        UC3 -.->|"«include»"| UC8
        UC3 -.->|"«include»"| UC9
        UC3 -.->|"«extend»"| UC6
        UC4 -.->|"«include»"| UC7
        UC4 -.->|"«extend»"| UC6
        UC4 -.->|"«extend»"| UC8
        UC5 -.->|"«include»"| UC10
    end

    A2 --> UC1
    A2 --> UC2
    A2 --> UC3
    A2 --> UC4
    A2 --> UC5
    A2 --> UC6

    SYS2 --> UC7
    SYS2 --> UC8
    SYS2 --> UC9
    SYS2 --> UC10
```

---

## Deskripsi Use Case

| Use Case | Aktor | Deskripsi |
|---|---|---|
| **Lihat Daftar User** | Kepala Staf | Tabel semua pengguna sistem beserta role dan foto |
| **Lihat Detail User** | Kepala Staf | Modal detail berisi semua informasi akun |
| **Tambah User Baru** | Kepala Staf | Form: nama, email, password, role, foto (opsional) |
| **Edit Data User** | Kepala Staf | Ubah nama, email, role, foto; password opsional diubah |
| **Hapus User** | Kepala Staf | Hapus akun beserta foto dari storage |
| **Upload Foto Profil** | Kepala Staf | Upload gambar profil user (opsional) |
| **Validasi Email Unik** | Sistem | Cek keunikan email di tabel `users` |
| **Enkripsi Password** | Sistem | Hash password menggunakan `bcrypt` sebelum disimpan |
| **Assign Role** | Sistem | Set `id_role` dari pilihan: Kepala Staf / Staf |
| **Hapus Foto dari Storage** | Sistem | Hapus file foto dari `public/assets/foto_admin` saat user dihapus |

## Role yang Tersedia

| Role | `id_role` | Akses |
|---|---|---|
| **Kepala Staf** | `1` | Semua modul: Dashboard (full), Surat, Siswa, Kode Surat, Laporan, User |
| **Staf** | `2` | Dashboard (terbatas), Surat, Siswa, Laporan |

## Aturan Bisnis

- Modul User **tidak muncul** di sidebar untuk Staf (`id_role != 1`)
- Route `/user` tidak memiliki middleware tambahan di route file, tetapi akses dikontrol via tampilan (sidebar menyembunyikan menu untuk Staf)
- Password minimal **3 karakter**
- Email harus unik di seluruh tabel `users`
- Foto disimpan di `public/assets/foto_admin/`
