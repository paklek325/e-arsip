# Penjelasan Aplikasi E-Arsip SMA Babussalam
## Dokumentasi untuk Keperluan Skripsi

---

## 1. Latar Belakang

Pengelolaan arsip surat dan dokumen siswa secara manual menghadirkan berbagai permasalahan, di antaranya: sulitnya pencarian arsip lama, risiko kerusakan atau kehilangan berkas fisik, lambatnya proses rekap data, serta ketidakefisienan dalam pengarsipan dokumen pendukung penerimaan siswa baru. SMA Babussalam sebagai satuan pendidikan menengah atas memiliki kebutuhan yang cukup tinggi terhadap pengelolaan administrasi surat-menyurat dan arsip dokumen peserta didik.

Untuk menjawab permasalahan tersebut, dikembangkanlah **E-Arsip SMA Babussalam**, yaitu sebuah sistem informasi manajemen arsip berbasis web yang memungkinkan pengelolaan surat masuk, surat keluar, dan dokumen peserta didik secara digital, terpusat, dan terintegrasi. Sistem ini juga dilengkapi dengan fitur asisten kecerdasan buatan (AI) bernama **Arsy** untuk membantu pengguna dalam pencarian dan analisis data arsip.

---

## 2. Deskripsi Umum Sistem

**Nama Aplikasi :** E-Arsip SMA Babussalam
**Jenis Sistem   :** Sistem Informasi Manajemen Arsip Berbasis Web
**Pengguna       :** Kepala Staf dan Staf Tata Usaha SMA Babussalam
**Platform       :** Web (dapat diakses melalui peramban)

E-Arsip adalah aplikasi manajemen arsip digital yang mengintegrasikan tiga domain utama administrasi sekolah:

1. **Manajemen Surat** — pengarsipan surat masuk dan surat keluar beserta lampirannya.
2. **Manajemen Peserta Didik** — penyimpanan data dan dokumen penerimaan peserta didik baru.
3. **Pelaporan dan Rekap** — pembuatan laporan rekap surat secara otomatis yang dapat diekspor ke PDF dan Excel.

Selain ketiga domain utama tersebut, sistem dilengkapi dengan **dasbor statistik**, **manajemen pengguna berbasis peran (role-based access control)**, dan **asisten AI** yang mampu menjawab pertanyaan seputar data arsip secara kontekstual.

---

## 3. Tujuan dan Manfaat Sistem

### 3.1 Tujuan

1. Mengubah proses pengarsipan manual menjadi sistem digital yang terstruktur dan mudah diakses.
2. Mempercepat proses pencarian arsip surat dan dokumen peserta didik.
3. Menyediakan laporan rekap surat secara otomatis tanpa perlu merekap ulang secara manual.
4. Meningkatkan keamanan dan integritas data arsip melalui sistem autentikasi dan otorisasi berbasis peran.
5. Memberikan gambaran statistik arsip secara real-time melalui dasbor yang informatif.

### 3.2 Manfaat

| Pihak | Manfaat |
|---|---|
| **Staf TU** | Proses input, pencarian, dan unduhan dokumen lebih cepat dan terorganisir |
| **Kepala Staf** | Pemantauan seluruh aktivitas arsip, pembuatan laporan, dan manajemen pengguna dari satu platform |
| **Institusi** | Arsip digital lebih aman, tidak mudah rusak/hilang, dan dapat diakses kapan saja |

---

## 4. Arsitektur Sistem

### 4.1 Arsitektur Perangkat Lunak

Aplikasi ini dibangun menggunakan pola arsitektur **MVC (Model-View-Controller)** yang merupakan standar pada framework Laravel. Berikut penjelasan masing-masing lapisan:

| Lapisan | Komponen | Fungsi |
|---|---|---|
| **Model** | `app/Models/` | Representasi data, relasi antar tabel, business logic |
| **View** | `resources/views/` | Template antarmuka pengguna (Blade templating) |
| **Controller** | `app/Http/Controllers/` | Menangani request HTTP, memanggil Model, merespons View |

### 4.2 Diagram Arsitektur Umum

