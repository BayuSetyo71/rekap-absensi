# Sistem Rekap Absensi, Jadwal Mengajar & Penggajian (Payroll)

Aplikasi manajemen presensi karyawan dan guru terintegrasi, dilengkapi dengan **Dynamic RBAC (Role-Based Access Control)**, pengelolaan jam kerja multi-jenjang yayasan (TK, SD, SMP, SMA), jadwal mengajar harian, serta mesin kalkulasi otomatis penggajian (payroll) guru dan cetak slip gaji digital.

---

## 🌟 Fitur Unggulan

### 1. 🔐 Dynamic RBAC & Role Management
- Manajemen peran (*roles*) dan pengguna (*users*) yang fleksibel.
- Dynamic Menu & Granular CRUD Permissions (Hak akses Create, Read, Update, Delete dapat diatur per menu per role).
- Fitur *1-Click Demo Login* untuk mempermudah simulasi berbagai tingkatan akun.

### 2. 🕒 Presensi & Rekapitulasi Kehadiran
- **Presensi Mandiri (Self Clock-In / Clock-Out)** langsung dari perangkat karyawan/guru.
- **Import Presensi Fingerprint**: Dukungan impor rekap kehadiran dari mesin absensi / file Excel (`.xlsx`, `.csv`).
- **Analisis & Rekap Kehadiran**: Rekap status hadir, terlambat, izin, sakit, alpa beserta kalkulasi durasi kerja efektif.

### 3. 🏫 Multi-Jenjang & Jadwal Mengajar
- Pengaturan jam kerja pegawai berdasarkan jenjang yayasan (**TK, SD, SMP, SMA**).
- **Jadwal Mengajar Guru**: Penjadwalan mata pelajaran, kelas, hari, dan jumlah jam/sesi mengajar perorangan.
- Tampilan jadwal mengajar personal (*My Schedule*) untuk guru yang sedang login.

### 4. 💰 Modul Penggajian & Honor Mengajar (Payroll)
- **Master Tarif Honor Mengajar**: Penyesuaian tarif per jam / sesi mengajar berdasarkan jenjang dan mata pelajaran.
- **Mesin Kalkulasi Otomatis**: Integrasi absensi kehadiran dan sesi mengajar aktual ke dalam perhitungan gaji kotor, potongan, dan gaji bersih.
- **Slip Gaji Digital (PDF)**: Generate dan unduh slip gaji perorangan berformat PDF resmi.
- **Laporan Rekapitulasi Payroll**: Ekspor rekapitulasi gaji yayasan ke format Excel dan PDF.

### 5. 🎨 Antarmuka Modern & Responsif
- Tampilan UI berbasis Bootstrap 5.3 dengan aksen Glassmorphism & kartu metrik modern.
- **Portal Menu Launcher**: Pencarian menu interaktif (*Live Search*) dan filter kategori menu secara *real-time*.
- Notifikasi interaktif berbasis **SweetAlert2**.

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 11.x
- **Bahasa Pemrograman**: PHP 8.2 / 8.3+
- **Database**: MySQL 8.x / MariaDB
- **Frontend**: Blade Templating, Bootstrap 5.3, Bootstrap Icons, SweetAlert2, jQuery 3.7
- **Library Tambahan**:
  - `barryvdh/laravel-dompdf` (Cetak Slip Gaji & Laporan PDF)
  - `maatwebsite/excel` (Import & Export Excel)

---

## 🚀 Panduan Instalasi Lokal

### 1. Prasyarat Sistem
- Web Server Lokal (disarankan **Laragon** atau XAMPP)
- PHP >= 8.2 (dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`)
- Composer
- Git

### 2. Kloning Repositori
```bash
git clone https://github.com/BayuSetyo71/rekap-absensi.git
cd rekap-absensi
```

### 3. Instal Dependensi Composer
```bash
composer install
```

### 4. Konfigurasi Lingkungan (`.env`)
Salin file template lingkungan dan sesuaikan konfigurasi database Anda:
```bash
cp .env.example .env
```
Buka file `.env` dan pastikan nama database sesuai (misal: `absensi`):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Migrasi Database & Seeder
Jalankan migrasi beserta data awal (roles, default menu, akun demo, tarif honor, dan sampel absensi):
```bash
php artisan migrate:fresh --seed
```

### 7. Jalankan Aplikasi
Jika menggunakan Laragon, aplikasi dapat diakses di:
- `http://localhost/absensi/public` atau `http://absensi.test`

Atau melalui built-in server Laravel:
```bash
php artisan serve
```
Akses di browser: `http://127.0.0.1:8000`

---

## 🔑 Akun Demo (Default Credentials)

Semua akun default menggunakan kata sandi: `password`

| Peran (Role) | Username | Email | Hak Akses Utama |
|---|---|---|---|
| **Super Administrator** | `superadmin` | `admin@absensi.com` | Akses penuh seluruh modul & bypass izin RBAC |
| **Admin HRD** | `adminhrd` | `admin2@absensi.com` | Manajemen Karyawan, Jam Kerja, Menu & Roles |
| **Guru / Karyawan** | `ahmadfauzi` | `user@absensi.com` | Presensi Mandiri, Jadwal Mengajar, Unduh Slip Gaji |

---

## 📚 Struktur Dokumentasi Modul (`docs/`)

Dokumentasi alur sistem tersusun rapi di dalam direktori `docs/` untuk mempermudah penelusuran (*tracing*):

```
docs/
├── README.md                           # Indeks dokumentasi lengkap
├── auth/                               # Alur autentikasi dan quick login
├── portal_menu/                        # Dokumentasi App Launcher & live search
├── karyawan/                           # Dashboard karyawan & presensi mandiri
├── jam_kerja/                          # Pengaturan jam kerja & jadwal mengajar
├── absensi/                            # Import Excel & rekapitulasi kehadiran
├── penggajian/                         # Alur tarif honor, kalkulasi payroll & slip gaji
├── rbac/                               # Manajemen role, user & menu permissions
├── laporan/                            # Export PDF/Excel laporan presensi & payroll
└── guide/                              # Panduan teknis & GitHub deployment
```

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi [MIT License](LICENSE).
