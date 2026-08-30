# Panduan Mengunggah (Push) Project ke GitHub

Dokumen ini menjelaskan langkah-langkah untuk mengunggah repositori lokal aplikasi Absensi ke GitHub.

---

## 1. Prasyarat
1. Sudah memiliki akun **GitHub** (https://github.com).
2. Sudah membuat **Repository Baru** di GitHub (misal bernama: `absensi` atau `laravel-absensi-rbac`).
   > *Catatan*: Saat membuat repositori baru di GitHub, **jangan** centang *"Initialize this repository with a README, .gitignore, or license"* agar repositori kosong dan siap menerima push pertama.

---

## 2. Langkah-Langkah Push ke GitHub

Jika Anda menggunakan **PowerShell** biasa dan perintah `git` belum terbaca (*not recognized*), jalankan perintah pertama di bawah untuk mengaktifkan Git dari Laragon:

### Langkah 0: Aktifkan Git Laragon di PowerShell (Jika Git belum terbaca di PATH)
```powershell
$env:Path += ";C:\laragon\bin\git\cmd;C:\laragon\bin\git\bin"
```

### Langkah 1: Inisialisasi Git Lokal
```powershell
git init
```

### Langkah 2: Tambahkan Semua File ke Staging
```bash
git add .
```

### Langkah 3: Buat Commit Pertama
```bash
git commit -m "feat: inisialisasi sistem absensi, rbac dinamis, jadwal mengajar, dan penggajian guru"
```

### Langkah 4: Ubah Nama Branch Utama ke `main`
```bash
git branch -M main
```

### Langkah 5: Hubungkan ke Repositori GitHub Anda
Ganti URL repositori sesuai dengan URL yang dibuat di GitHub:
```bash
git remote add origin https://github.com/USERNAME_ANDA/NAMA_REPO.git
```
*(Contoh: `git remote add origin https://github.com/tung/absensi.git`)*

### Langkah 6: Push File ke GitHub
```bash
git push -u origin main
```

---

## 3. Catatan Penting Keamanan (.gitignore)
File `.env` yang berisi kredensial database lokal **sudah diabaikan secara otomatis** oleh `.gitignore`, sehingga aman dan tidak akan terunggah ke publik. Di GitHub, orang lain akan menggunakan file `.env.example` sebagai referensi konfigurasi.
