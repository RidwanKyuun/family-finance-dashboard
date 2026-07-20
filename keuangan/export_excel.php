<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db.php';

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$bulan_awal  = $bulan . '-01';
$bulan_akhir = date('Y-m-t', strtotime($bulan_awal));

// ===== DATA TRANSAKSI BULAN TERPILIH =====
$stmt = $pdo->prepare("
    SELECT tanggal, jenis, kategori, keterangan, jumlah 
    FROM transaksi 
    WHERE tanggal BETWEEN ? AND ?
    ORDER BY tanggal ASC, id ASC
");
$stmt->execute([$bulan_awal, $bulan_akhir]);
$transaksi = $stmt->fetchAll();

// ===== RINGKASAN BULAN TERPILIH =====
$stmt_sum = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN jenis='masuk' THEN jumlah ELSE 0 END),0) AS total_masuk,
        COALESCE(SUM(CASE WHEN jenis='keluar' THEN jumlah ELSE 0 END),0) AS total_keluar
    FROM transaksi
    WHERE tanggal BETWEEN ? AND ?
");
$stmt_sum->execute([$bulan_awal, $bulan_akhir]);
$sum = $stmt_sum->fetch();
$saldo_bulan = $sum['total_masuk'] - $sum['total_keluar'];

// ===== SALDO TOTAL SEMUA WAKTU (untuk ditampilkan di ringkasan) =====
$stmt_total = $pdo->query("
    SELECT 
        COALESCE(SUM(CASE WHEN jenis='masuk' THEN jumlah ELSE 0 END),0) AS total_masuk_all,
        COALESCE(SUM(CASE WHEN jenis='keluar' THEN jumlah ELSE 0 END),0) AS total_keluar_all
    FROM transaksi
");
$total_all = $stmt_total->fetch();
$saldo_total = $total_all['total_masuk_all'] - $total_all['total_keluar_all'];

$filename = "Keuangan_Keluarga_" . date('F_Y', strtotime($bulan_awal)) . ".xls";

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

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

// ===== RINGKASAN DENGAN SALDO TOTAL SEMUA WAKTU =====
echo '<table border="1" cellpadding="5" cellspacing="0">';
echo '<tr><td colspan="2" class="judul">RINGKASAN KEUANGAN BULAN ' . strtoupper(date('F Y', strtotime($bulan_awal))) . '</td></tr>';
echo '<tr><td><strong>Total Pemasukan (Bulan Ini)</strong></td><td class="right green">Rp ' . number_format($sum['total_masuk'], 2, ',', '.') . '</td></tr>';
echo '<tr><td><strong>Total Pengeluaran (Bulan Ini)</strong></td><td class="right red">Rp ' . number_format($sum['total_keluar'], 2, ',', '.') . '</td></tr>';
echo '<tr><td><strong>Selisih Bulan Ini</strong></td><td class="right ' . ($saldo_bulan >= 0 ? 'green' : 'red') . '">Rp ' . number_format($saldo_bulan, 2, ',', '.') . '</td></tr>';
echo '<tr style="border-top: 2px solid #000;"><td><strong>SALDO TOTAL (Akumulasi Semua Waktu)</strong></td><td class="right ' . ($saldo_total >= 0 ? 'green' : 'red') . '" style="font-size:12pt; font-weight:bold;">Rp ' . number_format($saldo_total, 2, ',', '.') . '</td></tr>';
echo '</table>';
echo '<br>';

// ===== DAFTAR TRANSAKSI =====
echo '<table border="1" cellpadding="5" cellspacing="0">';
echo '<tr><td colspan="6" class="judul">DAFTAR TRANSAKSI BULAN ' . strtoupper(date('F Y', strtotime($bulan_awal))) . '</td></tr>';
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
?>