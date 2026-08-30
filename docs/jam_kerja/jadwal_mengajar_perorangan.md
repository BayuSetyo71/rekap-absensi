# Dokumentasi Menu Jadwal Mengajar Perorangan (Jadwal Saya)

Dokumen ini menjelaskan arsitektur, tampilan, dan alur modul **Jadwal Mengajar Saya** (`/my-schedule`) yang menyajikan jadwal mengajar mingguan (Senin sampai Minggu) per jam mengajar berdasarkan pengguna yang sedang login.

---

## 1. Konsep & Tujuan Modul

1. **Personal & Transparan**: Guru dapat melihat jadwal mengajarnya sendiri secara menyeluruh dari hari **Senin sampai Minggu**, lengkap dengan jam mulai, jam selesai, durasi, unit jenjang (TK, SD, SMP, SMA), dan mata pelajaran.
2. **Highlight Hari Ini**: Hari yang sedang berjalan (*Today*) otomatis disorot dengan border kontras dan label "HARI INI", sehingga guru langsung mengetahui kelas apa saja yang harus diajar hari ini.
3. **Sinkronisasi Payroll**: Data slot mengajar pada modul ini menjadi dasar formula kalkulasi otomatis jam riil mengajar pada modul **Penggajian Guru (Payroll)**.

---

## 2. Struktur Tampilan & Antarmuka (`resources/views/my-schedule/index.blade.php`)

### A. Banner Profil & Metrik Mingguan
- Foto avatar guru, NIP, jabatan, dan jenjang yang diampu.
- **4 Kotak Metrik**:
  1. **Beban Jam / Minggu**: Total akumulasi jam mengajar dalam satu pekan.
  2. **Total Sesi / Minggu**: Jumlah frekuensi slot sesi mengajar per pekan.
  3. **Jadwal Hari Ini**: Jumlah sesi dan durasi jam mengajar pada hari berjalan.
  4. **Jenjang Diampu**: Daftar kode unit sekolah (TK, SD, SMP, SMA).

### B. Kalender Grid 7 Hari (Senin s.d. Minggu)
- Setiap kartu hari menampilkan:
  - Header hari (Nama Hari + Total Sesi + Total Jam).
  - List sesi mengajar terurut waktu (`start_time` - `end_time`).
  - Badge jenjang sekolah dengan warna spesifik per unit.
  - Mata pelajaran / topik dan ruang kelas / catatan.
  - Durasi sesi dalam menit.
  - Jika hari libur / tidak ada jadwal: Menampilkan status kosong yang rapi (*Empty State*).

### C. Fitur Ekspor & Integrasi
- Tombol **"Unduh Jadwal PDF"**: Menghasilkan dokumen resmi PDF jadwal personal portrait A4 berlogo yayasan.
- Tombol **"Jadwal Seluruh Guru"**: Pintasan untuk melihat jadwal yayasan secara komprehensif.

---

## 3. Rute & Controller

* **Controller**: [app/Http/Controllers/MyScheduleController.php](file:///c:/laragon/www/absensi/app/Http/Controllers/MyScheduleController.php)
  - Method `index()`: Mengambil jadwal mengajar guru login dan mengelompokkannya ke 7 hari.
* **Rute**:
  - `GET /my-schedule` -> `my-schedule.index` (Middleware: `permission:my-schedule,view`)
* **Ekspor PDF**:
  - `GET /schedule-info/{user}/export-pdf` -> `schedule-info.export-personal-pdf`