```
┌─────────────────────────────────────────────────────────┐
│                     BROWSER / CLIENT                     │
│         (HTML + CSS + JavaScript + Bootstrap)            │
└──────────────────────────┬──────────────────────────────┘
                           │ HTTP Request
┌──────────────────────────▼──────────────────────────────┐
│                     WEB SERVER                           │
│              Apache / Nginx + PHP 8.1+                   │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│                 LARAVEL 10 FRAMEWORK                     │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────────┐  │
│  │  Router  │→ │  Controller  │→ │      Model        │  │
│  │(web.php) │  │(CRUD, Laporan│  │(Eloquent ORM)     │  │
│  └──────────┘  │ Chat, Auth)  │  └────────┬──────────┘  │
│                └──────┬───────┘           │              │
│                       │                   │              │
│                ┌──────▼───────┐    ┌──────▼──────────┐  │
│                │     View     │    │  MySQL Database  │  │
│                │(Blade Tmpl.) │    │  (7 Tabel)       │  │
│                └──────────────┘    └─────────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
              ┌────────────▼────────────┐
              │    Groq API (LLaMA)     │
              │  (Asisten AI - Arsy)    │
              └─────────────────────────┘
```

### 4.3 Teknologi yang Digunakan

#### Backend
| Teknologi | Versi | Fungsi |
|---|---|---|
| PHP | 8.1+ | Bahasa pemrograman server-side |
| Laravel | 10.10 | Framework MVC backend |
| MySQL | 8.0+ | Sistem manajemen basis data |
| Laravel Sanctum | 3.3 | Manajemen token API |
| DOMPDF (barryvdh) | 3.1 | Generate dokumen PDF |
| Maatwebsite Excel | 3.1 | Export laporan ke Excel |
| Spatie PDF-to-Image | 1.2 | Konversi PDF ke gambar |
| Guzzle HTTP | 7.2 | HTTP client untuk Groq API |
| PHPUnit | 10.1 | Framework pengujian |

#### Frontend
| Teknologi | Fungsi |
|---|---|
| Bootstrap 5 | Framework CSS responsif |
| Bootstrap Icons | Ikon antarmuka |
| Flatpickr | Date/time picker |
| Chart.js | Grafik dasbor |
| Vite 5.0 | Bundler aset frontend |
| JavaScript (ES6+) | Interaktivitas antarmuka |

#### Layanan Eksternal
| Layanan | Fungsi |
|---|---|
| Groq API (LLaMA 3.3 70B) | Model AI untuk asisten Arsy |

---

## 5. Basis Data

### 5.1 Tabel-Tabel Basis Data

Aplikasi menggunakan 6 tabel utama dan 3 tabel pendukung sistem Laravel:

| No. | Nama Tabel | Fungsi |
|---|---|---|
| 1 | `roles` | Menyimpan data peran pengguna |
| 2 | `users` | Menyimpan data akun pengguna sistem |
| 3 | `kode` | Menyimpan kode klasifikasi surat keluar |
| 4 | `surat` | Menyimpan data surat masuk dan surat keluar |
| 5 | `peserta_didik` | Menyimpan data dan dokumen peserta didik |
| 6 | `laporan` | Menyimpan snapshot rekap laporan surat |
| 7 | `personal_access_tokens` | Token API (Sanctum) |
| 8 | `password_reset_tokens` | Token reset kata sandi |
| 9 | `failed_jobs` | Antrian pekerjaan gagal |

### 5.2 Skema Tabel Utama

#### Tabel `roles`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_role` | bigint (PK) | Auto increment |
| `name` | varchar | Nama peran, unik |
| `description` | text | Deskripsi peran, nullable |
| `created_at` | datetime | — |
| `updated_at` | datetime | — |

#### Tabel `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_user` | bigint (PK) | Auto increment |
| `name` | varchar(150) | Nama lengkap pengguna |
| `email` | varchar | Email, unik |
| `password` | varchar | Hash bcrypt |
| `foto` | varchar | Path foto profil, nullable |
| `id_role` | bigint (FK) | Referensi ke `roles.id_role`, cascade delete |
| `created_at` | datetime | — |
| `updated_at` | datetime | — |

