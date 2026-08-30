# Modul Slip Gaji Digital & Ekspor Laporan Yayasan

Modul ini bertanggung jawab atas penerbitan dokumen resmi penggajian, mencakup **Slip Gaji Digital Guru (PDF A4 Portrait)** dan **Laporan Rekapitulasi Beban Penggajian Yayasan (Excel & PDF A4 Landscape)**.

---

## 1. Fitur Utama

### A. Slip Gaji Guru (Single PDF A4 Portrait)
- Menampilkan kop resmi Yayasan Pendidikan dan daftar unit yang dinaungi.
- Identitas lengkap guru (Nama, NIP, Jenjang Bertugas, Jumlah Hari Hadir).
- **Tabel Rincian Realisasi Mengajar**:
  - Kolom Unit / Jenjang Sekolah (TK, SD, SMP, SMA, Yayasan).
  - Kolom Mata Pelajaran.
  - Jumlah Sesi Mengajar.
  - Total Durasi Jam Mengajar.
  - Tarif per Jam yang diterapkan.
  - Subtotal Honor Mengajar per Mata Pelajaran.
- **Rincian Komponen Penyesuaian**:
  - Penambahan Tunjangan (+).
  - Pemotongan Kasbon / Iuran (-).
- **Kotak Take Home Pay (Gaji Bersih)** dengan aksen warna hijau resmi.
- Lembar tanda tangan ganda untuk Bendahara Yayasan dan Guru penerima.

### B. Ekspor Rekapitulasi Gaji Yayasan ke Excel (.xlsx)
- Memetakan seluruh guru pada bulan periode terpilih.
- Kolom NIP, Nama, Jenjang, Hari Hadir, Total Jam Mengajar, Honor Kotor, Tunjangan, Potongan, Take Home Pay, dan Status.
- Dilengkapi formula total otomatis (`=SUM(...)`) pada baris rekapitulasi akhir.

### C. Ekspor Rekapitulasi Gaji Yayasan ke PDF (Landscape A4)
- Menampilkan seluruh data penggajian dalam tata letak horizontal (Landscape).
- Dilengkapi kolom tanda tangan 3 pihak: Admin HRD, Kepala Urusan Keuangan, dan Ketua Yayasan.

---

## 2. Rute dan Hak Akses (RBAC)

| Endpoint URL | Method | Nama Rute | Izin RBAC | Fungsi |
|---|---|---|---|---|
| `/payrolls/{payroll}/slip-pdf` | `GET` | `payrolls.slip-pdf` | `export` | Unduh Slip Gaji PDF individual untuk guru |
| `/payrolls/export-summary-excel` | `GET` | `payrolls.export-excel` | `export` | Unduh Spreadsheet Excel Rekap Yayasan |
| `/payrolls/export-summary-pdf` | `GET` | `payrolls.export-pdf` | `export` | Unduh Dokumen PDF Rekapitulasi Yayasan |

---

## 3. Cara Penggunaan di Aplikasi

1. **Mencetak Slip Gaji Guru**:
   - Buka menu **Penggajian & Honor** $\rightarrow$ **Penggajian Guru (Payroll)**.
   - Pada baris guru yang diinginkan, klik tombol warna merah **`📄 Cetak Slip PDF`**.
   - Dokumen PDF siap cetak / dibagikan ke guru via WhatsApp atau Email.
2. **Mengunduh Laporan Rekapitulasi**:
   - Pilih periode bulan pada form filter di bagian atas tabel.
   - Klik tombol **`Rekap Excel`** atau **`Rekap PDF`** di pojok kanan atas halaman.
