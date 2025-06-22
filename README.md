# 🛍️ TOKO THRIFTING SUCI - Sistem Informasi E-Commerce Baju Thrift Berbasis Web

**Temukan Gaya Unik Anda. Berbelanja Cerdas, Berdampak Positif!** ✨

![image](https://github.com/user-attachments/assets/aea2e767-62eb-4ff2-bba7-d943d5c7d2a9)


Selamat datang di repositori proyek **TOKO THRIFTING SUCI**! Ini adalah sistem informasi e-commerce berbasis web yang didedikasikan untuk jual beli baju thrift (bekas layak pakai) secara online. Dirancang untuk memberikan pengalaman berbelanja yang mudah dan intuitif, sekaligus mendukung keberlanjutan. Proyek ini dikembangkan menggunakan PHP Native dan database MySQL, menjadikannya fondasi yang solid dan mudah dipelajari untuk pengembangan lebih lanjut.

## 📋 Daftar Isi

* [Tentang Proyek](#tentang-proyek)
* [Fitur Utama](#fitur-utama)
* [Teknologi Digunakan](#teknologi-digunakan)
* [Prasyarat (Yang Perlu Anda Siapkan)](#prasyarat-yang-perlu-anda-siapkan)
* [Panduan Instalasi & Menjalankan Aplikasi (Lokal)](#panduan-instalasi--menjalankan-aplikasi-lokal)
    * [1. Kloning Repositori](#1-kloning-repositori)
    * [2. Pengaturan Web Server (XAMPP)](#2-pengaturan-web-server-xampp)
    * [3. Pengaturan Database](#3-pengaturan-database)
    * [4. Instalasi Pendukung PHP (Composer)](#4-instalasi-pendukung-php-composer)
    * [5. Konfigurasi Aplikasi](#5-konfigurasi-aplikasi)
    * [6. Siapkan Gambar Produk](#6-siapkan-gambar-produk)
    * [7. Akses Aplikasi](#7-akses-aplikasi)
* [🔒 Akses Admin](#akses-admin)
* [📂 Struktur Proyek](#struktur-proyek)
* [📄 Lisensi](#lisensi)
* [📧 Kontak](#kontak)

---

## 💡 Tentang Proyek

**TOKO THRIFTING SUCI** adalah solusi e-commerce yang memungkinkan Anda untuk:
* Menjelajahi berbagai koleksi baju thrift berkualitas.
* Melakukan pembelian dengan proses yang cepat dan aman.
* Mengelola inventaris dan pesanan secara efisien melalui panel admin.

Proyek ini tidak hanya berfokus pada fungsionalitas, tetapi juga pada kemudahan pemahaman dan pengembangan, menjadikannya pilihan yang tepat untuk studi dan praktik pengembangan web.

## 🚀 Fitur Utama

### Untuk Pengguna (Pembeli)
* ✨ **Daftar & Masuk Akun**: Buat profil Anda dan akses toko.
* 🔑 **Lupa & Atur Ulang Kata Sandi**: Amankan akun Anda dengan mudah jika lupa password.
* 👕 **Jelajahi Produk**: Lihat koleksi lengkap baju thrift yang tersedia.
* 🔍 **Cari & Filter Produk**: Temukan item yang Anda inginkan dengan cepat berdasarkan nama atau kategori.
* 🖼️ **Lihat Detail Produk**: Dapatkan informasi lengkap termasuk deskripsi, harga, dan ketersediaan stok.
* 🛒 **Keranjang Belanja Interaktif**: Tambah, atur jumlah, atau hapus produk dari keranjang Anda.
* 💳 **Proses Checkout Mudah**: Selesaikan pembelian dengan langkah-langkah yang jelas.
* 📜 **Riwayat Pesanan**: Lacak semua pesanan Anda sebelumnya.

### Untuk Admin
* ⚙️ **Login Admin**: Akses panel manajemen toko yang powerful.
* 📊 **Dashboard Analitik**: Dapatkan gambaran cepat tentang statistik toko (produk, pesanan, pelanggan).
* 📦 **Manajemen Produk Lengkap**: Tambah, edit, dan hapus produk, termasuk upload gambar.
    * *Saran GIF*: GIF singkat menunjukkan proses tambah/edit produk di panel admin.
* 📦 **Manajemen Pesanan Efisien**: Lihat semua pesanan pelanggan dan perbarui statusnya (misal: dari 'Pending' menjadi 'Terkirim').
    * *Saran GIF*: GIF singkat menunjukkan proses perubahan status pesanan.
* 👥 **Manajemen Pengguna**: Tinjau daftar lengkap semua pengguna terdaftar.

## 🛠️ Teknologi Digunakan

Proyek ini dibangun dengan teknologi standar web yang kokoh dan mudah dipahami:
* **Bahasa Pemrograman**: PHP 🐘
* **Database**: MySQL/MariaDB (dikelola melalui phpMyAdmin) 🗄️
* **Web Server**: Apache (bagian dari XAMPP) 🌐
* **Front-end**: HTML5, CSS3 (Native), JavaScript (Native) 🎨
* **Pengelola Pustaka PHP**: Composer (untuk instalasi PHPMailer) 🎼
* **Pengiriman Email**: PHPMailer ✉️

## 🚦 Prasyarat (Yang Perlu Anda Siapkan)

Sebelum dapat menjalankan proyek ini secara lokal, pastikan Anda memiliki perangkat lunak berikut terinstal di komputer Anda:

1.  **XAMPP (atau WAMP/LAMP)**: Ini adalah "server instan" yang menyediakan Apache (web server), MySQL (database), dan PHP. 🖥️
    * Unduh dari: [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
    * Ikuti panduan instalasi standar.
2.  **Composer**: Alat untuk mengelola dependensi PHP. 📦
    * Unduh dari: [https://getcomposer.org/download/](https://getcomposer.org/download/)
    * Jalankan file instalasi dan ikuti panduan di situs web mereka.
3.  **Git**: Program untuk mengelola kode dan terhubung dengan GitHub. 🌳
    * Unduh dari: [https://git-scm.com/downloads](https://git-scm.com/downloads)
    * Ikuti petunjuk instalasi standar.

## 🚀 Panduan Instalasi & Menjalankan Aplikasi (Lokal)

Ikuti langkah-langkah di bawah ini untuk mengatur dan menjalankan proyek di komputer Anda:

### 1. Kloning Repositori

1.  Buka **Command Prompt (CMD)** di Windows.
2.  Navigasi ke folder `htdocs` dari instalasi XAMPP Anda. Contohnya:
    ```bash
    E:
    cd \XAMPP\htdocs
    ```
    (Ganti `E:` jika XAMPP Anda terinstal di drive lain.)
3.  Kloning (salin) kode proyek ini dari GitHub ke folder `htdocs` Anda:
    ```bash
    git clone [https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git](https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git) TOKO_THRIFTING_SUCI
    ```
    **Penting**: Ganti `YOUR_USERNAME` dengan username GitHub Anda, dan `YOUR_REPO_NAME` dengan nama repositori yang Anda buat di GitHub (contohnya `toko-thrifting-suci-web`).

Sekarang Anda akan memiliki folder `TOKO_THRIFTING_SUCI` di dalam `htdocs`.

### 2. Pengaturan Web Server (XAMPP)

1.  Buka **XAMPP Control Panel** (Anda bisa mencarinya di menu Start).
2.  Pada baris **Apache** dan **MySQL**, klik tombol **"Start"** untuk memulai kedua modul tersebut. ▶️
3.  Pastikan status keduanya menjadi `Running` (biasanya berwarna hijau). ✅

### 3. Pengaturan Database

1.  Buka browser web Anda dan ketik alamat: `http://localhost/phpmyadmin/` 🌐
2.  Di halaman phpMyAdmin, di sisi kiri, klik tab **"Databases"** (atau "Basis Data").
3.  Di bagian "Create database", Anda bisa membuat database baru dengan nama: `toko_thrifting_suci`. Lalu klik "Create".
4.  Setelah database `toko_thrifting_suci` terbuat, klik namanya di daftar database di sisi kiri.
5.  Di bagian atas, klik tab **"SQL"**.
6.  Buka file bernama `database.sql` yang ada di dalam folder proyek `TOKO_THRIFTING_SUCI/` Anda (gunakan editor teks seperti Notepad atau VS Code).
7.  **Salin SELURUH isi** dari file `database.sql`.
8.  Tempelkan semua kode SQL tersebut ke kotak teks besar di halaman phpMyAdmin.
9.  Klik tombol **"Go"** (atau "Kirim") di bagian kanan bawah. ▶️

Ini akan otomatis membuat semua tabel yang dibutuhkan (`users`, `products`, `categories`, `orders`, `order_items`, `password_resets`) dan mengisi beberapa data contoh agar Anda bisa langsung mencoba.

### 4. Instalasi Pendukung PHP (Composer)

Proyek ini menggunakan pustaka PHPMailer untuk fitur pengiriman email (misalnya untuk lupa password). Pustaka ini diinstal menggunakan Composer.

1.  Buka **Command Prompt (CMD)** Anda.
2.  Navigasikan ke folder utama proyek Anda:
    ```bash
    E:
    cd \XAMPP\htdocs\TOKO_THRIFTING_SUCI
    ```
3.  Jalankan perintah ini untuk menginstal pustaka yang diperlukan:
    ```bash
    composer install
    ```
    Ini akan membuat folder `vendor/` di dalam proyek Anda dan mengunduh file-file PHPMailer ke dalamnya. 📦

### 5. Konfigurasi Aplikasi

Ada dua file pengaturan penting yang perlu Anda periksa/edit:

* **`config/database.php`**: Berisi detail koneksi ke database Anda. 🗄️
    * Buka file `TOKO_THRIFTING_SUCI/config/database.php` dengan editor teks.
    * Secara default, pengaturan ini seharusnya sudah cocok dengan XAMPP. Jika Anda mengubah username atau password MySQL `root` Anda, sesuaikan di sini:
        ```php
        <?php
        return [
            'host' => 'localhost',
            'name' => 'toko_thrifting_suci',
            'user' => 'root',
            'password' => '', // Biarkan kosong jika password MySQL Anda kosong
            'charset' => 'utf8mb4'
        ];
        ```
* **`config/mail.php`**: Berisi pengaturan untuk mengirim email (untuk fitur lupa password). ✉️
    * Buka file `TOKO_THRIFTING_SUCI/config/mail.php`.
    * **Ini bagian yang SANGAT PENTING**: Anda **HARUS** mengganti `your_valid_email@gmail.com` dan `YOUR_APP_PASSWORD` dengan detail akun email SMTP Anda yang valid.
    * **Untuk pengguna Gmail**: Jika Anda ingin menggunakan akun Gmail untuk mengirim email, Anda mungkin perlu mengaktifkan **Verifikasi 2 Langkah** (2FA) di akun Google Anda terlebih dahulu. Setelah itu, buat **"App password"** dari pengaturan keamanan akun Google Anda (cari di bagian "Keamanan" -> "Cara Anda login ke Google" -> "Kata sandi aplikasi"). Gunakan kata sandi 16 karakter yang dihasilkan ini sebagai `password` di `mail.php`. **Jangan gunakan password akun Gmail utama Anda secara langsung!**
        ```php
        <?php
        return [
            'host' => 'smtp.gmail.com', // Contoh untuk Gmail
            'port' => 587,              // Port standar untuk TLS
            'username' => 'your_valid_email@gmail.com', // Ganti dengan email Anda
            'password' => 'YOUR_APP_PASSWORD', // Ganti dengan App Password Anda
            'smtp_secure' => 'tls',
            'smtp_auth' => true,
            'from_email' => 'your_valid_email@gmail.com',
            'from_name' => 'TOKO THRIFTING SUCI',
        ];
        ```

### 6. Siapkan Gambar Produk

Aplikasi ini akan mencari gambar produk dan gambar lainnya di folder `assets/images/`.

* Di dalam folder `TOKO_THRIFTING_SUCI/assets/images/`, buat folder baru bernama **`products`**.
* Letakkan gambar-gambar produk Anda (sesuai dengan nama file di `database.sql`, contoh: `kemeja_flanel.jpg`) di dalam folder `TOKO_THRIFTING_SUCI/assets/images/products/`.
* Untuk gambar latar belakang halaman utama (`hero_bg.jpg`) dan gambar pengganti (`product_placeholder.jpg`) jika produk tidak memiliki gambar, letakkan di folder `TOKO_THRIFTING_SUCI/assets/images/` (bukan di subfolder `products`). Anda bisa menggunakan gambar apa saja yang relevan.

### 7. Akses Aplikasi

Setelah semua langkah di atas selesai, buka browser web favorit Anda dan ketikkan alamat berikut:
`http://localhost/TOKO_THRIFTING_SUCI/`
Selamat! ✨ Anda akan melihat halaman utama "TOKO THRIFTING SUCI" dan seharusnya ada pesan "Koneksi Database Berhasil!" serta daftar produk dummy yang sudah tampil.

---

## 🔒 Akses Admin

Untuk masuk ke Panel Admin dan mengelola toko, Anda bisa menggunakan akun dummy yang sudah ada di database:

* **Username / Email**: `admin@suci.com`
* **Password**: `password123`

Setelah berhasil login, Anda akan otomatis diarahkan ke Dashboard Admin.

---

## 📂 Struktur Proyek

Proyek ini memiliki struktur folder yang rapi untuk memisahkan logika program, tampilan visual, dan aset-aset lainnya. Ini adalah gambaran singkatnya:
```text
TOKO_THRIFTING_SUCI/
├── admin/                     # Folder khusus untuk halaman dan fitur panel admin
│   ├── includes/              # File-file pembangun halaman admin (header, footer, sidebar)
│   ├── dashboard.php          # Halaman utama dashboard admin
│   ├── orders.php             # Halaman manajemen pesanan
│   ├── products.php           # Halaman daftar produk di admin
│   └── users.php              # Halaman daftar pengguna
├── assets/                    # Berisi semua aset statis (CSS, JavaScript, Gambar)
│   ├── css/
│   ├── images/                # Gambar umum dan folder 'products' untuk gambar barang
│   └── js/
├── config/                    # File konfigurasi aplikasi (database, email)
│   ├── database.php
│   └── mail.php
├── includes/                  # Kumpulan fungsi dan potongan kode yang digunakan di banyak halaman
│   ├── functions.php
│   ├── footer.php
│   └── header.php
├── vendor/                    # Pustaka PHP dari Composer (misal: PHPMailer)
├── .gitignore                 # Aturan file/folder yang tidak diunggah ke GitHub
├── .htaccess                  # Aturan konfigurasi server Apache
├── cart.php                   # Halaman keranjang belanja pembeli
├── checkout.php               # Halaman proses penyelesaian pesanan
├── database.sql               # File SQL untuk membuat database dan tabel
├── forgot_password.php        # Halaman untuk permintaan reset password
├── index.php                  # Halaman utama website
├── login.php                  # Halaman masuk akun
├── logout.php                 # Proses keluar akun
├── my_orders.php              # Halaman riwayat pesanan pengguna
├── products.php               # Halaman untuk melihat semua produk yang dijual
├── product_detail.php         # Halaman untuk melihat detail satu produk
├── register.php               # Halaman pendaftaran akun baru
└── reset_password.php         # Halaman untuk mengatur ulang password

```

## 📄 Lisensi

Proyek ini dirilis di bawah [Lisensi MIT](https://opensource.org/licenses/MIT). Anda bebas menggunakan, memodifikasi, dan mendistribusikannya.

---

## 📧 Kontak

Jika Anda memiliki pertanyaan, saran, atau ingin berkolaborasi, jangan ragu untuk menghubungi:

* **Nama**: Abbeey
* **Email**: [doelsumbing87@gmail.com](mailto:doelsumbing87@gmail.com)
* **GitHub**: [https://github.com/doelsumbing87](https://github.com/doelsumbing87)

---
