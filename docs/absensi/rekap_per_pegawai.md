# Dokumentasi Modul Rekap Absen Per Pegawai

Modul **Rekap Absen Per Pegawai** (`attendance-recap`) menyajikan ringkasan dan analisis kehadiran individual seluruh pegawai dalam satu tabel agregat interaktif, dilengkapi visualisasi grafik Chart.js, modal riwayat harian AJAX, serta ekspor format Excel.

---

## 1. Komponen & Berkas Terkait

- **Controller**: `app/Http/Controllers/AttendanceRecapController.php`
- **View Utama**: `resources/views/attendance-recap/index.blade.php`
- **View Halaman Penuh**: `resources/views/attendance-recap/show.blade.php`
- **Model Terkait**: `App\Models\User`, `App\Models\Attendance`, `App\Models\Menu`
- **Kode Menu**: `attendance-recap`
- **Rute Web (Routes)**:
  - `GET /attendance-recap` (`attendance-recap.index`) - Matriks rekapitulasi seluruh pegawai
  - `GET /attendance-recap/export` (`attendance-recap.export`) - Ekspor spreadsheet (.xlsx)
  - `GET /attendance-recap/{user}/detail-ajax` (`attendance-recap.detail.ajax`) - API JSON detail harian
  - `GET /attendance-recap/{user}/chart-ajax` (`attendance-recap.chart.ajax`) - API JSON visualisasi grafik
  - `GET /attendance-recap/{user}` (`attendance-recap.show`) - Halaman detail individu penuh

---

## 2. Fitur & Kolom Data yang Ditampilkan

### A. Kartu Ringkasan Eksekutif (Hero KPI Banner)
1. **Rata-rata Kehadiran (%)**: Rata-rata persentase kehadiran seluruh pegawai pada periode aktif.
2. **Total Check-In**: Akumulasi frekuensi scan masuk tercatat.
3. **Total Check-Out**: Akumulasi frekuensi scan pulang tercatat.
4. **Kali Terlambat**: Jumlah insiden kedatangan di atas pukul `07:30 WIB`.

### B. Struktur Tabel Rekapitulasi Pegawai (Kompak & Responsif)
Untuk memaksimalkan keterbacaan pada semua ukuran layar tanpa terpotong (*overflow*), kolom ditata secara cerdas ke dalam 10 kolom inti:
1. **NO**: Nomor urut daftar.
2. **PEGAWAI & DEPARTEMEN**: Foto profil, Nama Lengkap, NIP, Jabatan, dan Departemen (disusun dalam 2 baris elegan).
3. **HARI**: Total hari kerja tercatat dalam rentang filter.
4. **CHECK-IN / OUT**: Jumlah hari melakukan scan masuk dan scan pulang secara berdampingan (*inline badge*).
5. **TEPAT WAKTU**: Jumlah kehadiran tepat waktu $\le 07:30\text{ WIB}$.
6. **DATANG AWAL**: Akumulasi total waktu (format `Xj Ym`, contoh: `3j 45m`) kedatangan sebelum $07:30\text{ WIB}$ (Apresiasi Kedisiplinan).
7. **TERLAMBAT**: Frekuensi scan masuk $> 07:30\text{ WIB}$ beserta akumulasi durasi keterlambatan (contoh: `1x (15m)`).
8. **IZIN / SAKIT / ALPA**: Ringkasan mini badge ketidakhadiran berizin maupun alpa.
9. **% HADIR**: Rasio kehadiran dilengkapi *progress bar* proporsional berkode warna.
10. **AKSI**: Tombol **Detail** (membuka modal log harian) dan **Grafik** (membuka modal grafik analitik).

### C. Halaman Lengkap Visual Grafik & Analitik (`/attendance-recap/{user}/chart`)
Halaman khusus yang menampilkan analisis grafik berskala penuh:
1. **5 Kartu Metrik KPI & Apresiasi**:
   - Tingkat Kehadiran (%)
   - Total Waktu Datang Lebih Awal (Apresiasi Kedisiplinan)
   - Total Waktu Keterlambatan
   - Total Check-In & Check-Out
   - Total Ketidakhadiran (Izin/Sakit/Alpa)
2. **4 Visualisasi Grafik Interaktif (Chart.js)**:
   - **Grafik 1 (Doughnut Chart)**: Komposisi Status Kehadiran (*Tepat Waktu, Terlambat, Izin, Sakit, Alpa*).
   - **Grafik 2 (Multi-Line Chart)**: Tren Jam Kedatangan & Jam Pulang Harian vs Batas Waktu Masuk (`07:30`) & Standar Pulang (`16:30`).
   - **Grafik 3 (Bar Chart)**: Pola Distribusi Kehadiran per Hari Kerja (*Senin s/d Sabtu*).
   - **Grafik 4 (Bar & Line Chart)**: Durasi Jam Kerja Harian vs Target Standar 8 Jam.
3. **Filter Periode Analisis**:
   - Memungkinkan perubahan rentang tanggal analisis langsung di halaman grafik.

---

## 3. Struktur Rute & Endpoint

| Metode | URL | Nama Rute | Middleware / Permission | Fungsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/attendance-recap` | `attendance-recap.index` | `permission:attendance-recap,view` | Halaman utama rekapitulasi pegawai |
| `GET` | `/attendance-recap/export` | `attendance-recap.export` | `permission:attendance-recap,export` | Unduh laporan excel (.xlsx) lengkap |
| `GET` | `/attendance-recap/{user}/detail-ajax` | `attendance-recap.detail.ajax` | `permission:attendance-recap,view` | JSON data log harian untuk modal detail |
| `GET` | `/attendance-recap/{user}/chart-ajax` | `attendance-recap.chart.ajax` | `permission:attendance-recap,view` | JSON multi-chart analitik |
| `GET` | `/attendance-recap/{user}/chart` | `attendance-recap.chart` | `permission:attendance-recap,view` | Halaman Penuh Visual Grafik Analitik |
| `GET` | `/attendance-recap/{user}` | `attendance-recap.show` | `permission:attendance-recap,view` | Halaman Penuh Tabel Riwayat Presensi |

---

## 4. Matriks Perizinan & Hak Akses (RBAC)

| Role | Hak Akses Rekap Absen |
|---|---|
| **Super Administrator** | Akses Penuh (Dapat melihat seluruh pegawai, filter departemen, grafik, detail & ekspor Excel) |
| **Admin HRD** | Dapat melihat seluruh pegawai, memfilter data, melihat detail harian, grafik visual, dan mengunduh Excel |
| **Karyawan / User** | Terisolasi otomatis hanya melihat rekap data presensi miliknya sendiri |

---

## 5. Format Ekspor Excel (.xlsx)

- Library: `PhpOffice\PhpSpreadsheet`
- Styling:
  - Judul Laporan & Periode Tanggal Terpilih
  - Header tabel berwarna ungu gelap (`#1E1B4B`) dengan teks putih tebal
  - Zebra striping pada baris data genap (`#F8FAFC`)
  - Kolom formula otomatis `SUM` pada baris total akhir
  - Kolom persentase kehadiran dan pemformatan sel NIP sebagai teks