#### Tabel `kode`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_kode` | bigint (PK) | Auto increment |
| `kode` | varchar(10) | Kode surat, unik |
| `description` | varchar(100) | Deskripsi kode |

#### Tabel `surat`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | Auto increment |
| `no_surat` | varchar | Nomor surat |
| `kode_surat` | varchar | Kode surat (FK teks ke `kode.kode`, nullable) |
| `jenis_surat` | enum | `Masuk` atau `Keluar` |
| `tanggal_surat` | date | Tanggal surat |
| `perihal` | varchar | Perihal surat |
| `instansi` | varchar | Nama instansi pengirim/penerima |
| `pengirim` | varchar | Nama pengirim, nullable |
| `penerima` | varchar | Nama penerima, nullable |
| `keterangan` | text | Keterangan tambahan, nullable |
| `file_surat` | json | Array path file lampiran |
| `id_user` | bigint (FK) | Referensi ke `users.id_user` |
| `updated_by` | bigint (FK) | Referensi ke `users.id_user`, nullable |
| `created_at` | datetime | — |
| `updated_at` | datetime | — |

#### Tabel `peserta_didik`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_peserta_didik` | bigint (PK) | Auto increment |
| `nama_peserta_didik` | varchar | Nama lengkap siswa |
| `jenis_kelamin` | enum | `L` (laki-laki) atau `P` (perempuan) |
| `tempat_lahir` | varchar | Tempat lahir |
| `tanggal_lahir` | date | Tanggal lahir |
| `rombel` | enum | Rombongan belajar: `A` atau `B` |
| `tahun_angkatan` | varchar | Tahun angkatan masuk |
| `file_ppdb` | varchar | Formulir PPDB, nullable |
| `file_kk` | varchar | Kartu Keluarga, nullable |
| `file_akte` | varchar | Akte Kelahiran, nullable |
| `file_ktp` | varchar | KTP Orang Tua, nullable |
| `file_kts` | varchar | Kartu Tanda Siswa, nullable |
| `file_foto` | varchar | Foto siswa, nullable |
| `file_ijazah_smp` | varchar | Ijazah SMP, nullable |
| `file_ijazah_sma` | varchar | Ijazah SMA (opsional), nullable |
| `status` | enum | `lengkap` atau `belum lengkap` (otomatis) |
| `id_user` | bigint (FK) | Referensi ke `users.id_user` |
| `created_at` | datetime | — |
| `updated_at` | datetime | — |

#### Tabel `laporan`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_rekap` | bigint (PK) | Auto increment |
| `tahun` | int | Tahun rekap |
| `bulan` | int | Bulan rekap (1–12), nullable untuk rekap tahunan |
| `tipe_rekap` | enum | `Tahun`, `Bulan`, atau `Minggu` |
| `total_surat_masuk` | int | Total surat masuk |
| `total_surat_keluar` | int | Total surat keluar |
| `total_jenis` | int | Total masuk + keluar |
| `dibuat_oleh` | varchar | Nama pengguna pembuat laporan |
| `created_at` | datetime | — |

### 5.3 Relasi Antar Tabel

```
roles      ||──────< users          (One-to-Many, FK: users.id_role)
users      ||──────< surat          (One-to-Many, FK: surat.id_user)
users      ||──────< peserta_didik  (One-to-Many, FK: peserta_didik.id_user)
kode       ||──────< surat          (One-to-Many, FK: surat.kode_surat → kode.kode)
```

### 5.4 Aturan Bisnis Basis Data

| Aturan | Implementasi |
|---|---|
| Surat keluar **wajib** menggunakan kode dari tabel `kode` | Validasi di Controller sebelum menyimpan |
| Kode **tidak dapat dihapus** jika masih dipakai surat keluar | Model event `deleting` pada `Kode` |
| Saat kode **diperbarui**, semua `surat.kode_surat` terkait ikut diperbarui (cascade update) | Model event `updated` pada `Kode` |
| Status peserta didik (`lengkap`/`belum lengkap`) **dihitung otomatis** | Model event `saving` pada `PesertaDidik` |
| Satu user **hanya boleh satu peran** | FK `id_role` di tabel `users` |

