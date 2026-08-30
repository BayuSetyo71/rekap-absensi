# Dokumentasi Dashboard (Catatan Refactoring)

> [!NOTE]
> **Pembaruan Konsep Sistem**:
> Sesuai kebutuhan antarmuka pengguna, dashboard lama telah direfaktor dan dipisahkan menjadi dua modul:
> 1. **[Portal Menu Utama (App Launcher)](file:///c:/laragon/www/absensi/docs/portal_menu/portal_menu_launcher.md)**: Landing page interaktif setelah login berisi manajemen katalog modul terkelompok berdasarkan role dengan fitur live search.
> 2. **[Laporan Presensi](file:///c:/laragon/www/absensi/docs/laporan/laporan_presensi.md)**: Halaman khusus rekapitulasi, KPI, analitik grafik tren, tabel data kehadiran, dan fitur **Export Excel & PDF**.

---

## 1. Komponen & File Terkait
- **Controller**: `app/Http/Controllers/DashboardController.php`
- **View**: `resources/views/dashboard/index.blade.php`
- **Visualisasi Chart**: Pustaka `Chart.js` (Line Area Chart & Doughnut Chart)
- **Rute (Route)**: `GET /dashboard` (`dashboard`)

---

## 2. Fitur & Widget Dashboard

### A. Banner Sambutan & Aksi Cepat
- Menampilkan sapaan pengguna, role aktif, periode bulan saat ini (misal: *Agustus 2026*), dan jam sistem *live real-time* (WIB).
- Tombol aksi cepat: **Inject Data Excel** dan **Unduh Template**.

### B. 6 Kartu Metrik Utama Presensi (KPI Cards)
1. **Tingkat Kehadiran (%)**: Persentase rasio kehadiran $(Hadir + Terlambat) / Total$.
2. **Total Record Bulan Ini**: Total catatan presensi yang terdata dalam periode aktif.
3. **Hadir Tepat Waktu**: Jumlah scan masuk $\le$ `07:30`.
4. **Terlambat**: Jumlah scan masuk $>$ `07:30`.
5. **Izin & Sakit**: Gabungan jumlah permohonan izin dan sakit.
6. **Alpa (Tanpa Keterangan)**: Jumlah absensi tanpa scan kehadiran.

### C. Analisis Visual & Grafik (Chart.js)
1. **Grafik Tren Kehadiran Harian**:
   - Area line chart yang memvisualisasikan tren 10 tanggal presensi terakhir.
   - 3 Garis: *Hadir Tepat Waktu (Hijau)*, *Terlambat (Kuning/Oranye)*, dan *Izin/Sakit (Biru)*.
2. **Diagram Komposisi Status Kehadiran**:
   - Doughnut chart interaktif dengan tooltip persentase dan ringkasan angka di bagian bawah.

### D. Log Catatan Presensi Terkini
- Tabel riwayat kehadiran terbaru yang menampilkan Pegawai (avatar, nama, NIP), Tanggal, Jam Masuk, Jam Pulang, Status Badge, dan Keterangan.
- Dilengkapi tombol navigasi cepat **Lihat Semua** yang mengarah langsung ke menu Data Absensi lengkap.

### E. Ringkasan Master Data & Pintasan Navigasi
- Rekap total pegawai aktif, total role, total modul menu, serta tombol pintasan menu sesuai izin pengguna.
