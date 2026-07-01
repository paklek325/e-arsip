# DFD Level 1 — E-Arsip SMA Babussalam

Dekomposisi proses tunggal dari diagram konteks menjadi 8 proses utama beserta data store internal dan aliran datanya.

---

```mermaid
flowchart LR
    %% ── Entitas Eksternal ──
    USR["👤 Pengguna\n(Belum Login)"]
    A1["👤 Staf"]
    A2["👤 Kepala Staf"]
    GROQ["🤖 Groq API"]
    STORAGE[("💾 File Storage")]

    %% ── Proses ──
    P1(["1.0\nAutentikasi"])
    P2(["2.0\nKelola Surat"])
    P3(["3.0\nKelola Siswa"])
    P4(["4.0\nKelola\nKode Surat"])
    P5(["5.0\nGenerate\nLaporan"])
    P6(["6.0\nKelola User"])
    P7(["7.0\nArsy AI Chat"])
    P8(["8.0\nDashboard"])

    %% ── Data Store ──
    D1[("D1 · users")]
    D2[("D2 · surat")]
    D3[("D3 · siswa")]
    D4[("D4 · kode_surat")]
    D5[("D5 · roles")]

    %% ════════════════════════════
    %% TAHAP 1 — Autentikasi
    %% (sebelum login, role belum diketahui)
    %% ════════════════════════════
    USR -->|"email & password"| P1
    P1 -->|"baca data akun"| D1
    P1 -->|"baca role"| D5
    P1 -->|"sesi Staf"| A1
    P1 -->|"sesi Kepala Staf"| A2

    %% ════════════════════════════
    %% TAHAP 2 — Setelah Login (role sudah diketahui)
    %% Staf → Proses
    %% ════════════════════════════
    A1 -->|"data surat & file"| P2
    A1 -->|"data siswa & dokumen"| P3
    A1 -->|"parameter filter"| P5
    A1 -->|"pertanyaan"| P7
    A1 -->|"permintaan akses"| P8

    %% ════════════════════════════
    %% Kepala Staf → Proses
    %% ════════════════════════════
    A2 -->|"data surat & file"| P2
    A2 -->|"data siswa & dokumen"| P3
    A2 -->|"data kode surat"| P4
    A2 -->|"parameter filter"| P5
    A2 -->|"data akun & role"| P6
    A2 -->|"pertanyaan"| P7
    A2 -->|"permintaan akses"| P8

    %% ════════════════════════════
    %% Proses → Staf
    %% ════════════════════════════
    P2 -->|"daftar & detail surat, file"| A1
    P3 -->|"daftar & detail siswa, dokumen"| A1
    P5 -->|"rekap & export"| A1
    P7 -->|"jawaban AI streaming"| A1
    P8 -->|"statistik & grafik"| A1

    %% ════════════════════════════
    %% Proses → Kepala Staf
    %% ════════════════════════════
    P2 -->|"daftar & detail surat, file"| A2
    P3 -->|"daftar & detail siswa, dokumen"| A2
    P4 -->|"daftar kode surat"| A2
    P5 -->|"rekap & export"| A2
    P6 -->|"daftar & detail user"| A2
    P7 -->|"jawaban AI streaming"| A2
    P8 -->|"statistik lengkap & user terbaru"| A2

    %% ════════════════════════════
    %% Proses ↔ Data Store
    %% ════════════════════════════
    P2 -->|"tulis / baca"| D2
    P2 -->|"baca kode surat"| D4
    P3 -->|"tulis / baca"| D3
    P4 -->|"tulis / baca"| D4
    P4 -->|"update kode cascade"| D2
    P5 -->|"baca agregasi"| D2
    P6 -->|"tulis / baca"| D1
    P6 -->|"baca"| D5
    P7 -->|"baca info user & role"| D1
    P8 -->|"baca agregasi"| D1
    P8 -->|"baca agregasi"| D2
    P8 -->|"baca agregasi"| D3

    %% ════════════════════════════
    %% Proses ↔ Sistem Eksternal
    %% ════════════════════════════
    P2 -->|"tulis / baca file lampiran"| STORAGE
    P3 -->|"tulis / baca dokumen siswa"| STORAGE
    P6 -->|"tulis / baca foto profil"| STORAGE
    P7 -->|"system prompt + riwayat + pesan"| GROQ
    GROQ -->|"token jawaban SSE"| P7
```

---

## Alur Autentikasi — Penjelasan

```
Pengguna (belum login)
        │
        │  email + password
        ▼
   1.0 Autentikasi  ──── baca ────► D1 (users)
        │            ──── baca ────► D5 (roles)
        │
        ├──── role = Staf       ────► 👤 Staf
        └──── role = Kepala Staf────► 👤 Kepala Staf
```

Role baru ditentukan oleh proses autentikasi setelah membaca tabel `users` dan `roles`. Setelah sesi terbentuk, barulah entitas **Staf** atau **Kepala Staf** mengakses proses-proses lainnya.

---

## Daftar Proses

| ID | Proses | Deskripsi |
|---|---|---|
| **1.0** | Autentikasi | Verifikasi kredensial, tentukan role, buat sesi |
| **2.0** | Kelola Surat | CRUD surat masuk/keluar, upload/download file lampiran |
| **3.0** | Kelola Siswa | CRUD data siswa, upload/download 8 jenis dokumen |
| **4.0** | Kelola Kode Surat | CRUD kode klasifikasi surat keluar, cascade update ke surat |
| **5.0** | Generate Laporan | Rekap surat tahunan/bulanan, export PDF/Excel/Word |
| **6.0** | Kelola User | CRUD akun pengguna, assign role, upload foto profil |
| **7.0** | Arsy AI Chat | Terima pertanyaan, kirim ke Groq API, stream jawaban ke user |
| **8.0** | Dashboard | Agregasi statistik surat, siswa, user, dan grafik tren bulanan |

## Daftar Data Store

| ID | Nama | Tabel | Deskripsi |
|---|---|---|---|
| **D1** | users | `users` | Data akun pengguna beserta relasi ke role |
| **D2** | surat | `surats` | Data surat masuk dan keluar beserta metadata |
| **D3** | siswa | `siswas` | Data pribadi siswa dan path file 8 dokumen |
| **D4** | kode_surat | `kode_surats` | Master kode klasifikasi surat keluar |
| **D5** | roles | `roles` | Daftar role: Kepala Staf, Staf |

## Catatan Aliran Data Khusus

| Aliran | Keterangan |
|---|---|
| **USR → P1** | Satu-satunya titik masuk ke sistem — semua pengguna wajib melewati autentikasi dulu |
| **P1 → D5** | Role dibaca dari tabel `roles` untuk menentukan hak akses sesi |
| **P4 → D2** (cascade) | Edit kode surat otomatis memperbarui semua record `surats` yang menggunakannya |
| **P7 → GROQ** | Membawa system prompt dinamis + max 6 pesan riwayat per request |
| **P8 → D1** | Hanya diakses jika sesi aktif adalah Kepala Staf (statistik user) |