---

## 6. Modul-Modul Aplikasi

### 6.1 Modul Autentikasi

Sistem autentikasi menggunakan mekanisme **session-based** bawaan Laravel. Tidak ada pendaftaran mandiri — akun pengguna hanya dapat dibuat oleh Kepala Staf melalui menu manajemen pengguna.

**Alur Login:**
1. Pengguna memasukkan email dan kata sandi pada halaman `/login`.
2. Sistem memverifikasi melalui `Hash::check()` terhadap hash bcrypt di basis data.
3. Jika valid, sesi dibuat dan pengguna diarahkan ke `/dashboard`.
4. Jika gagal 5 kali dalam 1 menit, akses dikunci sementara (rate limiting).

**Alur Logout:**
1. Pengguna menekan tombol Logout.
2. Sistem memanggil `Auth::logout()`, menghapus sesi.
3. Pengguna diarahkan kembali ke halaman login.

### 6.2 Modul Dasbor

Dasbor merupakan halaman utama setelah login yang menampilkan statistik dan grafik arsip secara real-time.

**Komponen Dasbor:**

| Kartu Statistik | Isi |
|---|---|
| Users | Total pengguna sistem (khusus Kepala Staf) |
| Surat | Total surat tahun berjalan + rincian masuk/keluar |
| Peserta Didik | Total siswa semua angkatan + breakdown per rombel + gender |
| Kode | Total kode surat yang terdaftar |
| Rekap | Tautan cepat ke halaman laporan |

**Grafik yang Ditampilkan:**
- **Bar Chart** — Jumlah surat masuk vs. keluar per tahun (seluruh waktu)
- **Donut Chart** — Distribusi peserta didik per rombel (seluruh angkatan)
- **Line Chart** — Tren surat masuk dan keluar per bulan (tahun berjalan)

Dasbor mendukung **mode gelap (dark mode)** yang tersimpan di `localStorage` sehingga preferensi pengguna dipertahankan antar sesi.

### 6.3 Modul Surat

Modul ini mengelola seluruh surat masuk dan surat keluar secara digital.

**Fitur Utama:**
- **CRUD Surat** — Tambah, lihat, ubah, hapus surat beserta lampiran berkas.
- **Filter Jenis** — Tampilkan khusus surat masuk atau khusus surat keluar.
- **Lampiran Multi-Berkas** — Setiap surat dapat memiliki lebih dari satu file lampiran.
- **Unduh Berkas** — Unduh lampiran satu per satu atau sekaligus dalam format ZIP.
- **Generate Nomor Surat** — Sistem menghasilkan nomor surat otomatis berurutan.
- **Cek Duplikat** — Nomor surat dicek secara real-time via AJAX sebelum disimpan.
- **Pencarian AI** — Cari surat menggunakan bahasa alami (contoh: "surat masuk bulan Maret 2025").

**Struktur Penyimpanan Berkas:**
```
storage/surat/
  └── {TAHUN}/
       └── {BULAN}/
            ├── Masuk/
            └── Keluar/
```

**Pelacak Perubahan:**
- Kolom `id_user` mencatat siapa yang membuat surat.
- Kolom `updated_by` mencatat siapa yang terakhir mengubah surat.
- Accessor `status_edit` mengembalikan label "Belum Pernah Diubah" atau "Pernah Diubah".

**Klasifikasi Surat Keluar:**
Surat keluar diwajibkan menggunakan kode dari tabel `kode`. Sistem akan menolak penyimpanan jika kode tidak terdaftar, mencegah penggunaan kode sembarangan.

### 6.4 Modul Peserta Didik

Modul ini mengelola data dan kelengkapan dokumen penerimaan peserta didik baru (PPDB).

**Data yang Dikelola:**

