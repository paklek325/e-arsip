# Use Case — Autentikasi

Modul login/logout yang menjadi gerbang masuk ke seluruh fitur E-Arsip.

---

```mermaid
flowchart LR
    A1["👤 Staf"]
    A2["👤 Kepala Staf"]

    subgraph SYS["Sistem E-Arsip — Autentikasi"]
        direction TB
        UC1("Login dengan Email & Password")
        UC2("Logout")
        UC3("Validasi Kredensial")
        UC4("Redirect ke Dashboard")
        UC5("Tampilkan Pesan Error")

        UC1 -.->|"«include»"| UC3
        UC3 -.->|"«extend»"| UC4
        UC3 -.->|"«extend»"| UC5
    end

    A1 --> UC1
    A1 --> UC2
    A2 --> UC1
    A2 --> UC2
```

---

## Deskripsi Use Case

| Use Case | Aktor | Deskripsi |
|---|---|---|
| **Login dengan Email & Password** | Staf, Kepala Staf | Mengisi form login dan submit |
| **Validasi Kredensial** | Sistem | Cek email + password ke tabel `users` |
| **Redirect ke Dashboard** | Sistem | Jika valid → arahkan ke `/dashboard` |
| **Tampilkan Pesan Error** | Sistem | Jika gagal → tampilkan pesan kesalahan |
| **Logout** | Staf, Kepala Staf | Mengakhiri sesi dan kembali ke halaman login |

## Aturan Bisnis

- Email harus terdaftar di tabel `users`
- Password divalidasi menggunakan `bcrypt`
- Semua halaman (kecuali `/login`) dilindungi middleware `auth`
- Sesi dikelola oleh Laravel session driver
