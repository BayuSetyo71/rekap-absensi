# Dokumentasi Portal Karyawan (Employee Self-Service)

Modul ini menjelaskan arsitektur, antarmuka, dan alur bisnis sistem dari sudut pandang **Guru / Karyawan (Role `user`)**.

---

## 1. Konsep & Filosofi Desain

Aplikasi dirancang dengan prinsip **Role-Centric UI & Hardware-Based Attendance**:
1. **Presensi Fisik Mesin Fingerprint**: Absensi masuk dan pulang dilakukan langsung pada mesin fingerprint fisik sekolah.
2. **Dashboard Karyawan yang Bersih & Fokus**:
   - **Hero Banner Profil Karyawan**: Foto profil, NIP, Unit Sekolah, Jabatan, dan Jam Server Realtime WIB.
   - **Widget Jadwal Mengajar Hari Ini**: Daftar seluruh sesi/slot mengajar hari ini lengkap dengan unit jenjang (TK, SD, SMP, SMA), jam mulai-selesai, mata pelajaran, dan durasi menit.
   - **Ringkasan KPI Kehadiran Bulanan**: Total Hari Hadir, Terlambat, Izin/Sakit, dan Persentase Kehadiran.
   - **Akses Cepat Modul Karyawan**:
     1. *Jadwal Mengajar Saya* (`/my-schedule`)
     2. *Riwayat Log Absensi* (`/attendances`)
     3. *Grafik Kehadiran Saya* (`/attendance-recap/{user}/chart?start_date=YYYY-MM-01&end_date=YYYY-MM-31` - default bulan berjalan)
     4. *Slip Gaji Digital Saya* (`/payrolls`)

---

## 2. Fitur Khusus Grafik Kehadiran Pribadi

Saat guru / karyawan mengklik menu atau shortcut **Grafik Kehadiran**:
- Sistem secara otomatis mengarahkan langsung ke rute `attendance-recap.chart` milik akun guru yang sedang login dengan rentang tanggal default **Awal Bulan s.d. Akhir Bulan ini** (`default bulan berjalan`).
- Menyajikan 4 chart interaktif:
  1. *Grafik Donat / Pie*: Distribusi status kehadiran (Tepat Waktu, Terlambat, Izin, Sakit, Alpha).
  2. *Grafik Garis / Tren Kedisiplinan*: Jam scan masuk harian vs ambang batas toleransi jadwal kerja.
  3. *Grafik Batang*: Akumulasi menit keterlambatan harian.
  4. *Grafik Durasi Kerja*: Total jam kerja harian dari scan masuk s.d. scan pulang.

---

## 3. Matriks Hak Akses Karyawan (RBAC)

Berdasarkan `RoleMenuPermissionSeeder`:

| Kode Menu | Nama Menu | Lihat (`can_view`) | Tambah (`can_create`) | Ubah (`can_update`) | Hapus (`can_delete`) | Ekspor (`can_export`) |
|---|---|---|---|---|---|---|
| `dashboard` | Beranda / Portal Karyawan | ✅ Ya | ❌ Tidak | ❌ Tidak | ❌ Tidak | ❌ Tidak |
| `my-schedule` | Jadwal Mengajar Saya (Personal) | ✅ Ya | ❌ Tidak | ❌ Tidak | ❌ Tidak | ✅ Unduh PDF |
| `schedule-info` | Informasi Jadwal Guru Yayasan | ✅ Ya | ❌ Tidak | ❌ Tidak | ❌ Tidak | ✅ Unduh PDF |
| `attendances` | Riwayat Log Presensi | ✅ Ya | ❌ Tidak | ❌ Tidak | ❌ Tidak | ✅ Unduh Excel |
| `attendance-recap` | Rekap & Grafik Absen Pegawai | ✅ Ya | ❌ Tidak | ❌ Tidak | ❌ Tidak | ✅ Unduh Excel |
| `payrolls` | Slip Gaji Digital Saya | ✅ Ya | ❌ Tidak | ❌ Tidak | ❌ Tidak | ✅ Unduh Slip PDF |
| `work-schedules` | Pengaturan Jam Kerja Yayasan | ❌ Sembunyi | ❌ Tidak | ❌ Tidak | ❌ Tidak | ❌ Tidak |
| `teaching-rates` | Tarif Honor Mengajar Yayasan | ❌ Sembunyi | ❌ Tidak | ❌ Tidak | ❌ Tidak | ❌ Tidak |
| `reports` | Laporan Presensi Yayasan | ❌ Sembunyi | ❌ Tidak | ❌ Tidak | ❌ Tidak | ❌ Tidak |
| `users` | Manajemen Pengguna | ❌ Sembunyi | ❌ Tidak | ❌ Tidak | ❌ Tidak | ❌ Tidak |
| `roles` | Manajemen Role & Hak Akses | ❌ Sembunyi | ❌ Tidak | ❌ Tidak | ❌ Tidak | ❌ Tidak |
| `menus` | Manajemen Struktur Menu | ❌ Sembunyi | ❌ Tidak | ❌ Tidak | ❌ Tidak | ❌ Tidak |
