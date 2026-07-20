<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db.php';

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$bulan_awal  = $bulan . '-01';
$bulan_akhir = date('Y-m-t', strtotime($bulan_awal));

// Ambil data transaksi
$stmt = $pdo->prepare("
    SELECT tanggal, jenis, kategori, keterangan, jumlah 
    FROM transaksi 
    WHERE tanggal BETWEEN ? AND ?
    ORDER BY tanggal ASC, id ASC
");
$stmt->execute([$bulan_awal, $bulan_akhir]);
$transaksi = $stmt->fetchAll();

// Ringkasan
$stmt_sum = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN jenis='masuk' THEN jumlah ELSE 0 END),0) AS total_masuk,
        COALESCE(SUM(CASE WHEN jenis='keluar' THEN jumlah ELSE 0 END),0) AS total_keluar
    FROM transaksi
    WHERE tanggal BETWEEN ? AND ?
");
$stmt_sum->execute([$bulan_awal, $bulan_akhir]);
$sum = $stmt_sum->fetch();
$saldo = $sum['total_masuk'] - $sum['total_keluar'];

$filename = "Keuangan_Keluarga_" . date('F_Y', strtotime($bulan_awal)) . ".xls";

// Header untuk file Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Output sebagai tabel HTML (format yang dibaca Excel)
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta charset="UTF-8">';
echo '<style>
    table { border-collapse: collapse; }
    th, td { padding: 8px; border: 1px solid #999; }
    th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
    .wrap { white-space: normal; word-wrap: break-word; }
    .right { text-align: right; }
    .center { text-align: center; }
    .green { color: green; font-weight: bold; }
    .red { color: red; font-weight: bold; }
    .judul { font-size: 14pt; font-weight: bold; text-align: center; background-color: #e0e0e0; }
</style>';
echo '</head><body>';

// Ringkasan dengan merge cells
echo '<table border="1" cellpadding="5" cellspacing="0">';
echo '<tr><td colspan="2" class="judul">RINGKASAN KEUANGAN BULAN ' . strtoupper(date('F Y', strtotime($bulan_awal))) . '</td></tr>';
echo '<tr><td><strong>Total Pemasukan</strong></td><td class="right green">Rp ' . number_format($sum['total_masuk'], 2, ',', '.') . '</td></tr>';
echo '<tr><td><strong>Total Pengeluaran</strong></td><td class="right red">Rp ' . number_format($sum['total_keluar'], 2, ',', '.') . '</td></tr>';
echo '<tr><td><strong>Saldo</strong></td><td class="right ' . ($saldo >= 0 ? 'green' : 'red') . '">Rp ' . number_format($saldo, 2, ',', '.') . '</td></tr>';
echo '</table>';
echo '<br>';

// Tabel daftar transaksi
echo '<table border="1" cellpadding="5" cellspacing="0">';
echo '<tr><td colspan="6" class="judul">DAFTAR TRANSAKSI</td></tr>';
echo '<tr>';
echo '<th>No</th><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th>Jumlah (Rp)</th>';
echo '</tr>';

$no = 1;
foreach ($transaksi as $row) {
    $jenis_label = ($row['jenis'] == 'masuk') ? 'Pemasukan' : 'Pengeluaran';
    $color_class = ($row['jenis'] == 'masuk') ? 'green' : 'red';
    echo '<tr>';
    echo '<td class="center">' . $no++ . '</td>';
    echo '<td class="center">' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>';
    echo '<td class="center ' . $color_class . '">' . $jenis_label . '</td>';
    echo '<td>' . htmlspecialchars($row['kategori']) . '</td>';
    echo '<td class="wrap">' . htmlspecialchars($row['keterangan'] ?? '') . '</td>';
    echo '<td class="right">' . number_format($row['jumlah'], 2, ',', '.') . '</td>';
    echo '</tr>';
}
echo '</table>';
echo '</body></html>';
exit;