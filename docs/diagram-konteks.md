# Diagram Konteks (DFD Level 0) — E-Arsip SMA Babussalam

Menggambarkan sistem E-Arsip sebagai **satu proses tunggal** beserta seluruh entitas eksternal dan aliran data yang masuk maupun keluar.

---

```mermaid
flowchart LR
    A1["👤 Staf"]
    A2["👤 Kepala Staf"]
    GROQ["🤖 Groq API"]
    STORAGE["💾 File Storage"]

    SYS(["Sistem E-Arsip\nSMA Babussalam"])

    A1 -->|"kredensial login"| SYS
    A1 -->|"data surat & lampiran"| SYS
    A1 -->|"data siswa & dokumen"| SYS
    A1 -->|"parameter filter laporan"| SYS
    A1 -->|"pertanyaan chat"| SYS

    SYS -->|"sesi autentikasi"| A1
    SYS -->|"statistik & grafik"| A1
    SYS -->|"daftar surat, detail, file download"| A1
    SYS -->|"daftar siswa, dokumen download"| A1
    SYS -->|"rekap laporan & export"| A1
    SYS -->|"jawaban AI streaming"| A1

    A2 -->|"kredensial login"| SYS
    A2 -->|"data surat & lampiran"| SYS
    A2 -->|"data siswa & dokumen"| SYS
    A2 -->|"data kode surat"| SYS
    A2 -->|"data akun & role"| SYS
    A2 -->|"parameter filter laporan"| SYS
    A2 -->|"pertanyaan chat"| SYS

    SYS -->|"sesi autentikasi"| A2
    SYS -->|"statistik lengkap & user terbaru"| A2
    SYS -->|"daftar surat, detail, file download"| A2
    SYS -->|"daftar siswa, dokumen download"| A2
    SYS -->|"daftar & manajemen kode surat"| A2
    SYS -->|"daftar & manajemen akun user"| A2
    SYS -->|"rekap laporan & export"| A2
    SYS -->|"jawaban AI streaming"| A2

    SYS -->|"system prompt + riwayat + pertanyaan"| GROQ
    GROQ -->|"token jawaban SSE streaming"| SYS

    SYS -->|"tulis file lampiran, dokumen, foto"| STORAGE
    STORAGE -->|"baca file untuk download"| SYS
```

---

## Entitas Eksternal

| Entitas | Tipe | Deskripsi |
|---|---|---|
| **Staf** | Pengguna | Akses operasional: surat, siswa, laporan, chat |
| **Kepala Staf** | Pengguna (Admin) | Akses penuh termasuk kode surat dan manajemen user |
| **Groq API** | Sistem Eksternal | Model LLaMA 3.3 70B untuk menjawab pertanyaan via Arsy |
| **File Storage** | Sistem Eksternal | Disk server untuk menyimpan lampiran surat, dokumen siswa, foto user |

## Aliran Data

| Dari | Ke | Data |
|---|---|---|
| Staf / Kepala Staf | Sistem | Kredensial, input form, file upload, pertanyaan chat |
| Sistem | Staf / Kepala Staf | Sesi, statistik, tabel data, file download, jawaban AI |
| Sistem | Groq API | System prompt + riwayat percakapan + pesan user |
| Groq API | Sistem | Token jawaban via SSE real-time |
| Sistem | File Storage | Tulis file lampiran surat, dokumen siswa, foto profil |
| File Storage | Sistem | Baca file untuk di-download pengguna |
