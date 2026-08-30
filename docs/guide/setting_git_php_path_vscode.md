# Panduan Menambahkan Git & PHP Laragon ke Terminal VS Code (Permanen)

Panduan ini menjelaskan cara mengaktifkan perintah `git`, `php`, dan `composer` secara otomatis dan permanen di Terminal VS Code.

---

## Cara 1: Menggunakan 1 Baris Perintah PowerShell (Paling Cepat & Praktis) ⭐

1. Buka terminal di VS Code (atau PowerShell).
2. Salin dan jalankan perintah berikut:

```powershell
[Environment]::SetEnvironmentVariable("Path", [Environment]::GetEnvironmentVariable("Path", "User") + ";C:\laragon\bin\git\cmd;C:\laragon\bin\git\bin;C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64;C:\laragon\bin\composer", "User")
```

3. **Tutup VS Code, lalu buka kembali VS Code** (atau buka terminal baru).
4. Selesai! Sekarang ketik `git --version`, `php -v`, atau `composer -V` di terminal, perintah akan langsung terbaca.

---

## Cara 2: Melalui Pengaturan Settings VS Code (`settings.json`)

1. Di VS Code, tekan tombol `Ctrl + Shift + P`.
2. Ketik **`Preferences: Open User Settings (JSON)`**, lalu tekan Enter.
3. Tambahkan konfigurasi berikut di dalam kurung kurawal `{ ... }`:

```json
"terminal.integrated.env.windows": {
    "PATH": "C:\\laragon\\bin\\git\\cmd;C:\\laragon\\bin\\git\\bin;C:\\laragon\\bin\\php\\php-8.3.33-Win32-vs16-x64;C:\\laragon\\bin\\composer;${env:PATH}"
}
```

4. Simpan file (`Ctrl + S`), lalu buka terminal baru di VS Code (`Ctrl + ~`).

---

## Cara 3: Melalui Menu Windows Environment Variables (GUI)

1. Tekan tombol `Windows + R`, ketik `sysdm.cpl` lalu tekan Enter.
2. Buka tab **Advanced** -> klik tombol **Environment Variables...** di kanan bawah.
3. Pada kotak **User variables for [User]**, pilih baris **Path**, lalu klik **Edit...**
4. Klik tombol **New**, lalu masukkan:
   - `C:\laragon\bin\git\cmd`
   - `C:\laragon\bin\git\bin`
   - `C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64`
   - `C:\laragon\bin\composer`
5. Klik **OK** -> **OK** -> **Apply**.
6. **Restart VS Code** agar perubahan terbaca.
