# Use Case — Overview E-Arsip SMA Babussalam

Gambaran umum seluruh fitur aplikasi E-Arsip.

---

```mermaid
flowchart LR
    A1["👤 Staf"]
    A2["👤 Kepala Staf"]

    subgraph SYS["Sistem E-Arsip SMA Babussalam"]
        direction TB

        subgraph AUTH["Autentikasi"]
            F1("Login")
            F2("Logout")
        end

        subgraph DASH["Dashboard"]
            F3("Lihat Statistik & Grafik")
        end

        subgraph SURAT["Surat"]
            F4("Kelola Surat Masuk & Keluar")
            F5("Download Lampiran")
            F6("Filter & Pencarian Surat")
        end

        subgraph SISWA["Siswa"]
            F7("Kelola Data Siswa")
            F8("Kelola Dokumen Siswa")
            F9("Download Dokumen")
        end

        subgraph KODE["Kode Surat"]
            F10("Kelola Kode Surat Keluar")
        end

        subgraph LAPORAN["Laporan"]
            F11("Generate Rekap Surat")
            F12("Export PDF / Excel / Word")
        end

        subgraph USER["User"]
            F13("Kelola Akun Pengguna")
        end

        subgraph ARSY["Arsy AI Chat"]
            F14("Tanya Asisten AI")
        end
    end

    A1 --> F1
    A1 --> F2
    A1 --> F3
    A1 --> F4
    A1 --> F5
    A1 --> F6
    A1 --> F7
    A1 --> F8
    A1 --> F9
    A1 --> F11
    A1 --> F12
    A1 --> F14

    A2 --> F1
    A2 --> F2
    A2 --> F3
    A2 --> F4
    A2 --> F5
    A2 --> F6
    A2 --> F7
    A2 --> F8
    A2 --> F9
    A2 --> F10
    A2 --> F11
    A2 --> F12
    A2 --> F13
    A2 --> F14
```

---

## Ringkasan Akses per Role

| Fitur | Staf | Kepala Staf |
|---|:---:|:---:|
| Login / Logout | ✅ | ✅ |
| Dashboard | ✅ (terbatas) | ✅ (penuh) |
| Surat | ✅ | ✅ |
| Siswa | ✅ | ✅ |
| Kode Surat | ❌ | ✅ |
| Laporan | ✅ | ✅ |
| User | ❌ | ✅ |
| Arsy AI Chat | ✅ | ✅ |