| Kelompok | Kolom |
|---|---|
| Identitas | Nama, jenis kelamin, tempat & tanggal lahir, rombel, tahun angkatan |
| Dokumen Wajib (7 berkas) | PPDB, Kartu Keluarga (KK), Akte Kelahiran, KTP Orang Tua, Kartu Tanda Siswa (KTS), Foto, Ijazah SMP |
| Dokumen Opsional | Ijazah SMA (untuk siswa pindahan/lanjutan) |

**Status Kelengkapan Otomatis:**
Sistem secara otomatis menghitung status peserta didik:
- `lengkap` → jika 7 dokumen wajib sudah terunggah.
- `belum lengkap` → jika ada dokumen wajib yang masih kosong.

Status ini diperbarui setiap kali data peserta didik disimpan melalui **Eloquent model event `saving`**, sehingga tidak memerlukan perhitungan manual.

**Fitur Unduh Dokumen:**
- Unduh satu dokumen tertentu.
- Unduh dokumen yang dipilih (multi-select) dalam satu file ZIP.
- Unduh semua dokumen milik satu siswa dalam satu file ZIP.

**Struktur Penyimpanan Berkas:**
```
storage/peserta_didik/
  └── {TAHUN_ANGKATAN}/
       └── {ROMBEL}/
            └── {nama-slug-siswa}/
                 └── [berkas-berkas dokumen]
```

### 6.5 Modul Kode Surat

Modul ini mengelola daftar kode klasifikasi yang digunakan untuk mengkategorikan surat keluar.

**Aturan Bisnis:**
- Kode bersifat unik, maksimal 10 karakter.
- Kode **tidak dapat dihapus** jika masih dipakai oleh surat keluar yang tersimpan.
- Jika kode **diperbarui**, seluruh surat keluar yang menggunakan kode lama secara otomatis akan memperbarui referensinya ke kode baru (cascade update melalui model event).

**Akses:** Hanya Kepala Staf yang dapat mengelola kode surat.

### 6.6 Modul Laporan

Modul laporan menyediakan rekap statistik surat yang dapat diunduh dalam berbagai format.

**Jenis Laporan:**

| Jenis | Cakupan | Isi |
|---|---|---|
| **Tahunan** | Satu tahun penuh | Tabel 12 baris (Jan–Des) berisi total surat masuk dan keluar per bulan |
| **Bulanan** | Satu bulan tertentu | Daftar seluruh surat pada bulan tersebut beserta totalnya |

**Format Ekspor:**
- **PDF** — Menggunakan library DOMPDF; siap cetak.
- **Excel** — Menggunakan Maatwebsite Excel; dapat diolah lebih lanjut.
- **Word** — Template dokumen Word dengan data yang sudah terisi.

**Alur Generate Laporan:**
1. Pengguna memilih tahun dan (opsional) bulan pada halaman filter.
2. Sistem menghitung agregat menggunakan query `GROUP BY` dengan `CASE WHEN`.
3. Laporan ditampilkan di layar dan tersedia tombol ekspor.

### 6.7 Modul Manajemen Pengguna

Modul ini hanya dapat diakses oleh Kepala Staf untuk mengelola akun pengguna sistem.

**Fitur:**
- Tambah pengguna baru dengan memilih peran (Kepala Staf / Staf).
- Ubah data pengguna (nama, email, foto profil, peran, kata sandi).
- Hapus pengguna (tidak dapat menghapus akun sendiri).
- Setiap pengguna dapat mengubah data profilnya sendiri melalui menu Profil.

**Enkripsi Kata Sandi:**
Kata sandi dienkripsi secara otomatis menggunakan **bcrypt** melalui mutator pada Model `User`. Kata sandi tidak pernah disimpan dalam bentuk teks biasa.

### 6.8 Modul Asisten AI (Arsy)

Arsy adalah asisten berbasis kecerdasan buatan yang terintegrasi langsung ke dalam aplikasi, menggunakan model **LLaMA 3.3 70B** yang diakses melalui **Groq API**.

