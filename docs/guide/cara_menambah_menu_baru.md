# Panduan Menambahkan Menu Baru ke Sistem

Panduan ini disiapkan agar ketika Anda (atau AI) ingin menambahkan modul menu bisnis baru (seperti Presensi, Shift Kerja, Pengajuan Cuti, Laporan, dll.), alur penambahannya konsisten dan langsung terintegrasi dengan Dynamic Menu RBAC.

---

## Langkah 1: Buat Controller dan View
Buat controller baru di `app/Http/Controllers/` dan view baru di `resources/views/`.

Contoh: Menu Presensi
1. Controller: `app/Http/Controllers/AttendanceController.php`
2. View: `resources/views/attendances/index.blade.php`

---

## Langkah 2: Daftarkan Rute di `routes/web.php`
Daftarkan rute dengan memproteksinya menggunakan middleware `permission:kode_menu,action`.

```php
Route::prefix('attendances')->name('attendances.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index')->middleware('permission:attendances,view');
    Route::post('/', [AttendanceController::class, 'store'])->name('store')->middleware('permission:attendances,create');
    Route::post('/{attendance}/update', [AttendanceController::class, 'update'])->name('update')->middleware('permission:attendances,update');
    Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy')->middleware('permission:attendances,delete');
    Route::get('/export', [AttendanceController::class, 'export'])->name('export')->middleware('permission:attendances,export');
});
```

---

## Langkah 3: Tambahkan Menu ke Database
Anda dapat menambahkan menu baru melalui 2 cara:

### Cara A: Melalui Menu UI Aplikasi (Direkomendasikan)
1. Login sebagai **Super Admin**.
2. Masuk ke menu **Manajemen Menu** (`/menus`).
3. Klik tombol **Tambah Menu Baru**.
4. Isi form:
   - **Nama Label**: Data Presensi
   - **Kode Menu**: `attendances` (harus sama dengan yang didaftarkan di middleware)
   - **Route**: `attendances.index`
   - **Icon**: `bi bi-fingerprint` (pilih icon dari Bootstrap Icons)
   - **Aksi yang Didukung**: Centang Create, Update, Delete, Export.
5. Klik **Simpan**. Sistem akan otomatis menginisialisasi entri permission pada seluruh role yang ada.

### Cara B: Melalui Seeder / Database Script
```php
use App\Models\Menu;

$menu = Menu::create([
    'code' => 'attendances',
    'name' => 'Data Presensi',
    'route_name' => 'attendances.index',
    'icon' => 'bi bi-fingerprint',
    'order_index' => 5,
    'is_active' => true,
    'has_create' => true,
    'has_update' => true,
    'has_delete' => true,
    'has_export' => true,
]);
```

---

## Langkah 4: Atur Izin Akses per Role
1. Buka menu **Manajemen Role & Izin** (`/roles`).
2. Klik tombol **Izin Menu** pada role yang ingin diatur (misal: role `Karyawan` atau `Admin`).
3. Centang kotak izin yang diperbolehkan untuk menu baru tersebut:
   - `Lihat` (Read/View)
   - `Tambah` (Create)
   - `Ubah` (Update)
   - `Hapus` (Delete)
   - `Export` (Unduh)
4. Klik **Simpan Matriks Hak Akses**.

---

## Langkah 5: Proteksi Tombol di View Blade
Gunakan Blade directive kustom pada tampilan Blade Anda:

```blade
@canCreate('attendances')
    <button class="btn btn-primary" id="btnPresensiMasuk">Absen Masuk</button>
@endcanCreate

@canExport('attendances')
    <a href="{{ route('attendances.export') }}" class="btn btn-success">Download Rekap</a>
@endcanExport

@canDelete('attendances')
    <button class="btn btn-danger btn-delete">Hapus</button>
@endcanDelete
```
