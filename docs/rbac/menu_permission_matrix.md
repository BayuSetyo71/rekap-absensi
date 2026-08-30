# Dokumentasi Dynamic Menu & Granular CRUD Permission Matrix

Dokumen ini menjelaskan arsitektur sistem hak akses menu dinamis dan granular CRUD permissions per role.

---

## 1. Arsitektur Database Otorisasi

Sistem ini memiliki 3 entitas utama dalam menentukan hak akses:
1. **`roles`**: Menyimpan daftar peran pengguna (contoh: `superadmin`, `admin`, `user`).
2. **`menus`**: Menyimpan struktur menu navigasi, kode menu (`code`), rute (`route_name`), url, urutan (`order_index`), ikon (`icon`), dan izin aksi yang didukung (`has_create`, `has_update`, `has_delete`, `has_export`).
3. **`role_menu_permissions`**: Menyimpan matriks persilangan antara `role_id` dan `menu_id` dengan status boolean untuk setiap aksi:
   - `can_view` : Izin melihat menu di navigasi dan membuka halaman.
   - `can_create` : Izin menambah data (Create/Store).
   - `can_update` : Izin mengubah data (Edit/Update).
   - `can_delete` : Izin menghapus data (Destroy).
   - `can_export` : Izin mengunduh/ekspor data (CSV/PDF/Print).

---

## 2. Cara Kerja Navigasi Sidebar Dinamis

Fungsi helper `get_user_menus()` di `app/Helpers/PermissionHelper.php`:
1. Membaca user yang sedang login via `Auth::user()`.
2. Jika user memiliki role `superadmin`, seluruh menu aktif langsung ditampilkan.
3. Untuk role lain, sistem memfilter menu level utama dan sub-menu yang memiliki relasi `can_view = 1`.
4. Jika suatu menu utama memiliki sub-menu yang diizinkan, maka menu utama otomatis ikut tampil sebagai pembungkus dropdown.

---

## 3. Cara Mengamankan Route (Middleware)

Gunakan middleware `permission:menu_code,action` pada rute di `routes/web.php`:

```php
// Mengamankan akses halaman index (Read/View)
Route::get('/users', [UserController::class, 'index'])->middleware('permission:users,view');

// Mengamankan aksi simpan data baru (Create)
Route::post('/users', [UserController::class, 'store'])->middleware('permission:users,create');

// Mengamankan aksi update data (Update)
Route::post('/users/{user}/update', [UserController::class, 'update'])->middleware('permission:users,update');

// Mengamankan aksi hapus (Delete)
Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:users,delete');

// Mengamankan aksi ekspor (Export)
Route::get('/users/export', [UserController::class, 'export'])->middleware('permission:users,export');
```

---

## 4. Cara Mengamankan Elemen Tampilan UI di Blade

Sistem menyediakan custom Blade Directives:

### A. `@canDo('menu_code', 'action')`
```blade
@canDo('users', 'create')
    <button class="btn btn-primary">Tambah Pengguna</button>
@endcanDo
```

### B. Directives Spesifik per Aksi:
- `@canView('users')` ... `@endcanView`
- `@canCreate('users')` ... `@endcanCreate`
- `@canUpdate('users')` ... `@endcanUpdate`
- `@canDelete('users')` ... `@endcanDelete`
- `@canExport('users')` ... `@endcanExport`

Contoh penggunaan tombol aksi tabel:
```blade
@canUpdate('users')
    <button class="btn btn-warning btn-edit" data-id="{{ $u->id }}">Edit</button>
@endcanUpdate

@canDelete('users')
    <button class="btn btn-danger btn-delete" data-id="{{ $u->id }}">Hapus</button>
@endcanDelete
```

---

## 5. Matriks Izin (UI Interaktif dengan jQuery)
- Halaman Matriks Hak Akses diakses melalui route `roles.permissions` (`/roles/{role}/permissions`).
- **Interaktivitas jQuery**:
  - Tombol **Centang Semua** & **Hapus Semua Centang** untuk seluruh matriks.
  - Checkbox per kolom (Header `LIHAT`, `TAMBAH`, `UBAH`, `HAPUS`, `EXPORT`) untuk memilih seluruh menu dalam satu kolom aksi.
  - Checkbox per baris (**Semua Baris**) untuk memilih seluruh aksi dalam satu menu tertentu.
  - Logika otomatis: Memilih `Tambah`, `Ubah`, atau `Hapus` akan otomatis mencentang `Lihat`.
  - Simpan perubahan melalui **jQuery AJAX** dengan visual loading dan SweetAlert2 toast tanpa reload halaman.