**Kemampuan Arsy:**
- Menjawab pertanyaan tentang data arsip (jumlah surat, status siswa, dll.).
- Menjelaskan cara penggunaan fitur-fitur aplikasi.
- Menganalisis berkas yang diunggah pengguna (PDF, Word, Excel).
- Memberikan saran tindakan cepat berdasarkan halaman yang sedang dibuka.

**Jenis Berkas yang Dapat Dianalisis:**

| Format | Metode Ekstraksi |
|---|---|
| PDF | Ekstraksi teks via `pdftotext` |
| Word (.docx) | Ekstraksi XML dari arsip ZIP |
| Excel (.xlsx) | Ekstraksi dari `sharedStrings.xml` |
| Gambar | Dideteksi tetapi tidak dilakukan OCR |

**Konteks yang Dikirim ke AI:**
Setiap percakapan menyertakan konteks dari sistem:
- Halaman yang sedang dibuka.
- Statistik ringkas (total surat, total siswa, dll.).
- Peran dan nama pengguna.
- Riwayat percakapan (maksimal 6 pesan terakhir untuk efisiensi token).

**Pembatasan Akses:**
- Pesan chat: maksimal 30 permintaan per menit per pengguna.
- Unggah berkas: maksimal 10 unggahan per menit per pengguna.

---

## 7. Sistem Keamanan

### 7.1 Autentikasi dan Otorisasi

| Mekanisme | Implementasi |
|---|---|
| Session-based Auth | Laravel default `web` guard |
| Password Hashing | bcrypt via `Hash::make()` |
| CSRF Protection | Laravel CSRF token pada semua form POST |
| Rate Limiting | Login: 5x/menit; Chat: 30x/menit; Upload: 10x/menit |
| Role-Based Access | Middleware `RoleMiddleware` pada rute yang dibatasi |

### 7.2 Hak Akses Berdasarkan Peran

| Fitur | Staf | Kepala Staf |
|---|:---:|:---:|
| Login / Logout | ✅ | ✅ |
| Dashboard (statistik terbatas) | ✅ | ✅ |
| Dashboard (statistik penuh + tabel users) | ❌ | ✅ |
| Surat (CRUD + download) | ✅ | ✅ |
| Peserta Didik (CRUD + download) | ✅ | ✅ |
| Kode Surat (CRUD) | ❌ | ✅ |
| Laporan (generate + export) | ✅ | ✅ |
| Manajemen Pengguna (CRUD) | ❌ | ✅ |
| Manajemen Peran (CRUD) | ❌ | ✅ |
| Asisten AI Arsy | ✅ | ✅ |
| Edit Profil Sendiri | ✅ | ✅ |

### 7.3 Perlindungan Rute

Semua rute (kecuali `/login`) dilindungi oleh middleware `auth`. Middleware `RoleMiddleware` diterapkan secara tambahan pada rute-rute yang hanya boleh diakses Kepala Staf:

```
route('/user/*')  → middleware(['auth', 'role:Kepala Staf'])
route('/role/*')  → middleware(['auth', 'role:Kepala Staf'])
route('/kode/*')  → middleware(['auth', 'role:Kepala Staf'])
```

---

## 8. Alur Kerja Utama

### 8.1 Alur Input Surat Masuk

```
1. Pengguna membuka menu "Surat" → pilih "Surat Masuk"
2. Klik tombol "Tambah Surat"
3. Isi form: nomor surat, tanggal, instansi, perihal, keterangan, lampiran
4. Sistem cek duplikat nomor surat secara real-time (AJAX)
5. Simpan → sistem menyimpan berkas ke storage/surat/{tahun}/{bulan}/Masuk/
6. Data tersimpan di tabel surat dengan jenis_surat = 'Masuk'
7. Tabel surat diperbarui secara langsung tanpa reload halaman
```

### 8.2 Alur Input Surat Keluar

```
1. Pengguna membuka menu "Surat" → pilih "Surat Keluar"
2. Klik tombol "Tambah Surat"
3. Isi form termasuk kode surat (wajib dipilih dari daftar kode terdaftar)
4. Sistem validasi: kode harus ada di tabel kode
5. Sistem cek duplikat nomor surat
6. Simpan → berkas tersimpan di storage/surat/{tahun}/{bulan}/Keluar/
7. Data tersimpan di tabel surat dengan jenis_surat = 'Keluar'
```

