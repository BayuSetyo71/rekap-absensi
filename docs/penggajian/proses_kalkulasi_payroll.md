# Modul Proses Kalkulasi Penggajian Bulanan (`/payrolls`)

Modul **Penggajian Guru (Payroll)** adalah mesin kalkulasi otomatis yang menghubungkan data **Presensi Kehadiran Harian** (`attendances`), **Jadwal Sesi Mengajar Multi-Jenjang** (`employee_teaching_slots`), dan **Master Tarif Honor** (`teaching_rates`).

---

## 1. Logika Kalkulasi Otomatis (Calculation Engine)

Proses kalkulasi dikelola oleh Service: `App\Services\PayrollCalculationService`.

### Alur Langkah Kalkulasi:
1. **Identifikasi Periode**: Admin memilih bulan (misal `2026-08`). Sistem membatasi tanggal dari tanggal 1 hingga akhir bulan.
2. **Filter Kehadiran Guru**:
   - Sistem mencari data `attendances` guru bersangkutan pada periode tersebut dengan status **`hadir`** atau **`terlambat`**.
   - Hari dengan status `izin`, `sakit`, atau `alpa` **tidak dihitung** sebagai sesi mengajar aktif.
3. **Pencocokan Sesi Mengajar**:
   - Untuk setiap tanggal kehadiran, sistem memeriksa hari dalam minggu (`day_of_week`, 1=Senin s.d. 7=Minggu).
   - Mengambil seluruh sesi mengajar guru tersebut di hari itu dari tabel `employee_teaching_slots`.
4. **Pencocokan Tarif (Rate Matching)**:
   - Sistem mencocokkan `unit_id` dan nama `subject` dengan tabel `teaching_rates`.
   - Prioritas 1: Tarif spesifik mapel di unit tersebut.
   - Prioritas 2: Tarif standar `'DEFAULT'` di unit tersebut.
   - Prioritas 3: Fallback tarif dasar yayasan.
5. **Akumulasi Breakdown & Subtotal**:
   $$\text{Durasi Jam} = \frac{\text{Durasi Menit Sesi}}{60}$$
   $$\text{Subtotal Sesi} = \text{Durasi Jam} \times \text{Tarif / Jam}$$
6. **Rekapitulasi Gaji Bersih (Take Home Pay)**:
   $$\text{Take Home Pay} = \text{Total Honor Mengajar Kotor} + \text{Total Tunjangan} - \text{Total Potongan}$$

---

## 2. Struktur Skema Database Transaksi Payroll

### A. Header Penggajian (`payrolls`)
- `id` (PK)
- `user_id` (FK to `users`)
- `period_month` (VARCHAR 7, misal: `'2026-08'`)
- `total_present_days` (INTEGER)
- `total_sessions_taught` (INTEGER)
- `total_hours_taught` (DECIMAL 8,2)
- `gross_teaching_amount` (DECIMAL 15,2)
- `total_allowances` (DECIMAL 15,2)
- `total_deductions` (DECIMAL 15,2)
- `net_salary` (DECIMAL 15,2)
- `status` (`'draft'`, `'locked'`, `'paid'`)
- `paid_at` (TIMESTAMP)
- `processed_by` (FK to `users`)

### B. Rincian Breakdown Sesi Mengajar (`payroll_details`)
- `id` (PK)
- `payroll_id` (FK to `payrolls` cascade)
- `unit_id` (FK to `units`)
- `subject` (VARCHAR)
- `total_sessions` (INTEGER)
- `total_hours` (DECIMAL 8,2)
- `rate_applied` (DECIMAL 12,2)
- `subtotal` (DECIMAL 15,2)

### C. Penyesuaian Gaji (`payroll_adjustments`)
- `id` (PK)
- `payroll_id` (FK to `payrolls` cascade)
- `type` (`'allowance'` untuk tunjangan, `'deduction'` untuk potongan)
- `name` (VARCHAR, misal: 'Tunjangan Wali Kelas', 'Kasbon Koperasi')
- `amount` (DECIMAL 12,2)
- `notes` (TEXT)

---

## 3. Rute & Hak Akses (RBAC)

Kode Menu: `payrolls`

| Endpoint URL | Method | Nama Rute | Izin RBAC | Fungsi |
|---|---|---|---|---|
| `/payrolls` | `GET` | `payrolls.index` | `view` | Dashboard KPI dan tabel rekap gaji bulanan |
| `/payrolls/generate` | `POST` | `payrolls.generate` | `create` | Eksekusi mesin hitung gaji otomatis massal/perorangan |
| `/payrolls/{payroll}` | `GET` | `payrolls.show` | `view` | Halaman rincian breakdown mengajar & penyesuaian |
| `/payrolls/{payroll}/adjustments` | `POST` | `payrolls.adjustments.store` | `update` | Tambah tunjangan atau potongan gaji |
| `/payrolls/{payroll}/adjustments/{adjustment}` | `DELETE` | `payrolls.adjustments.destroy` | `update` | Hapus komponen tunjangan/potongan |
| `/payrolls/{payroll}/status` | `PUT/POST` | `payrolls.status.update` | `update` | Update status: Draft $\rightarrow$ Locked $\rightarrow$ Paid |
| `/payrolls/{payroll}/recalculate` | `POST` | `payrolls.recalculate` | `update` | Sinkronisasi ulang data jam mengajar & presensi |
| `/payrolls/{payroll}` | `DELETE` | `payrolls.destroy` | `delete` | Hapus draft payroll |
