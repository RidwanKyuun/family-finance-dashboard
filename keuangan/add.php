<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis      = $_POST['jenis'];
    $jumlah     = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'] ?: null;
    $kategori   = $_POST['kategori'] ?: 'Umum';
    $tanggal    = $_POST['tanggal'];
    $bulan_kembali = $_POST['bulan_kembali'] ?? date('Y-m');

    if (!in_array($jenis, ['masuk', 'keluar']) || $jumlah <= 0) {
        $_SESSION['pesan'] = 'Data tidak valid.';
        $_SESSION['tipe']  = 'danger';
        header("Location: index.php?bulan=$bulan_kembali");
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO transaksi (jenis, jumlah, keterangan, kategori, tanggal) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$jenis, $jumlah, $keterangan, $kategori, $tanggal]);

    $_SESSION['pesan'] = 'Transaksi berhasil ditambahkan.';
    $_SESSION['tipe']  = 'success';
    header("Location: index.php?bulan=$bulan_kembali");
    exit;
} else {
    header("Location: index.php");
    exit;
}