### 8.3 Alur Input Data Peserta Didik

```
1. Pengguna membuka menu "Peserta Didik"
2. Klik tombol "Tambah Peserta Didik"
3. Isi form identitas + unggah dokumen
4. Model event saving → sistem menghitung status kelengkapan:
   - 7 dokumen wajib lengkap → status = 'lengkap'
   - Ada yang kurang         → status = 'belum lengkap'
5. Data dan berkas tersimpan di storage/peserta_didik/{angkatan}/{rombel}/{nama-slug}/
```

### 8.4 Alur Generate Laporan

```
1. Pengguna membuka menu "Laporan"
2. Pilih tahun (dan opsional bulan)
3. Klik "Generate"
4. Sistem query tabel surat dengan GROUP BY dan CASE WHEN
5. Laporan ditampilkan dalam tabel di layar
6. Pengguna dapat klik "Export PDF" atau "Export Excel"
7. Sistem menggunakan DOMPDF / Maatwebsite Excel untuk menghasilkan berkas
8. Berkas diunduh langsung ke perangkat pengguna
```

---

## 9. Antarmuka Pengguna

### 9.1 Prinsip Desain

Antarmuka E-Arsip dirancang dengan mempertimbangkan:
- **Kemudahan penggunaan** — navigasi intuitif dengan sidebar tetap.
- **Responsivitas** — tampilan menyesuaikan berbagai ukuran layar (desktop, tablet, ponsel).
- **Aksesibilitas** — mendukung mode terang (light mode) dan mode gelap (dark mode).
- **Konsistensi** — komponen visual yang seragam di seluruh halaman.

### 9.2 Struktur Navigasi

```
├── Dashboard
├── MASTER
│   ├── Peserta Didik
│   ├── Surat
│   ├── Surat Masuk (filter)
│   └── Surat Keluar (filter)
├── LAPORAN
│   └── Rekap
└── ADMIN (hanya Kepala Staf)
    ├── User
    └── Kode Surat
```

### 9.3 Komponen Utama Antarmuka

| Komponen | Deskripsi |
|---|---|
| **Sidebar** | Navigasi tetap di sisi kiri, dapat disembunyikan |
| **Header** | Tombol toggle sidebar, toggle dark mode, profil pengguna |
| **Dasbor Cards** | Kartu statistik dengan animasi counter dan progress bar |
| **Tabel Data** | Tabel responsif dengan filter, pencarian, dan paginasi |
| **Modal Form** | Form input/edit dalam jendela modal tanpa berpindah halaman |
| **Alert Toast** | Notifikasi sukses/gagal yang muncul sementara di pojok layar |
| **AI Chat (Arsy)** | Tombol bubble di pojok kanan bawah untuk membuka panel chat |
| **Dark Mode Toggle** | Tombol geser di header untuk beralih mode tampilan |

---

## 10. Struktur Direktori Proyek

```
e-arsip/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Semua controller
│   │   └── Middleware/
│   │       └── RoleMiddleware.php # Middleware otorisasi peran
│   └── Models/                   # Model Eloquent
│       ├── User.php
│       ├── Role.php
│       ├── Surat.php
│       ├── Kode.php
│       ├── PesertaDidik.php
│       ├── Laporan.php
│       └── LaporanRekap.php
├── database/
│   └── migrations/               # Skema basis data
├── docs/                         # Dokumentasi & diagram UML
├── public/
│   └── css/                      # CSS yang sudah dikompilasi
├── resources/
│   ├── css/                      # CSS sumber
│   ├── js/                       # JavaScript per halaman
│   └── views/                    # Template Blade
│       ├── layouts/
│       │   └── app.blade.php     # Layout utama
│       ├── dashboard.blade.php
│       ├── surat/
│       ├── peserta-didik/
│       ├── kode/
│       ├── laporan/
│       └── user/
├── routes/
│   └── web.php                   # Definisi rute
├── storage/
│   ├── surat/                    # Berkas lampiran surat
│   └── peserta_didik/            # Berkas dokumen siswa
├── composer.json                 # Dependensi PHP
├── package.json                  # Dependensi JavaScript
└── vite.config.js                # Konfigurasi build frontend
```

