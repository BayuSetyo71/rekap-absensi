# Dokumentasi Portal Menu & App Launcher (Pengganti Dashboard)

Portal Menu merupakan gerbang utama (landing page) aplikasi setelah pengguna berhasil login, yang dirancang menggantikan konsep dashboard tradisional menjadi katalog modul terkelompok berbasis peran (*Role-Based App Launcher*).

---

## 1. Spesifikasi Teknis & Berkas Terkait
- **Controller**: `app/Http/Controllers/DashboardController.php`
- **View**: `resources/views/dashboard/index.blade.php`
- **Rute Web**: `GET /dashboard` (`dashboard`) -> Middleware `permission:dashboard,view`
- **Helper Terkait**: `get_user_menus()`, `can_do()`, `get_unconfigured_schedules_count()`

---

## 2. Fitur & Komponen Tampilan

### A. Hero Portal Hub
- Sapaan hangat personal kepada pengguna yang aktif.
- Lencana peran aktif pengguna (*Super Administrator*, *Admin HRD*, *Pegawai/Guru*).
- Jam sistem *real-time live* WIB.
- Indikator total modul yang dapat diakses oleh peran aktif.

### B. Live Instant Search & Category Filter
- **Live Instant Search**: Kotak pencarian responsif yang memfilter nama modul, deskripsi fitur, atau kategori kata kunci secara *real-time* saat pengguna mengetik.
- **Shortcut Keyboard**: Menekan tombol `/` pada keyboard akan langsung memfokuskan kursor ke kolom pencarian.
- **Filter Pills / Kategori**: Tombol tab navigasi cepat untuk beralih antar grup (*Semua Modul*, *Presensi & Absensi*, *Jadwal & Jam Kerja*, *Master & Pengaturan*, *Akun Personal*).
- **Empty State Handler**: Tampilan ilustratif ramah apabila pencarian tidak menemukan kecocokan modul dengan tombol reset pencarian instan.

### C. Kartu Modul Terkelompok (Role-Based Grid Cards)
Setiap modul ditampilkan dalam kartu modern dengan elemen:
- **Ikon Visual Dinamis**: Ikon dengan background gradasi khas per modul.
- **Lencana Kategori & Level Akses**: Menampilkan tag kategori dan tingkatan izin pengguna (*Akses Penuh (CRUD)*, *Kelola & Update*, *Lihat & Ekspor*, *Hanya Lihat*).
- **Deskripsi Fungsional**: Ringkasan fungsi modul agar pengguna langsung memahami tujuan menu tersebut.
- **Tombol Navigasi**: Tombol **Buka Menu** dan tombol aksi cepat langsung (misal: tombol **Ekspor**).
- **Badge Peringatan Khusus**: Tampil pada modul *Pengaturan Jam Kerja* jika terdapat pegawai yang belum diatur jadwal kerjanya.

---

## 3. Logika Filter Berdasarkan Role (Role-Based Display)
1. Sistem mengambil daftar menu terdaftar via helper `get_user_menus()`.
2. Jika pengguna adalah `superadmin`, seluruh modul master, jadwal, presensi, dan laporan ditampilkan.
3. Untuk peran lain (seperti `admin` atau `user`), sistem secara otomatis menyaring modul yang memiliki relasi hak akses `can_view = 1`.
4. Modul yang tidak diizinkan otomatis disembunyikan dari grid antarmuka tanpa menyisakan ruang kosong.
