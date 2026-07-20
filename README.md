# family-finance-dashboard
Manage your household finances with this sleek dashboard. Add income/expense transactions, filter by month, view category-wise comparisons, and export reports to Excel. Built with PHP, MySQL, Bootstrap 5, featuring a modern glassmorphism dark theme.
---

```markdown
# 🏦 Family Finance Dashboard

**Family Finance Dashboard** adalah aplikasi web berbasis PHP dan MySQL untuk mencatat dan memantau keuangan keluarga secara praktis. Dengan antarmuka modern bergaya *glassmorphism* dan navigasi tab yang intuitif, Anda dapat mengelola pemasukan, pengeluaran, serta melihat selisih per kategori dengan mudah.

> 📌 **Versi** – 1.0.0  
> 📅 **Status** – Stable

---

## ✨ Fitur Unggulan

| Fitur | Deskripsi |
|-------|-----------|
| **Ringkasan Keuangan** | Tampilan cepat total pemasukan, pengeluaran, dan saldo bersih per bulan. |
| **Filter Bulanan** | Pilih bulan/tahun tertentu untuk melihat data historis transaksi. |
| **Navigasi Tab** | Pisahkan tampilan transaksi: Semua, Pemasukan, Pengeluaran, dan Selisih. |
| **Tambah Transaksi Cepat** | Formulir dengan validasi sederhana untuk memasukkan data baru. |
| **Ekspor ke Excel** | Unduh laporan bulanan dalam format `.xls` (kompatibel dengan Microsoft Excel). |
| **Desain Responsif & Modern** | Menggunakan Bootstrap 5 dengan efek *glassmorphism*, gradien gelap, dan animasi halus. |

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Fungsi |
|-----------|--------|
| **PHP** (v7.4+) | Logika backend dan koneksi database (PDO) |
| **MySQL** (v5.7+) | Database penyimpanan transaksi |
| **Bootstrap 5** | Layout dan komponen UI responsif |
| **CSS3** | Kustomisasi tampilan (gradien, efek kaca, animasi) |
| **JavaScript** | Interaksi dasar (konfirmasi hapus, alert dismiss) |

---

## 🗄️ Struktur Database

Aplikasi ini hanya membutuhkan **satu tabel** utama:

```sql
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jenis ENUM('masuk', 'keluar') NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    kategori VARCHAR(100) DEFAULT 'Umum',
    keterangan TEXT DEFAULT NULL,
    tanggal DATE NOT NULL
);
```

> 📌 **Catatan**: Pastikan database bernama `keuangan_keluarga` atau sesuaikan dengan konfigurasi di `db.php`.

---

## 🚀 Cara Instalasi

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di lingkungan lokal:

### 1. Clone Repository
```bash
git clone https://github.com/RidwanKyuun/family-finance-dashboard.git
cd family-finance-dashboard
```

### 2. Pindahkan Folder ke Web Server
- **XAMPP** → pindahkan **folder `keuangan`** ke dalam `htdocs/`
- **Laragon** → pindahkan **folder `keuangan`** ke dalam `www/`
- **LAMP/LNMP** → sesuaikan dengan dokumentasi server Anda

> 📌 **Pastikan**: Folder yang dipindahkan adalah `keuangan`, bukan isi folder-nya.  
> Sehingga path aksesnya menjadi: `http://localhost/keuangan/index.php`

### 3. Buat Database
- Buka **phpMyAdmin** atau klien MySQL favorit Anda.
- Buat database baru, misalnya `keuangan_keluarga`.
- Jalankan query pembuatan tabel di atas.

### 4. Konfigurasi Koneksi
Buka file `db.php` di dalam folder `keuangan` dan sesuaikan parameter berikut:
```php
$host = 'localhost';
$db   = 'keuangan_keluarga';   // nama database Anda
$user = 'root';                // username MySQL
$pass = '';                    // password MySQL
```

### 5. Jalankan Aplikasi
- Buka browser dan akses:  
  `http://localhost/keuangan/index.php`
- Aplikasi siap digunakan.

---

## 📂 Struktur File

Berikut adalah struktur file utama dalam proyek ini:

```
keuangan/
├── add.php            # Proses tambah transaksi
├── db.php             # Konfigurasi koneksi database
├── delete.php         # Proses hapus transaksi
├── export_excel.php   # Ekspor laporan ke Excel
├── index.php          # Halaman utama (dashboard + tab navigasi)
└── README.md          # Dokumentasi proyek
```

---

## 🖥️ Tampilan Aplikasi (Deskripsi Antarmuka)

Aplikasi ini dirancang dengan antarmuka yang bersih dan mudah digunakan. Berikut gambaran setiap bagian utama:

- **Dashboard Saldo** – Menampilkan saldo utama dengan gradien emas, serta tiga kartu ringkasan (Pemasukan, Pengeluaran, Selisih) yang dilengkapi ikon dan animasi hover.
- **Filter Bulanan** – Input bulan di bagian atas dashboard untuk menyaring data berdasarkan bulan tertentu.
- **Form Tambah Transaksi** – Terdiri dari pilihan jenis (Pemasukan/Pengeluaran), input jumlah, kategori, tanggal, dan keterangan opsional. Tombol simpan berwarna emas dengan efek hover.
- **Navigasi Tab** – Empat tab yang memudahkan perpindahan antar tampilan: Semua, Pemasukan, Pengeluaran, dan Selisih.
- **Tabel Transaksi** – Menampilkan daftar transaksi dengan kolom tanggal, jenis (ditandai dengan badge berwarna), kategori, keterangan, jumlah, dan tombol hapus. Tabel dilengkapi dengan total data dan tombol ekspor Excel.
- **Halaman Selisih** – Tabel perbandingan pemasukan dan pengeluaran per kategori, dilengkapi dengan baris total keseluruhan. Nilai selisih positif berwarna hijau, negatif merah.

---

## 🤝 Kontribusi

Kontribusi sangat terbuka! Silakan lakukan *fork*, buat *branch* baru, dan ajukan *pull request*. Untuk saran atau laporan bug, buka *issue* di repository ini.

Langkah kontribusi:
1. *Fork* repository ini.
2. Buat *branch* fitur: `git checkout -b fitur-baru`
3. *Commit* perubahan: `git commit -m 'Tambah fitur x'`
4. *Push* ke *branch*: `git push origin fitur-baru`
5. Ajukan *Pull Request*.

---

## 🙏 Ucapan Terima Kasih

Terima kasih telah menggunakan **Family Finance Dashboard**. Semoga aplikasi ini membantu Anda mengelola keuangan keluarga dengan lebih baik dan terencana.

---

**Dibuat dengan ❤️ oleh [RidwanKyuun](https://github.com/RidwanKyuun)**
```

---

## 🔄 Perubahan yang Saya Lakukan
- **Struktur File** sekarang sesuai dengan folder `keuangan`.
- **Instalasi** diperbaiki: pindahkan folder `keuangan` ke web server.
- **Path akses** menjadi `http://localhost/keuangan/index.php`.
- Bagian `LICENSE` sudah dihilangkan.

Salin konten di atas ke file `README.md` Anda. File ini siap untuk di-commit dan dipush ke repository. 😊
