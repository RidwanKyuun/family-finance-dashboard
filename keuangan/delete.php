<?php
session_start();
require 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $bulan = $_GET['bulan'] ?? date('Y-m');

    $stmt = $pdo->prepare("DELETE FROM transaksi WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['pesan'] = 'Transaksi berhasil dihapus.';
    $_SESSION['tipe']  = 'warning';
    header("Location: index.php?bulan=$bulan");
    exit;
} else {
    header("Location: index.php");
    exit;
}