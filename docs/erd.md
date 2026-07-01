# ERD — E-Arsip SMA Babussalam

Entity Relationship Diagram seluruh tabel database aplikasi.

---

```mermaid
erDiagram

    roles {
        bigint   id_role        PK "Auto increment"
        varchar  name           "Nama role"
        text     description    "Nullable"
    }

    users {
        bigint   id_user        PK "Auto increment"
        varchar  name           "Nama lengkap"
        varchar  email          "Unique"
        varchar  password       "Bcrypt hash"
        varchar  foto           "Path foto, nullable"
        bigint   id_role        FK "roles.id_role, cascade delete"
        datetime created_at
        datetime updated_at
    }

    kode {
        bigint   id_kode        PK "Auto increment"
        varchar  kode           "Unique, max 10 char"
        varchar  description    "Max 100 char"
    }

    surat {
        bigint   id             PK "Auto increment"
        varchar  no_surat       "Nomor surat"
        varchar  kode_surat     FK "kode.kode, nullable"
        enum     jenis_surat    "Masuk atau Keluar"
        date     tanggal_surat
        varchar  perihal
        varchar  instansi
        varchar  pengirim       "Nullable"
        varchar  penerima       "Nullable"
        text     keterangan     "Nullable"
        json     file_surat     "Array path file lampiran"
        varchar  created_by     "Nama pembuat, bukan FK"
        datetime created_at
        datetime updated_at
    }

    siswa {
        bigint   id_siswa       PK "Auto increment"
        varchar  nama_siswa
        enum     jenis_kelamin  "L atau P"
        varchar  tempat_lahir
        date     tanggal_lahir
        text     alamat
        enum     rombel         "A atau B"
        varchar  tahun_angkatan
        varchar  file_ppdb      "Nullable"
        varchar  file_kk        "Nullable"
        varchar  file_akte      "Nullable"
        varchar  file_ktp       "Nullable"
        varchar  file_kts       "Nullable"
        varchar  file_foto      "Nullable"
        varchar  file_ijazah_smp "Nullable"
        varchar  file_ijazah_sma "Nullable"
        enum     status         "lengkap atau belum lengkap"
        varchar  created_by     "Nama pembuat, bukan FK"
        datetime created_at
        datetime updated_at
    }

    laporan {
        bigint   id_rekap           PK "Auto increment"
        int      tahun              "Contoh: 2025"
        int      bulan              "1-12, nullable jika Tahunan"
        enum     tipe_rekap         "Tahun, Bulan, atau Minggu"
        int      total_surat_masuk  "Default 0"
        int      total_surat_keluar "Default 0"
        int      total_jenis        "Masuk ditambah Keluar"
        varchar  dibuat_oleh        "Nullable"
        datetime created_at
    }

    roles   ||--o{  users  : "memiliki"
    users   ||--o{  surat  : "membuat"
    users   ||--o{  siswa  : "menginput"
    kode    ||--o{  surat  : "mengklasifikasi"
```

---

## Keterangan Relasi

| Relasi | Tipe | Foreign Key | On Delete |
|---|---|---|---|
| `roles` → `users` | One-to-Many | `users.id_role` → `roles.id_role` | CASCADE |
| `kode` → `surat` | One-to-Many | `surat.kode_surat` → `kode.kode` | SET NULL via model event |
| `users` → `surat` | One-to-Many | `surat.created_by` ← nama string | — (bukan FK di DB) |
| `users` → `siswa` | One-to-Many | `siswa.created_by` ← nama string | — (bukan FK di DB) |

## Catatan Desain

| Aspek | Keterangan |
|---|---|
| **`surat.kode_surat`** | FK ke `kode.kode` (string), bukan ke `kode.id_kode`. Cascade update dilakukan manual via model event `updating` pada `Kode`, bukan constraint DB |
| **`created_by`** | Kolom `surat.created_by` dan `siswa.created_by` menyimpan nama user sebagai string, bukan `id_user` — tidak ada referential integrity |
| **`surat.file_surat`** | Kolom `json` yang di-cast ke `array` oleh Eloquent. Tidak ada tabel terpisah untuk lampiran |
| **`siswa.file_*`** | 8 kolom terpisah per jenis dokumen. Status `lengkap`/`belum lengkap` dihitung otomatis via model event `saving` |
| **`laporan`** | Tabel snapshot rekap. Model `Laporan` di Laravel tidak membaca tabel ini — ia query langsung ke tabel `surat` |
