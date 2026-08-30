# Dokumentasi Modul Autentikasi (Login & Logout)

Modul ini mengatur alur masuk (login), validasi kredensial, proteksi session, dan fitur demo login.

---

## 1. File Terkait
- **Controller**: `app/Http/Controllers/AuthController.php`
- **View**: `resources/views/auth/login.blade.php`
- **Rute (Routes)**:
  - `GET /login` -> `login` (Form login)
  - `POST /login` -> Proses login
  - `GET /demo-login/{role}` -> `demo.login` (Quick demo login per role: `superadmin`, `admin`, `user`)
  - `POST /logout` -> `logout` (Proses logout dan invalidate session)

---

## 2. Alur Kerja (Flowchart Logika)

```mermaid
flowchart TD
    Start([User Membuka /login]) --> InputForm[Input Email/Username & Password]
    InputForm --> CheckFieldType{Input format Email atau Username?}
    CheckFieldType -->|Email| AuthEmail[Auth::attempt by email]
    CheckFieldType -->|Username| AuthUsername[Auth::attempt by username]
    AuthEmail --> ValidateCred{Kredensial Valid?}
    AuthUsername --> ValidateCred
    ValidateCred -->|Tidak| ShowError[Kembalikan Error Validasi ke Form]
    ValidateCred -->|Ya| CheckActive{Akun is_active == 1?}
    CheckActive -->|Tidak (0)| LogoutInactive[Logout & Tampilkan Pesan Akun Dinonaktifkan]
    CheckActive -->|Ya (1)| RegenerateSession[Regenerate Session ID]
    RegenerateSession --> RedirectDashboard[Redirect ke /dashboard]
```

---

## 3. Fitur Utama
1. **Multi-Identifier Login**: Pengguna dapat masuk menggunakan alamat **email** ATAU **username**.
2. **Status Akun (is_active)**: Akun yang diset `is_active = 0` tidak akan diizinkan masuk dan session langsung dibatalkan.
3. **1-Click Demo Login**: Tombol pintas untuk langsung login sebagai role tertentu tanpa perlu mengetik manual (mempermudah proses pengetesan izin menu).
4. **Interaktivitas jQuery**:
   - Fitur toggle show/hide password (ikon mata untuk intip kata sandi) pada form input.
   - Penanganan feedback form dan auto-dismiss alert flash message.
