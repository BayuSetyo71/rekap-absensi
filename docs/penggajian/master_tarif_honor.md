# Modul Master Tarif Honor Mengajar (`/teaching-rates`)

Modul **Tarif Honor Mengajar** dirancang untuk mengelola matriks honorarium guru per jam mengajar berdasarkan jenjang sekolah (**TK / PAUD, SD, SMP, SMA, dan Unit Yayasan**) serta mata pelajaran spesifik.

---

## 1. Konsep & Aturan Bisnis

1. **Tarif Standar Jenjang (`DEFAULT`)**:
   - Setiap jenjang sekolah memiliki satu tarif standar dengan `subject_name = 'DEFAULT'`.
   - Contoh Tarif Standar:
     - **TK / PAUD**: Rp 30.000 / Jam
     - **SD**: Rp 35.000 / Jam
     - **SMP**: Rp 45.000 / Jam
     - **SMA**: Rp 55.000 / Jam
     - **Yayasan**: Rp 50.000 / Jam
   - Jika guru mengajar mata pelajaran umum yang tidak memiliki tarif khusus (misal: Tematik, PKn, dsb), sistem otomatis menerapkan tarif standar jenjang tersebut.

2. **Tarif Khusus Mata Pelajaran**:
   - HRD dapat menambahkan nominal honor khusus untuk mapel tertentu di jenjang tertentu.
   - Contoh:
     - **SMA** - `Informatika / Coding`: Rp 75.000 / Jam
     - **SMA** - `Bahasa Inggris / TOEFL`: Rp 65.000 / Jam
     - **SMP** - `Informatika`: Rp 50.000 / Jam
     - **SD** - `IT / Komputer`: Rp 40.000 / Jam

3. **Status Aktif & Penonaktifan Tarif**:
   - Setiap tarif memiliki switch status aktif/nonaktif via toggle realtime AJAX.
   - Tarif yang dinonaktifkan tidak akan digunakan dalam kalkulasi penggajian baru.

---

## 2. Struktur Skema Database (`teaching_rates`)

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `BIGINT (PK)` | Auto increment primary key |
| `unit_id` | `BIGINT (FK)` | Relasi ke tabel `units` (TK, SD, SMP, SMA, Yayasan) |
| `subject_name` | `VARCHAR(150)` | Nama mapel spesifik atau `'DEFAULT'` untuk tarif umum |
| `rate_per_hour` | `DECIMAL(12,2)` | Nominal honor per 60 menit mengajar (Rupiah) |
| `rate_type` | `VARCHAR(30)` | `'per_hour'` (per 60 menit) atau `'per_session'` |
| `notes` | `TEXT` | Catatan pendukung / keterangan tarif |
| `is_active` | `BOOLEAN` | Status aktif (`true` / `false`) |

---

## 3. Rute dan Hak Akses (RBAC)

Kode Menu: `teaching-rates`

| Endpoint URL | Method | Nama Rute | Izin RBAC | Fungsi |
|---|---|---|---|---|
| `/teaching-rates` | `GET` | `teaching-rates.index` | `view` | Tampilan matriks tabel tarif & KPI ringkasan |
| `/teaching-rates` | `POST` | `teaching-rates.store` | `create` | Tambah aturan tarif baru |
| `/teaching-rates/{rate}/edit` | `GET` | `teaching-rates.edit` | `update` | Ambil data tarif (AJAX JSON Modal) |
| `/teaching-rates/{rate}/update` | `PUT/POST` | `teaching-rates.update` | `update` | Perbarui data tarif |
| `/teaching-rates/{rate}` | `DELETE` | `teaching-rates.destroy` | `delete` | Hapus aturan tarif |
| `/teaching-rates/{rate}/toggle` | `POST` | `teaching-rates.toggle` | `update` | Toggle switch aktif/nonaktif via AJAX |

---

## 4. Cara Penggunaan di Aplikasi

1. Buka sidebar menu **Penggajian & Honor** $\rightarrow$ **Tarif Honor Mengajar**.
2. Klik tombol **`+ Tambah Tarif Baru`** pada sudut kanan atas.
3. Pilih Jenjang Unit (contoh: `SMA`), isi Nama Mapel (`Informatika / Coding`), dan Nominal Honor (`75000`).
4. Klik **Simpan Tarif**.
5. Untuk mengubah nominal, klik ikon **Pensil (Edit)** pada baris yang bersangkutan.
