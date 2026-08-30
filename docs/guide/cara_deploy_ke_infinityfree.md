# Panduan Lengkap Deploy Aplikasi Laravel ke InfinityFree

Panduan ini menjelaskan langkah demi langkah cara mengunggah (deploy) sistem aplikasi **Absensi & Payroll** berbasis Laravel ke layanan hosting gratis **[InfinityFree](https://dash.infinityfree.com/)**.

---

## ⚠️ Karakteristik & Hal yang Perlu Diperhatikan di InfinityFree
1. **Tidak Ada Akses SSH / Terminal**: Perintah seperti `php artisan migrate`, `composer install`, atau `php artisan storage:link` tidak bisa dijalankan langsung di server hosting. Seluruh persiapan dependensi (`vendor/`) harus dilakukan di komputer lokal terlebih dahulu.
2. **Document Root adalah `htdocs/`**: File publik (`index.php`, asset css/js) harus berada di dalam folder `htdocs/`.
3. **Kredensial Database Khusus**: Host database bukan `localhost`/`127.0.0.1`, melainkan host MySQL khusus (contoh: `sql123.infinityfree.com`).

---

## Langkah 1: Persiapan di Komputer Lokal

### 1.1 Optimasi & Siapkan Folder Vendor
Buka terminal di komputer lokal pada folder project `c:\laragon\www\absensi` dan jalankan:
```bash
# Bersihkan cache development lokal
php artisan optimize:clear

# Pastikan folder vendor sudah lengkap
composer install --optimize-autoloader --no-dev
```

### 1.2 Export Database MySQL Lokal (Pilih Salah Satu)

Ada dua opsi untuk menyiapkan database yang akan diexport:

* **Opsi A: Database Bersih / Fresh (Direkomendasikan untuk Deploy Baru)**
  Jika Anda ingin database **kosong dari riwayat transaksi** (tanpa data absensi lama, tanpa data gaji lama, dll), tetapi tetap memiliki struktur tabel, menu, hak akses, dan akun admin awal:
  1. Jalankan perintah ini di terminal lokal:
     ```bash
     php artisan migrate:fresh --seed
     ```
  2. Buka **phpMyAdmin** lokal di Laragon (`http://localhost/phpmyadmin`) atau **HeidiSQL**.
  3. Pilih database `absensi` -> Klik tab **Export** -> Klik **Go / Kirim** (simpan file sebagai `absensi_backup.sql`).

* **Opsi B: Database Lengkap Beserta Seluruh Data Saat Ini**
  Jika ingin membawa seluruh riwayat data absensi dan pegawai yang sudah ada di lokal:
  1. Langsung buka **phpMyAdmin** lokal (`http://localhost/phpmyadmin`).
  2. Pilih database `absensi` -> Klik tab **Export** -> Simpan sebagai `absensi_backup.sql`.


### 1.3 Siapkan File ZIP Project
Kompres (ZIP) seluruh file di folder `absensi`, **KECUALI**:
- Folder `.git` (tidak perlu diupload).
- File `node_modules` (jika ada).

---

## Langkah 2: Buat Akun & Database di InfinityFree

1. Login ke Dashboard [InfinityFree](https://dash.infinityfree.com/).
2. Masuk ke menu **Accounts** -> Pilih akun hosting Anda -> Klik **Control Panel (vPanel)**.
3. Di Control Panel, cari menu **MySQL Databases**:
   - Buat database baru (misal diberi nama: `absensi`). Nama lengkapnya akan menjadi seperti `if0_12345678_absensi`.
   - Catat detail kredensial database berikut yang tertera di halaman tersebut:
     - **MySQL Host Name**: (contoh: `sql300.infinityfree.com`)
     - **MySQL User Name**: (contoh: `if0_12345678`)
     - **MySQL Password**: (password vPanel akun Anda)
     - **MySQL Database Name**: (contoh: `if0_12345678_absensi`)
     - **Port**: `3306`

---

## Langkah 3: Import Database ke phpMyAdmin InfinityFree

1. Kembali ke Control Panel InfinityFree, klik ikon **phpMyAdmin**.
2. Klik tombol **Connect** pada database yang baru dibuat.
3. Masuk ke tab **Import**.
4. Pilih file `absensi_backup.sql` yang sudah diexport dari lokal pada Langkah 1.2, lalu klik **Go / Kirim**.

---

## Langkah 4: Upload File ke InfinityFree

Ada 2 cara upload: via **File Manager di Browser** atau via **FTP (FileZilla)**.

### Struktur Folder yang Disarankan di Server:
Di root direktori hosting Anda (di luar `htdocs`), buat folder baru bernama `core`:

```text
/ (Root Hosting)
├── core/                       <-- Berisi semua file inti Laravel
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env                    <-- File konfigurasi produksi
│   └── artisan
└── htdocs/                     <-- Pindahkan SEMUA ISI folder public/ ke sini
    ├── css/
    ├── js/
    ├── images/
    ├── .htaccess
    └── index.php               <-- Diedit path-nya
```

### Langkah Upload:
1. Buka **Online File Manager** di Control Panel InfinityFree.
2. Buat folder baru bernama `core` di root (sejajar dengan `htdocs`).
3. Upload dan ekstrak seluruh isi project Laravel ke dalam folder `core/`.
4. Buka folder `core/public/`, **Pindahkan (Move)** semua file & folder di dalamnya (`index.php`, `.htaccess`, assets css/js) langsung ke dalam folder `htdocs/`.
5. Hapus folder `core/public/` yang sudah kosong.

---

## Langkah 5: Edit File `htdocs/index.php`

Buka dan edit file `htdocs/index.php` melalui File Manager InfinityFree. Sesuaikan baris `require` agar mengarah ke folder `core/`:

Cari baris:
```php
// SEBELUM (Default Laravel):
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

Ubah menjadi:
```php
// SESUDAH (Arahkan ke folder core):
require __DIR__.'/../core/vendor/autoload.php';
$app = require_once __DIR__.'/../core/bootstrap/app.php';
```
Simpan file tersebut.

---

## Langkah 6: Konfigurasi File `core/.env`

Buka dan edit file `core/.env` di File Manager:

```env
APP_NAME="Sistem Absensi"
APP_ENV=production
APP_KEY=base64:PASTE_APP_KEY_DARI_LOKAL_ANDA=
APP_DEBUG=false
APP_URL=http://namadomainanda.infinityfreeapp.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=sql300.infinityfree.com          # Sesuai MySQL Host InfinityFree
DB_PORT=3306
DB_DATABASE=if0_12345678_absensi         # Sesuai DB Name InfinityFree
DB_USERNAME=if0_12345678                 # Sesuai DB User InfinityFree
DB_PASSWORD=password_vpanel_anda         # Sesuai DB Password InfinityFree

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## Langkah 7: Hubungkan Storage Symlink (Tanpa SSH)

Karena InfinityFree tidak menyediakan SSH untuk menjalankan `php artisan storage:link`, Anda bisa membuat route sementara untuk membuat symlink.

1. Buka file `core/routes/web.php`.
2. Tambahkan route berikut di baris paling bawah:
   ```php
   Route::get('/symlink-storage', function () {
       $target = storage_path('app/public');
       $link = $_SERVER['DOCUMENT_ROOT'] . '/storage';
       if (file_exists($link)) {
           return 'Symlink storage sudah ada!';
       }
       symlink($target, $link);
       return 'Symlink storage berhasil dibuat!';
   });
   ```
3. Buka browser dan akses: `http://namadomainanda.infinityfreeapp.com/symlink-storage`.
4. Setelah muncul tulisan *"Symlink storage berhasil dibuat!"*, hapus kembali route tersebut dari `routes/web.php` demi keamanan.

---

## Langkah 8: Pengujian Aplikasi

1. Buka domain InfinityFree Anda di browser: `http://namadomainanda.infinityfreeapp.com`.
2. Lakukan login menggunakan akun demo:
   - **Username**: `superadmin`
   - **Password**: `password`
3. Uji fungsi utama:
   - Dashboard & Portal Launcher
   - Presensi Mandiri
   - Download Slip Gaji PDF & Laporan Excel
