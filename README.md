# family-finance-dashboard
Manage your household finances with this sleek dashboard. Add income/expense transactions, filter by month, view category-wise comparisons, and export reports to Excel. Built with PHP, MySQL, Bootstrap 5, featuring a modern glassmorphism dark theme.
Baik, saya akan buatkan **README.md** yang profesional tanpa menggunakan gambar. Semua penjelasan cukup dengan deskripsi teks yang informatif dan terstruktur. Berikut file lengkapnya:

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

### 2. Pindahkan ke Web Server
- **XAMPP** → pindahkan folder ke `htdocs/`
- **Laragon** → pindahkan ke `www/`
- **LAMP/LNMP** → sesuaikan dengan dokumentasi server Anda

### 3. Buat Database
- Buka **phpMyAdmin** atau klien MySQL favorit Anda.
- Buat database baru, misalnya `keuangan_keluarga`.
- Jalankan query pembuatan tabel di atas.

### 4. Konfigurasi Koneksi
Buka file `db.php` dan sesuaikan parameter berikut:
```php
$host = 'localhost';
$db   = 'keuangan_keluarga';   // nama database Anda
$user = 'root';                // username MySQL
$pass = '';                    // password MySQL
```

### 5. Jalankan Aplikasi
- Buka browser dan akses:  
  `http://localhost/family-finance-dashboard/index.php`
- Aplikasi siap digunakan.

---

## 📂 Struktur File

Berikut adalah struktur file utama dalam proyek ini:

```
family-finance-dashboard/
├── index.php          # Halaman utama (dashboard + tab navigasi)
├── add.php            # Proses tambah transaksi
├── delete.php         # Proses hapus transaksi
├── export_excel.php   # Ekspor laporan ke Excel
├── db.php             # Konfigurasi koneksi database
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

## 📄 Lisensi

Proyek ini dilisensikan di bawah **MIT License** – lihat file [LICENSE](LICENSE) untuk detail.

---

## 🙏 Ucapan Terima Kasih

Terima kasih telah menggunakan **Family Finance Dashboard**. Semoga aplikasi ini membantu Anda mengelola keuangan keluarga dengan lebih baik dan terencana.

---

**Dibuat dengan ❤️ oleh [RidwanKyuun](https://github.com/RidwanKyuun)**
```

---

## ✨ Perubahan yang Dilakukan
- Bagian **📸 Tampilan Aplikasi** diganti dengan **🖥️ Tampilan Aplikasi (Deskripsi Antarmuka)** yang menjelaskan setiap komponen UI secara rinci tanpa gambar.
- Penjelasan dibuat deskriptif namun tetap profesional, membantu pengguna memahami alur dan fitur aplikasi.
- Semua bagian lain tetap rapi dan informatif.

Salin seluruh konten di atas ke file `README.md` Anda, lalu *commit* dan *push*. Repository Anda akan terlihat sangat profesional! 🚀
