# Dokumentasi Modul Role & Manajemen Pengguna

Modul ini mencakup pengelolaan peran (Role), pengguna sistem (Users), dan relasi hak akses pengguna.

---

## 1. Modul Manajemen Role
- **Controller**: `app/Http/Controllers/RoleController.php`
- **View**: `resources/views/roles/index.blade.php`, `resources/views/roles/permissions.blade.php`
- **Model**: `app/Models/Role.php`
- **Rute**:
  - `GET /roles` -> `roles.index` (Melihat daftar role)
  - `POST /roles` -> `roles.store` (Membuat role baru)
  - `GET /roles/{role}/edit` -> `roles.edit` (Mengambil data JSON untuk modal edit via jQuery)
  - `POST /roles/{role}/update` -> `roles.update` (Memperbarui data role)
  - `DELETE /roles/{role}` -> `roles.destroy` (Menghapus role kustom)
  - `GET /roles/{role}/permissions` -> `roles.permissions` (Halaman matriks izin menu)
  - `POST /roles/{role}/permissions` -> `roles.permissions.update` (Menyimpan izin menu)

### Aturan Keamanan Role:
1. Role dengan `is_system = 1` (`superadmin`, `admin`) dilindungi dari penghapusan.
2. Role yang masih memiliki relasi pengguna (`users_count > 0`) tidak dapat dihapus sebelum penggunanya dialihkan ke role lain.

---

## 2. Modul Manajemen Pengguna (Users)
- **Controller**: `app/Http/Controllers/UserController.php`
- **View**: `resources/views/users/index.blade.php`
- **Model**: `app/Models/User.php`
- **Rute**:
  - `GET /users` -> `users.index` (Daftar pengguna dengan filter pencarian nama, email, NIP, role, status)
  - `POST /users` -> `users.store` (Tambah pengguna)
  - `GET /users/{user}/edit` -> `users.edit` (Mengambil data JSON untuk modal edit via jQuery)
  - `POST /users/{user}/update` -> `users.update` (Perbarui data pengguna)
  - `DELETE /users/{user}` -> `users.destroy` (Hapus pengguna)
  - `POST /users/{user}/toggle` -> `users.toggle` (Toggle status aktif/non-aktif via jQuery AJAX)
  - `GET /users/export` -> `users.export` (Ekspor seluruh data pengguna ke format CSV Excel UTF-8)

### Kolom Data Pengguna:
- `name` : Nama lengkap karyawan/pengguna
- `email` : Alamat email aktif (wajib unik)
- `username` : Username unik untuk login alternatif
- `password` : Kata sandi terenkripsi (Hash::make)
- `role_id` : ID Role rujukan
- `nip` : Nomor Induk Pegawai
- `phone` : Nomor telepon / WhatsApp
- `position` : Nama jabatan
- `department` : Nama divisi / departemen
- `avatar` : Path foto profil (fallback ke UI-Avatars otomatis)
- `is_active` : Boolean status keaktifan akun

### Fitur Interaktif Tambahan:
- **Tombol Intip Password (Eye Icon Toggle)**: Pada modal Tambah/Edit Pengguna dan Halaman Profil Pengguna, semua field password dilengkapi tombol ikon mata untuk melihat atau menyembunyikan teks kata sandi secara real-time via jQuery.
