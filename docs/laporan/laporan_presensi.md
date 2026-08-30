# Dokumentasi Modul Laporan Presensi & Kehadiran

Modul **Laporan Presensi** merupakan fitur analitik eksekutif dan rekapitulasi presensi yang dipindahkan dari dashboard lama menjadi menu laporan mandiri dengan kapabilitas ekspor laporan resmi.

---

## 1. Spesifikasi Teknis & Berkas Terkait
- **Controller**: `app/Http/Controllers/ReportController.php`
- **View Web**: `resources/views/reports/index.blade.php`
- **Template PDF**: `resources/views/reports/pdf.blade.php`
- **Pustaka Ekspor**:
  - **Excel**: `PhpOffice\PhpSpreadsheet` (Format file `.xlsx` dengan styling header dan formula summary)
  - **PDF**: `Barryvdh\DomPDF\Facade\Pdf` (Format file `.pdf` A4 Landscape dengan kop surat resmi yayasan)
- **Visualisasi Grafik**: `Chart.js` (Area Line Chart & Doughnut Chart)
- **Kode Menu**: `reports` (Kelompok: *Presensi & Absensi*)
- **Rute Web**:
  - `GET /reports` (`reports.index`) -> Middleware `permission:reports,view`
  - `GET /reports/export-excel` (`reports.export-excel`) -> Middleware `permission:reports,export`
  - `GET /reports/export-pdf` (`reports.export-pdf`) -> Middleware `permission:reports,export`

---

## 2. Fitur & Fungsionalitas Utama

### A. Filter Periode & Kriteria Fleksibel
- **Filter Bulan & Tahun**: Menentukan periode laporan bulanan yang ingin dianalisis.
- **Filter Unit / Jenjang Sekolah**: Memfilter catatan kehadiran khusus untuk jenjang TK, SD, SMP, SMA, atau seluruh unit.
- **Filter Status**: Memfilter berdasarkan status kehadiran (*Hadir Tepat*, *Terlambat*, *Izin*, *Sakit*, *Alpa*).
- **Pencarian Cepat**: Pencarian nama pegawai atau NIP secara langsung.

### B. 6 Kartu Ringkasan Metrik (KPI Cards)
1. **Tingkat Kehadiran (%)**: Rasio kehadiran $(Hadir + Terlambat) / Total$.
2. **Total Record**: Jumlah seluruh log presensi pada periode aktif.
3. **Hadir Tepat**: Jumlah scan masuk $\le$ jam mulai kerja / $07:30$.
4. **Terlambat**: Jumlah scan masuk $>$ batas jam masuk kerja (dilengkapi total menit terlambat).
5. **Izin & Sakit**: Gabungan total izin dan sakit.
6. **Alpa / Bolos**: Jumlah ketidakhadiran tanpa keterangan scan.

### C. Visualisasi Analitik (Chart.js)
1. **Grafik Tren Kehadiran Harian**: Line Area Chart yang memvisualisasikan dinamika status kehadiran harian sepanjang bulan.
2. **Diagram Komposisi Status**: Doughnut Chart yang menampilkan persentase distribusi status kehadiran.

### D. Tabel Rincian Data Presensi
- Menampilkan daftar log presensi dengan pagination, foto avatar, NIP, unit/jenjang, tanggal, hari, scan masuk, scan pulang, badge status, dan catatan/keterangan.

---

## 3. Fitur Ekspor Data

### A. Ekspor Excel (`.xlsx`)
- Menggunakan pustaka `PhpOffice\PhpSpreadsheet`.
- Dilengkapi header institusi berwana indigo korporat, garis border tipis, format tanggal dan waktu presisi, serta baris total ringkasan otomatis di bagian bawah.

### B. Ekspor PDF (`.pdf`)
- Menggunakan pustaka `Barryvdh\DomPDF\Facade\Pdf` ukuran kertas **A4 Landscape**.
- Dilengkapi Kop Surat Resmi Yayasan, metadata tanggal cetak dan nama petugas, tabel ringkasan KPI eksekutif, data tabel kehadiran bergaris formal, serta blok tanda tangan pengesahan Kepala Kepegawaian & HRD.

---

## 4. Hak Akses (Role-Based Access Control)
- **Super Administrator**: Memiliki akses penuh melihat seluruh data dan mengekspor seluruh laporan.
- **Admin HRD**: Memiliki akses melihat seluruh unit dan mengekspor data laporan.
- **User / Karyawan**: Hanya dapat melihat dan mengekspor catatan kehadiran miliknya sendiri.
