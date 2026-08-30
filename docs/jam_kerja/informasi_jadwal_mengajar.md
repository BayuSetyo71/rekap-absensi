# Modul Informasi Jadwal Mengajar Guru Yayasan

Modul ini adalah halaman **Informasi & Monitoring Jadwal Mengajar** yang terpisah dari halaman konfigurasi/transaksi (`work-schedules`). Modul ini dirancang untuk memudahkan manajemen yayasan, kepala sekolah, guru, dan staf melihat, memantau, dan menganalisis jadwal mengajar harian serta mingguan di seluruh jenjang sekolah (TK, SD, SMP, SMA, dan Yayasan).

---

## 1. Perbedaan Menu Jam Kerja vs Informasi Jadwal

| Aspek | Informasi Jadwal Mengajar (`/schedule-info`) | Pengaturan Jam Kerja (`/work-schedules`) |
| :--- | :--- | :--- |
| **Sifat Menu** | **Informasi / Monitoring / Viewer** | **Transaksi / Konfigurasi CRUD** |
| **Fungsi Utama** | Melihat papan jadwal harian, matriks mingguan, analisis beban jam guru, dan export excel. | Mengatur sesi mengajar per hari, jam masuk/pulang, assign unit jenjang sekolah, dan bulk preset. |
| **Pengguna** | Seluruh Guru, Kepala Sekolah, Admin HRD, Yayasan. | Admin HRD dan Super Administrator. |

---

## 2. Fitur Utama Halaman Informasi Jadwal

### A. Kartu Ringkasan KPI Statistik
- **Guru Mengajar Hari Ini**: Jumlah guru yang memiliki jadwal mengajar pada hari yang dipilih.
- **Total Sesi Pelajaran**: Akumulasi sesi kelas di seluruh jenjang (TK, SD, SMP, SMA).
- **Total Durasi Mengajar**: Akumulasi jam mengajar aktif.
- **Total Guru Yayasan**: Jumlah guru terdaftar beserta status pengaturannya.

### B. Pilihan Hari & Filter Fleksibel
- **Pill Navigasi Hari (Senin - Minggu)**: Pengguna dapat berpindah hari dengan 1 klik, dilengkapi indikator badge **"Hari Ini"**.
- **Filter Unit Sekolah**: Menyaring sesi khusus di TK, SD, SMP, SMA, atau Kantor Yayasan.
- **Pencarian Realtime**: Mencari berdasarkan nama guru, NIP, atau mata pelajaran/kelas.
- **Export Laporan Jadwal ke PDF & Excel**:
  - **Export PDF Jadwal (Laporan Keseluruhan)**: Mengunduh laporan matriks jadwal seluruh guru yayasan dalam format **Landscape A4** yang rapi dan siap dicetak/dibagikan.
  - **Export Excel Jadwal**: Mengunduh seluruh matriks jadwal guru ke file spreadsheet Excel.
- **Export PDF Jadwal Pribadi Guru (Personal Teaching Card)**:
  - Pada setiap baris guru (baik di tabel mingguan, beban kerja, maupun kartu sesi harian), terdapat tombol **`📄 Cetak PDF`**.
  - Menghasilkan dokumen **Portrait A4 resmi** khusus guru tersebut:
    - Informasi guru (Nama, NIP, Jabatan, Jenjang yang diampu).
    - Rincian sesi mengajar per hari (Senin s.d. Minggu): jam mulai, jam selesai, unit sekolah, mapel/kelas, dan durasi menit.
    - Rekap total sesi dan total beban jam mengajar mingguan.
    - Lembar tanda tangan resmi untuk Kepala Yayasan/HRD dan Guru bersangkutan.

---

## 3. Struktur Rute & Hak Akses (RBAC)

- **Rute URL**:
  - `GET /schedule-info` (`schedule-info.index`) - Izin: `can_do('schedule-info', 'view')`
  - `GET /schedule-info/export` (`schedule-info.export`) - Izin: `can_do('schedule-info', 'export')`
  - `GET /schedule-info/export-pdf` (`schedule-info.export-pdf`) - Izin: `can_do('schedule-info', 'export')`
  - `GET /schedule-info/{user}/export-pdf` (`schedule-info.export-personal-pdf`) - Izin: `can_do('schedule-info', 'export')`
- **Controller**: `app/Http/Controllers/ScheduleInfoController.php`
- **Views**:
  - `resources/views/schedule-info/index.blade.php` (Halaman Web Informasi)
  - `resources/views/schedule-info/pdf-all.blade.php` (Template PDF Keseluruhan - Landscape)
  - `resources/views/schedule-info/pdf-personal.blade.php` (Template PDF Pribadi - Portrait)