---

## 11. Pengujian Sistem

### 11.1 Framework Pengujian
Aplikasi menggunakan **PHPUnit 10.1** yang terintegrasi dengan Laravel. Pengujian dijalankan dengan perintah:
```
php artisan test
```

### 11.2 Jenis Pengujian
| Jenis | Cakupan |
|---|---|
| **Unit Test** | Model (business logic, mutator, event) |
| **Feature Test** | Controller (request-response, validasi, otorisasi) |
| **Browser Test** | (Opsional) Antarmuka pengguna dengan Laravel Dusk |

---

## 12. Penjelasan Singkat Per File Dokumen di Folder `docs/`

Folder `docs/` berisi diagram-diagram UML dan alur yang mendukung dokumentasi teknis aplikasi:

| File | Isi |
|---|---|
| `erd.md` | Entity Relationship Diagram — relasi antar tabel basis data |
| `class-diagram.md` | Class diagram — struktur Model Eloquent dan relasinya |
| `usecase-overview.md` | Use case keseluruhan — semua fitur dan aktor |
| `usecase-auth.md` | Use case modul autentikasi |
| `usecase-dashboard.md` | Use case modul dasbor |
| `usecase-surat.md` | Use case modul surat |
| `usecase-siswa.md` | Use case modul peserta didik |
| `usecase-kode-surat.md` | Use case modul kode surat |
| `usecase-laporan.md` | Use case modul laporan |
| `usecase-user.md` | Use case modul manajemen pengguna |
| `usecase-arsy.md` | Use case modul asisten AI Arsy |
| `activity-auth.md` | Activity diagram alur autentikasi |
| `activity-dashboard.md` | Activity diagram alur dasbor |
| `activity-surat.md` | Activity diagram alur surat |
| `activity-siswa.md` | Activity diagram alur peserta didik |
| `activity-laporan.md` | Activity diagram alur laporan |
| `activity-kode-surat.md` | Activity diagram alur kode surat |
| `activity-user.md` | Activity diagram alur manajemen pengguna |
| `activity-arsy.md` | Activity diagram alur asisten AI |
| `dfd-level1.md` | Data Flow Diagram level 1 |
| `diagram-konteks.md` | Diagram konteks (DFD level 0) |
| `flowchart-general.md` | Flowchart alur umum sistem |
| `arsy-chat-flow.md` | Detail alur percakapan Asisten AI Arsy |
| `penjelasan-aplikasi.md` | **File ini** — penjelasan lengkap aplikasi untuk skripsi |

---

## 13. Kesimpulan

E-Arsip SMA Babussalam merupakan sistem informasi manajemen arsip berbasis web yang dibangun menggunakan framework Laravel 10 dengan basis data MySQL. Sistem ini menjawab kebutuhan pengelolaan surat dan dokumen peserta didik secara digital di lingkungan sekolah.

Keunggulan utama sistem ini meliputi:
1. **Pengelolaan surat yang terstruktur** dengan klasifikasi kode dan pelacakan pembuat/editor.
2. **Manajemen dokumen siswa otomatis** dengan penghitungan status kelengkapan yang berjalan sendiri.
3. **Laporan rekap surat** yang dapat digenerate dan diekspor ke berbagai format tanpa proses manual.
4. **Kontrol akses berbasis peran** yang membedakan hak Kepala Staf dan Staf secara tegas.
5. **Asisten AI terintegrasi** yang membantu pengguna memahami data arsip melalui percakapan alami.
6. **Antarmuka adaptif** dengan dukungan dark mode dan tampilan responsif untuk semua perangkat.

Sistem ini dirancang dengan memperhatikan keamanan data, efisiensi query basis data, dan kemudahan penggunaan sehingga dapat diadopsi secara langsung oleh satuan pendidikan sebagai solusi digitalisasi arsip yang praktis dan andal.
