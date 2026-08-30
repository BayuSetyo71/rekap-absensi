# Panduan Auto-Deploy ke InfinityFree Menggunakan GitHub Actions (FTP)

Panduan ini menjelaskan cara menghubungkan repository **GitHub** ke **InfinityFree** sehingga setiap kali Anda melakukan `git push`, perubahan kode akan otomatis diunggah (deploy) ke hosting tanpa perlu upload manual.

---

## 💡 Mengapa Perlu GitHub Actions?
Di InfinityFree (versi gratis):
* **Tidak ada SSH / Terminal** untuk menjalankan `git clone` atau `git pull` langsung di server.
* Sebagai solusinya, kita memanfaatkan **GitHub Actions CI/CD**: GitHub yang akan mengeksekusi proses build dan mengirim file yang berubah ke hosting Anda melalui protokol **FTP**.

---

## Langkah 1: Dapatkan Kredensial FTP InfinityFree

1. Buka dashboard [InfinityFree](https://dash.infinityfree.com/).
2. Pilih akun hosting Anda -> Klik **Control Panel (vPanel)** atau lihat ringkasan akun.
3. Catat detail FTP berikut:
   * **FTP Host / Server**: (contoh: `ftpupload.net`)
   * **FTP Username**: (contoh: `if0_12345678`)
   * **FTP Password**: (password vPanel akun Anda)
   * **FTP Port**: `21`

---

## Langkah 2: Tambahkan Secret di Repository GitHub

1. Buka repository project Anda di **GitHub**.
2. Masuk ke menu **Settings** -> **Secrets and variables** -> **Actions**.
3. Klik **New repository secret** dan tambahkan 3 secret berikut:

| Nama Secret | Nilai / Isi |
| :--- | :--- |
| `FTP_SERVER` | `ftpupload.net` (sesuai host FTP InfinityFree) |
| `FTP_USERNAME` | Username FTP vPanel Anda (misal: `if0_12345678`) |
| `FTP_PASSWORD` | Password vPanel akun Anda |

---

## Langkah 3: Buat File Konfigurasi GitHub Actions

Di dalam project lokal Anda, buat file baru:
`.github/workflows/deploy.yml`

Isi file tersebut dengan script berikut:

```yaml
name: Deploy to InfinityFree via FTP

on:
  push:
    branches:
      - main # atau master, sesuaikan dengan branch utama Anda

jobs:
  web-deploy:
    name: 🎉 Sync to InfinityFree
    runs-on: ubuntu-latest
    steps:
      - name: 🚚 Ambil Kode Terkini (Checkout)
        uses: actions/checkout@v4
        with:
          fetch-depth: 2

      - name: 📂 Upload File yang Berubah via FTP
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.FTP_SERVER }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          server-dir: /core/ # Direktori tujuan di hosting
          exclude: |
            **/.git*
            **/.git*/**
            **/node_modules/**
            public/**
            .env
```

---

## Langkah 4: Cara Kerja Update Selanjutnya

Setiap kali Anda selesai mengubah kodingan di komputer lokal (misal mengedit Controller atau Blade view):
1. Anda cukup menjalankan:
   ```bash
   git add .
   git commit -m "update fitur X"
   git push origin main
   ```
2. GitHub Actions otomatis berjalan di background dan mengunggah hanya file yang berubah ke InfinityFree.
3. Anda bisa memantau status upload di tab **Actions** pada repository GitHub Anda.
