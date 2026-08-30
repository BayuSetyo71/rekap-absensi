# Dokumentasi Sistem Aplikasi Absensi & Dynamic RBAC

Selamat datang di dokumentasi resmi **Sistem Absensi**. Dokumentasi ini dibuat terstruktur per modul/fungsi untuk mempermudah penelusuran alur sistem (tracing AI dan pengembang) serta menghemat penggunaan token.

---

## 1. Spesifikasi Lingkungan (Environment Stack)
- **Framework**: Laravel 11.x
- **PHP Version**: 8.3.33 (ZTS Win64)
- **Web Server**: Apache 2.4.68 (Laragon)
- **Database**: MySQL 8.4.3 (`absensi`)
- **Frontend Stack**: Blade Templates, Bootstrap 5.3, Bootstrap Icons, SweetAlert2, dan **jQuery 3.7.1**
- **Timezone**: `Asia/Jakarta` (WIB)

---

## 2. Struktur Folder Dokumentasi (`docs/`)

```
docs/
├── README.md                           # Ringkasan sistem dan indeks dokumentasi
├── auth/
│   └── login.md                        # Alur autentikasi, session, dan quick demo login
├── portal_menu/
│   └── portal_menu_launcher.md         # Portal Menu Utama (App Launcher) berbasis Role & Live Search
├── karyawan/
│   └── portal_karyawan_dan_presensi_mandiri.md # Dashboard Karyawan, Presensi Mandiri Clock-In/Out & Slip Gaji
├── laporan/
│   └── laporan_presensi.md             # Modul Laporan Presensi, KPI, Chart, Export Excel & PDF
├── rbac/
│   ├── menu_permission_matrix.md       # Sistem Dynamic Menu dan Granular CRUD Permissions
│   └── roles_and_users.md              # Manajemen Role, User, dan Relasi Database
├── jam_kerja/
│   ├── jadwal_mengajar_perorangan.md   # Jadwal Mengajar Perorangan (Senin - Minggu) Berdasarkan Login
│   └── pengaturan_jam_kerja_pegawai.md # Jam Kerja Pegawai, Multi-Jenjang Yayasan (TK, SD, SMP, SMA)
├── absensi/
│   ├── import_export_excel.md          # Alur inject data absensi Excel/CSV & ekspor data
│   └── rekap_per_pegawai.md            # Rekapitulasi per pegawai, checkin/out, keterlambatan, detail & grafik
├── penggajian/
│   ├── alur_bisnis_penggajian_guru.md  # Bisnis Proses, Arsitektur & Formula Penggajian Guru Multi-Jenjang
│   ├── master_tarif_honor.md           # Master Tarif Honor Mengajar per Jenjang (TK-SMA) & Mapel
│   ├── proses_kalkulasi_payroll.md     # Mesin Perhitungan Otomatis Payroll berbasis Presensi & Sesi
│   └── slip_gaji_dan_laporan.md        # Slip Gaji Digital PDF & Ekspor Rekapitulasi Yayasan (Excel/PDF)
└── guide/
    ├── cara_menambah_menu_baru.md      # Panduan langkah demi langkah menambah menu baru
    ├── cara_push_ke_github.md          # Panduan langkah mengunggah (push) project ke GitHub
    └── setting_git_php_path_vscode.md  # Konfigurasi PATH Git & PHP Laragon di Terminal VS Code
```

---

## 3. Akun Demo untuk Uji Coba (Credentials)

Sistem telah dilengkapi seeder akun dengan kata sandi default: `password`

| Nama Pengguna | Email | Username | Role | Hak Akses |
|---|---|---|---|---|
| **Super Administrator** | `admin@absensi.com` | `superadmin` | `superadmin` | Akses Penuh (Bypass semua izin CRUD & Menu) |
| **Admin HRD** | `admin2@absensi.com` | `adminhrd` | `admin` | Akses CRUD Menu, Role & Users |
| **Ahmad Fauzi (Karyawan)** | `user@absensi.com` | `ahmadfauzi` | `user` | Akses Terbatas (Hanya Dashboard) |

---

## 4. Cara Menjalankan Aplikasi di Laragon

1. Buka aplikasi **Laragon**, pastikan service **Apache** dan **MySQL** telah aktif (status *Started*).
2. Buka browser dan akses salah satu alamat berikut:
   - `http://localhost/absensi/public`
   - atau `http://absensi.test` (jika fitur Pretty URL / Virtual Host Laragon aktif).
3. Halaman login akan tampil dengan tombol **1-Click Demo Login** untuk mempermudah pengujian hak akses per role.
