# Panduan Membuka FTP Menggunakan Windows File Explorer

Anda bisa membuka dan mengelola file server hosting InfinityFree langsung dari **File Explorer bawaan Windows** (tanpa perlu install aplikasi pihak ketiga seperti FileZilla).

---

## ⚡ Cara Cepat (Lewat Address Bar File Explorer)

1. Tekan tombol **`Windows + E`** di keyboard untuk membuka **File Explorer**.
2. Klik pada **Address Bar** (kotak alamat folder di bagian atas) dan ketik:
   ```text
   ftp://ftpupload.net
   ```
   lalu tekan **Enter**.

3. Akan muncul jendela popup login (**Log On As**):
   * **User name**: `if0_42784458`
   * **Password**: `Ngawut123456789`
   * Centang **Save password** (agar tidak perlu ketik ulang nanti).
   * Klik tombol **Log On**.

4. Folder server hosting akan terbuka seperti folder biasa di komputer Anda. Anda bisa langsung copy-paste file/folder (seperti folder `vendor/`) ke folder `/core/`.

---

## 📌 Cara Permanen (Menambahkan Network Location ke "This PC")

Agar folder FTP tersimpan permanen di menu **This PC** laptop Anda:

1. Buka **File Explorer** (`Win + E`) -> Klik **This PC** di panel kiri.
2. Klik kanan di area kosong -> Pilih **Add a network location** (*Tambahkan lokasi jaringan*).
3. Klik **Next** -> Pilih **Choose a custom network location** -> Klik **Next**.
4. Di kolom *Internet or network address*, ketik:
   ```text
   ftp://ftpupload.net
   ```
   lalu klik **Next**.
5. Hilangkan centang pada opsi *Log on anonymously*, lalu isi:
   * **User name**: `if0_42784458`
   * Klik **Next**.
6. Beri nama shortcut ini, misalnya: `Hosting InfinityFree` -> Klik **Next** -> Klik **Finish**.
7. Masukkan password `Ngawut123456789` dan centang **Save password** -> Klik **Log On**.
