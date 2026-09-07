# 📇 Aplikasi ID Card / Passport Sederhana (PHP + Bootstrap + MySQL)

Aplikasi sederhana untuk membuat ID Card digital berbasis web, responsive,
dark mode, dan didesain khusus agar **muat dalam satu layar HP (portrait)
tanpa perlu scroll**.

## ✨ Fitur

- Input biodata: **Nama, Kelas, Absen, JK (Jenis Kelamin), Nomer HP**
- Setelah "difoto", otomatis menampilkan **ID Card / Passport** dalam mode
  portrait, dark mode, satu kartu (card) penuh tanpa header/footer.
- Menampilkan info **AWS Environment** di bagian bawah kartu: EC2 Instance
  ID, EC2 Public IP, RDS Endpoint, Region/AZ. Data ini **diambil langsung
  dari AWS EC2 Instance Metadata Service (IMDSv2)** jika aplikasi memang
  dijalankan di EC2 (mis. AWS Academy Lab). Jika tidak terdeteksi berjalan
  di EC2 (mis. testing di localhost/laptop), nilai yang tidak tersedia akan
  ditampilkan sebagai **`-`** (tidak ada data dummy/palsu yang dipaksakan),
  dan kartu akan menampilkan badge **LIVE** (data asli) atau **N/A**
  (metadata tidak terdeteksi).
- Foto pada ID Card ditampilkan **besar dan sesuai rasio asli gambar**
  (tidak dicrop paksa ke rasio tertentu).
- **Database, tabel, dan struktur tabel dibuat otomatis** oleh aplikasi
  saat pertama kali dijalankan (tidak perlu import SQL manual).
- Tombol **"Buat Ulang"** untuk kembali ke form dan input data baru.

## 📁 Struktur File

```
idcard-app/
├── konfig.php        # Semua konfigurasi (DB & info AWS dummy) + auto-create DB/table
├── index.php         # Halaman utama (single page app: form → kamera → kartu)
├── save.php          # Endpoint AJAX: simpan biodata ke DB, return JSON
├── css/
│   └── style.css     # Styling dark mode & layout fit 1 layar HP
├── js/
│   └── app.js        # Logika kamera & render kartu
├── img/
└── README.md
```

## ⚙️ Instalasi & Menjalankan

### 1. Persyaratan
- PHP 7.4+ dengan ekstensi `mysqli`
- MySQL / MariaDB Server
- Web server: Apache2

### 2. Setup

1. Extract file zip ke folder web server Anda, misalnya:
   ```
   /var/www/html/idcard-app/
   ```
2. Buka file **`konfig.php`**, sesuaikan kredensial database:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_PORT', '3306');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'db_idcard');
   define('DB_TABLE', 'biodata');
   ```
   > Anda **tidak perlu** membuat database/tabel secara manual — aplikasi
   > akan membuatnya otomatis saat pertama kali diakses/menyimpan data.

3. (Opsional) Sesuaikan info AWS dummy di `konfig.php` jika ingin
   menampilkan info environment server Anda yang sebenarnya:
   ```php
   define('AWS_EC2_INSTANCE_ID', 'i-0a1b2c3d4e5f6g7h8');
   define('AWS_EC2_PUBLIC_IP', '13.212.45.101');
   define('AWS_RDS_ENDPOINT', 'db-idcard.xxxxx.rds.amazonaws.com');
   ...
   ```

4. Akses dari browser HP/desktop yang satu jaringan:
   ```
   http://<IP-KOMPUTER-ANDA>:8000
   ```

   Atau taruh di folder `htdocs` (XAMPP/Laragon) lalu akses via:
   ```
   http://localhost/idcard-app
   ```

### 3. Cara Pakai

1. Isi form biodata (Nama, Kelas, Absen, JK, Nomer HP).
2. Tekan tombol **📷 Ambil Foto** 
3. Setelah selesai, data otomatis tersimpan ke database dan tampilan
   berpindah ke **ID Card** lengkap dengan foto (sesuai JK) dan info
   AWS Environment di bagian bawah kartu.
4. Tekan **🔄 Buat Ulang** untuk kembali ke form dan input data baru.

## ⚠️ Catatan Penting

- Info AWS (EC2 Instance ID, IP, Region/AZ) **otomatis diambil dari data
  ASLI** melalui **AWS EC2 Instance Metadata Service versi 2 (IMDSv2)**
  — lihat fungsi `getAwsInfo()` di `konfig.php`. Ini akan berfungsi
  otomatis jika aplikasi dijalankan di instance EC2 (termasuk EC2 dari
  AWS Academy Lab), tanpa perlu konfigurasi tambahan.
  - **RDS Endpoint** yang ditampilkan adalah `DB_HOST` sungguhan dari
    `konfig.php` (bukan dummy) — yaitu endpoint database yang benar-benar
    dipakai aplikasi untuk konek.
  - Jika dijalankan **bukan di EC2** (mis. di localhost/laptop biasa),
    request ke metadata service (`169.254.169.254`) akan gagal/timeout
    (percobaan dibatasi ±0.4 detik agar tidak lama), dan field yang tidak
    berhasil dibaca akan ditampilkan sebagai **`-`** (bukan nilai
    dummy/palsu). Badge pada kartu akan menunjukkan **LIVE** (data asli)
    atau **N/A** (metadata tidak terdeteksi) sesuai kondisi ini.
  - Tidak ada lagi konstanta AWS dummy di `konfig.php` — semua nilai AWS
    murni hasil pembacaan metadata real-time.

## 🗄️ Struktur Tabel `biodata` (dibuat otomatis)

| Kolom      | Tipe                          | Keterangan            |
|------------|-------------------------------|------------------------|
| id         | INT AUTO_INCREMENT PRIMARY KEY | ID unik               |
| nama       | VARCHAR(100)                  | Nama lengkap          |
| kelas      | VARCHAR(50)                   | Kelas                 |
| absen      | VARCHAR(10)                   | Nomor absen           |
| jk         | ENUM('Laki-Laki','Perempuan') | Jenis kelamin         |
| no_hp      | VARCHAR(20)                   | Nomor HP              |
| foto_path  | VARCHAR(255)                  | Path foto             |
| created_at | TIMESTAMP                     | Waktu data dibuat     |

## 🛠️ Teknologi

- PHP (native, `mysqli`)
- Bootstrap 5 (CDN)
- MySQL / MariaDB
- Vanilla JavaScript (Fetch API untuk AJAX)
- Dark mode custom CSS, layout `100dvh` agar pas 1 layar tanpa scroll

## 📄 Lisensi

Bebas digunakan dan dimodifikasi untuk keperluan pembelajaran/internal.
