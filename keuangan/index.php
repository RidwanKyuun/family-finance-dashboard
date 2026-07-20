<?php
session_start();
require 'db.php';

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$bulan_awal = $bulan . '-01';
$bulan_akhir = date('Y-m-t', strtotime($bulan_awal));

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

$stmt = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN jenis='masuk' THEN jumlah ELSE 0 END),0) AS total_masuk,
        COALESCE(SUM(CASE WHEN jenis='keluar' THEN jumlah ELSE 0 END),0) AS total_keluar
    FROM transaksi
    WHERE tanggal BETWEEN ? AND ?
");
$stmt->execute([$bulan_awal, $bulan_akhir]);
$ringkasan = $stmt->fetch();
$saldo = $ringkasan['total_masuk'] - $ringkasan['total_keluar'];

if ($tab == 'masuk') {
    $stmt = $pdo->prepare("
        SELECT * FROM transaksi 
        WHERE tanggal BETWEEN ? AND ? AND jenis = 'masuk'
        ORDER BY tanggal DESC, id DESC
    ");
} elseif ($tab == 'keluar') {
    $stmt = $pdo->prepare("
        SELECT * FROM transaksi 
        WHERE tanggal BETWEEN ? AND ? AND jenis = 'keluar'
        ORDER BY tanggal DESC, id DESC
    ");
} elseif ($tab == 'selisih') {
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(kategori, 'Umum') as kategori,
            SUM(CASE WHEN jenis='masuk' THEN jumlah ELSE 0 END) as total_masuk,
            SUM(CASE WHEN jenis='keluar' THEN jumlah ELSE 0 END) as total_keluar,
            SUM(CASE WHEN jenis='masuk' THEN jumlah ELSE -jumlah END) as selisih
        FROM transaksi
        WHERE tanggal BETWEEN ? AND ?
        GROUP BY kategori
        ORDER BY kategori
    ");
    $stmt->execute([$bulan_awal, $bulan_akhir]);
    $selisih_data = $stmt->fetchAll();
    $total_masuk_all = array_sum(array_column($selisih_data, 'total_masuk'));
    $total_keluar_all = array_sum(array_column($selisih_data, 'total_keluar'));
    $selisih_all = $total_masuk_all - $total_keluar_all;
} else {
    $stmt = $pdo->prepare("
        SELECT * FROM transaksi 
        WHERE tanggal BETWEEN ? AND ?
        ORDER BY tanggal DESC, id DESC
    ");
}

if ($tab != 'selisih') {
    $stmt->execute([$bulan_awal, $bulan_akhir]);
    $transaksi = $stmt->fetchAll();
}

$pesan = $_SESSION['pesan'] ?? '';
$tipe  = $_SESSION['tipe'] ?? '';
unset($_SESSION['pesan'], $_SESSION['tipe']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuangan Keluarga — Bank Keluarga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(145deg, #0b0e1a 0%, #1a1f35 100%);
            min-height: 100vh;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            padding: 1.5rem 0;
            color: #eef2ff;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,0.15), transparent),
                radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,0.1), transparent),
                radial-gradient(2px 2px at 50px 160px, rgba(255,255,255,0.12), transparent),
                radial-gradient(2px 2px at 90px 40px, rgba(255,255,255,0.08), transparent),
                radial-gradient(2px 2px at 130px 80px, rgba(255,255,255,0.14), transparent),
                radial-gradient(2px 2px at 160px 30px, rgba(255,255,255,0.1), transparent);
            background-size: 200px 200px;
            pointer-events: none;
            z-index: 0;
            animation: twinkle 8s ease-in-out infinite alternate;
        }
        @keyframes twinkle {
            0% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        .container {
            position: relative;
            z-index: 1;
            max-width: 1200px;
        }
        .main-balance {
            background: linear-gradient(135deg, #1e2a4a, #0f1729);
            border-radius: 32px;
            padding: 2rem 2rem 1.8rem;
            border: 1px solid rgba(255,215,0,0.15);
            box-shadow: 0 20px 60px rgba(0,0,0,0.7), inset 0 1px 0 rgba(255,215,0,0.1);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.4s ease;
        }
        .main-balance:hover { transform: translateY(-3px); }
        .main-balance .glow {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(255,215,0,0.03), transparent 70%);
            pointer-events: none;
            animation: pulseGlow 6s ease-in-out infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.2); opacity: 0.7; }
        }
        .main-balance .label-saldo {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,215,0,0.6);
            font-weight: 500;
        }
        .main-balance .nominal-saldo {
            font-size: 3.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #f6d365, #fda085);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
            margin: 0.2rem 0 0.5rem;
            letter-spacing: -1px;
        }
        .main-balance .periode {
            color: rgba(255,255,255,0.4);
            font-size: 0.95rem;
        }
        .main-balance .filter-area {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.8rem;
        }
        .main-balance .filter-area .form-control {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,215,0,0.15);
            border-radius: 40px;
            padding: 0.6rem 1.5rem;
            color: #fff;
            width: auto;
            min-width: 180px;
            transition: 0.3s;
        }
        .main-balance .filter-area .form-control:focus {
            background: rgba(255,255,255,0.1);
            border-color: #f6d365;
            box-shadow: 0 0 0 0.2rem rgba(246,211,101,0.2);
            color: #fff;
        }
        .main-balance .filter-area .btn-filter {
            background: rgba(255,215,0,0.12);
            border: 1px solid rgba(255,215,0,0.2);
            border-radius: 40px;
            color: #f6d365;
            padding: 0.6rem 1.8rem;
            transition: 0.3s;
            font-weight: 500;
        }
        .main-balance .filter-area .btn-filter:hover {
            background: rgba(255,215,0,0.25);
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(255,215,0,0.1);
        }
        .card-summary {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 24px;
            padding: 1.5rem 1rem;
            transition: all 0.3s ease;
            height: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .card-summary::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.02), transparent);
            pointer-events: none;
        }
        .card-summary:hover {
            transform: translateY(-6px) scale(1.01);
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,215,0,0.15);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .card-summary .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
            font-size: 1.8rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .card-summary .label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5);
            font-weight: 500;
        }
        .card-summary .value {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0.3rem 0 0;
            line-height: 1.2;
        }
        .card-summary.masuk .icon-circle { border-color: rgba(105,240,174,0.3); color: #69f0ae; }
        .card-summary.masuk .value { color: #69f0ae; }
        .card-summary.keluar .icon-circle { border-color: rgba(255,138,128,0.3); color: #ff8a80; }
        .card-summary.keluar .value { color: #ff8a80; }
        .card-summary.saldo .icon-circle { border-color: rgba(130,177,255,0.3); color: #82b1ff; }
        .card-summary.saldo .value { color: #82b1ff; }

        /* Form glass */
        .form-glass {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 28px;
            padding: 1.8rem;
            transition: 0.4s;
        }
        .form-glass:hover { border-color: rgba(255,215,0,0.12); }
        .form-glass .form-label {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .form-glass .form-control, .form-glass .form-select {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 16px;
            padding: 0.75rem 1.2rem;
            color: #fff;
            transition: 0.3s;
        }
        .form-glass .form-control:focus, .form-glass .form-select:focus {
            background: rgba(255,255,255,0.2);
            border-color: #f6d365;
            box-shadow: 0 0 0 0.2rem rgba(246,211,101,0.15);
            color: #fff;
        }
        .form-glass .form-control::placeholder {
            color: rgba(255,255,255,0.5);
        }
        .form-glass .form-select option {
            color: #000 !important;
            background: #fff !important;
        }
        .form-glass input[type="number"]::-webkit-inner-spin-button,
        .form-glass input[type="number"]::-webkit-outer-spin-button {
            opacity: 0.8;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            height: 30px;
        }
        .form-glass input[type="number"] {
            -moz-appearance: textfield;
        }
        .form-glass input[type="number"]:hover::-webkit-inner-spin-button {
            opacity: 1;
        }
        .btn-gold {
            background: linear-gradient(135deg, #f6d365, #fda085);
            border: none;
            border-radius: 40px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: #0b0e1a;
            transition: 0.3s;
            letter-spacing: 0.3px;
        }
        .btn-gold:hover {
            transform: scale(1.03);
            box-shadow: 0 12px 30px rgba(246,211,101,0.35);
            color: #0b0e1a;
        }

        /* Tab Navigasi */
        .nav-tabs-custom {
            border-bottom: 1px solid rgba(255,255,255,0.06);
            margin-bottom: 1.5rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .nav-tabs-custom .nav-item {
            list-style: none;
        }
        .nav-tabs-custom .nav-link {
            background: transparent;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 40px;
            color: rgba(255,255,255,0.5);
            font-weight: 500;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .nav-tabs-custom .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.05);
        }
        .nav-tabs-custom .nav-link.active {
            background: rgba(255,215,0,0.12);
            color: #f6d365;
            border: 1px solid rgba(255,215,0,0.2);
        }
        .nav-tabs-custom .nav-link i {
            margin-right: 0.5rem;
        }

        /* Tabel - perbaikan kontras teks */
        .table-wrapper {
            background: rgba(255,255,255,0.02);
            backdrop-filter: blur(8px);
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,0.05);
            overflow: hidden;
            transition: 0.4s;
        }
        .table-wrapper:hover { border-color: rgba(255,215,0,0.08); }
        .table-wrapper .table-header {
            padding: 1.2rem 1.8rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .table-wrapper .table-header .title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #fff; /* putih terang */
        }
        .table-wrapper .table-header .badge-count {
            background: rgba(255,255,255,0.06);
            border-radius: 40px;
            padding: 0.3rem 1rem;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
        }
        .btn-excel {
            background: rgba(105,240,174,0.08);
            border: 1px solid rgba(105,240,174,0.15);
            border-radius: 40px;
            color: #69f0ae;
            padding: 0.4rem 1.2rem;
            transition: 0.3s;
            font-size: 0.9rem;
            text-decoration: none;
        }
        .btn-excel:hover {
            background: rgba(105,240,174,0.18);
            color: #69f0ae;
            transform: scale(1.02);
        }
        .table-custom {
            margin: 0;
            color: #fff; /* teks putih untuk seluruh tabel */
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-custom th {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.9); /* lebih terang */
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1px;
            padding: 1rem 1rem 0.8rem;
            background: transparent;
        }
        .table-custom td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            vertical-align: middle;
            background: transparent;
            color: #f0f0f0; /* putih bersih */
        }
        .table-custom tbody tr {
            transition: 0.2s;
        }
        .table-custom tbody tr:hover {
            background: rgba(255,255,255,0.06);
        }
        .table-custom .badge-jenis {
            border-radius: 40px;
            padding: 0.3rem 1rem;
            font-weight: 500;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-masuk {
            background: rgba(105,240,174,0.2);
            color: #69f0ae;
        }
        .badge-keluar {
            background: rgba(255,138,128,0.2);
            color: #ff8a80;
        }
        .text-gold {
            color: #f6d365;
        }
        .btn-delete {
            background: rgba(255,138,128,0.08);
            border: 1px solid rgba(255,138,128,0.1);
            border-radius: 40px;
            color: #ff8a80;
            padding: 0.3rem 1rem;
            transition: 0.3s;
            font-size: 0.8rem;
            text-decoration: none;
        }
        .btn-delete:hover {
            background: rgba(255,138,128,0.2);
            color: #ff8a80;
            transform: scale(1.05);
        }
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeUp 0.7s ease forwards;
        }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up-d1 { animation-delay: 0.05s; }
        .fade-up-d2 { animation-delay: 0.1s; }
        .fade-up-d3 { animation-delay: 0.15s; }
        .fade-up-d4 { animation-delay: 0.2s; }
        .table-responsive::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(255,215,0,0.2);
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.02);
        }
        @media (max-width: 768px) {
            .main-balance .nominal-saldo { font-size: 2.4rem; }
            .main-balance .filter-area .form-control { min-width: 140px; }
            .nav-tabs-custom .nav-link { padding: 0.5rem 1rem; font-size: 0.9rem; }
        }
        .alert-custom {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            color: #fff;
        }
        .alert-custom.alert-success { border-color: #69f0ae; background: rgba(105,240,174,0.08); }
        .alert-custom.alert-danger  { border-color: #ff8a80; background: rgba(255,138,128,0.08); }
        .alert-custom.alert-warning { border-color: #ffd740; background: rgba(255,215,64,0.08); }
        .table-selisih .text-end { text-align: right; }
        .table-selisih .fw-bold { font-weight: 600; }
        .table-selisih .total-row { border-top: 2px solid rgba(255,215,0,0.2); }
        .table-selisih .selisih-positif { color: #69f0ae; }
        .table-selisih .selisih-negatif { color: #ff8a80; }
    </style>
</head>
<body>

<div class="container">

    <!-- Notifikasi -->
    <?php if ($pesan): ?>
    <div class="alert alert-custom alert-<?= $tipe ?> alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-info-circle me-2"></i> <?= htmlspecialchars($pesan) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Saldo Utama -->
    <div class="main-balance fade-up">
        <div class="glow"></div>
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <div class="label-saldo"><i class="bi bi-wallet2 me-2"></i>Saldo Keluarga</div>
                <div class="nominal-saldo">Rp <?= number_format($saldo, 0, ',', '.') ?></div>
                <div class="periode"><i class="bi bi-calendar3 me-1"></i> Bulan <?= date('F Y', strtotime($bulan_awal)) ?></div>
            </div>
            <div class="filter-area">
                <form method="get" class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="month" name="bulan" value="<?= $bulan ?>" class="form-control">
                    <button class="btn-filter" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Ringkasan 3 Kartu -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 fade-up fade-up-d1">
            <div class="card-summary masuk">
                <div class="icon-circle"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="label">Pemasukan</div>
                <div class="value">Rp <?= number_format($ringkasan['total_masuk'], 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="col-md-4 fade-up fade-up-d2">
            <div class="card-summary keluar">
                <div class="icon-circle"><i class="bi bi-arrow-down-circle"></i></div>
                <div class="label">Pengeluaran</div>
                <div class="value">Rp <?= number_format($ringkasan['total_keluar'], 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="col-md-4 fade-up fade-up-d3">
            <div class="card-summary saldo">
                <div class="icon-circle"><i class="bi bi-pie-chart"></i></div>
                <div class="label">Selisih</div>
                <div class="value">Rp <?= number_format($saldo, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <!-- Form Tambah -->
    <div class="form-glass mb-4 fade-up fade-up-d4">
        <h5 class="mb-3" style="font-weight:600; color:rgba(255,255,255,0.7);"><i class="bi bi-plus-circle-dotted me-2 text-gold"></i>Tambah Transaksi</h5>
        <form action="add.php" method="post">
            <input type="hidden" name="bulan_kembali" value="<?= $bulan ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Jenis</label>
                    <select name="jenis" class="form-select" required>
                        <option value="masuk">Pemasukan</option>
                        <option value="keluar">Pengeluaran</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jumlah (Rp)</label>
                    <input type="number" name="jumlah" class="form-control" step="0.01" min="0.01" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" class="form-control" placeholder="contoh: Gaji, Makanan">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Opsional">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-gold w-100"><i class="bi bi-save me-1"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tab Navigasi -->
    <ul class="nav-tabs-custom fade-up fade-up-d4">
        <li class="nav-item">
            <a class="nav-link <?= $tab == 'all' ? 'active' : '' ?>" href="?bulan=<?= $bulan ?>&tab=all">
                <i class="bi bi-table"></i> Semua
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab == 'masuk' ? 'active' : '' ?>" href="?bulan=<?= $bulan ?>&tab=masuk">
                <i class="bi bi-arrow-up-circle text-success"></i> Pemasukan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab == 'keluar' ? 'active' : '' ?>" href="?bulan=<?= $bulan ?>&tab=keluar">
                <i class="bi bi-arrow-down-circle text-danger"></i> Pengeluaran
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab == 'selisih' ? 'active' : '' ?>" href="?bulan=<?= $bulan ?>&tab=selisih">
                <i class="bi bi-bar-chart-fill text-gold"></i> Selisih
            </a>
        </li>
    </ul>

    <!-- Konten berdasarkan tab -->
    <?php if ($tab == 'selisih'): ?>
        <!-- Tabel Selisih Per Kategori -->
        <div class="table-wrapper fade-up fade-up-d4">
            <div class="table-header">
                <span class="title"><i class="bi bi-bar-chart me-2 text-gold"></i>Perbandingan Pemasukan & Pengeluaran per Kategori</span>
                <span class="badge-count"><i class="bi bi-database me-1"></i> <?= count($selisih_data) ?> kategori</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-selisih">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th class="text-end">Total Masuk</th>
                            <th class="text-end">Total Keluar</th>
                            <th class="text-end">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($selisih_data)): ?>
                        <tr><td colspan="4" class="text-center py-4" style="color:rgba(255,255,255,0.3);">Belum ada transaksi bulan ini</td></tr>
                        <?php else: ?>
                            <?php foreach ($selisih_data as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['kategori']) ?></td>
                                <td class="text-end text-success">Rp <?= number_format($row['total_masuk'], 0, ',', '.') ?></td>
                                <td class="text-end text-danger">Rp <?= number_format($row['total_keluar'], 0, ',', '.') ?></td>
                                <td class="text-end fw-bold <?= $row['selisih'] >= 0 ? 'selisih-positif' : 'selisih-negatif' ?>">
                                    Rp <?= number_format($row['selisih'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td class="fw-bold">Total Keseluruhan</td>
                                <td class="text-end fw-bold text-success">Rp <?= number_format($total_masuk_all, 0, ',', '.') ?></td>
                                <td class="text-end fw-bold text-danger">Rp <?= number_format($total_keluar_all, 0, ',', '.') ?></td>
                                <td class="text-end fw-bold <?= $selisih_all >= 0 ? 'selisih-positif' : 'selisih-negatif' ?>">
                                    Rp <?= number_format($selisih_all, 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Tabel Daftar Transaksi -->
        <div class="table-wrapper fade-up fade-up-d4">
            <div class="table-header">
                <span class="title">
                    <i class="bi bi-list-ul me-2 text-gold"></i>
                    Daftar Transaksi
                    <?php if ($tab == 'masuk'): ?><span class="text-success"> (Pemasukan)</span><?php endif; ?>
                    <?php if ($tab == 'keluar'): ?><span class="text-danger"> (Pengeluaran)</span><?php endif; ?>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge-count"><i class="bi bi-database me-1"></i> <?= count($transaksi) ?> data</span>
                    <a href="export_excel.php?bulan=<?= $bulan ?>" class="btn-excel">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor Excel
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transaksi)): ?>
                        <tr><td colspan="6" class="text-center py-4" style="color:rgba(255,255,255,0.3);">Belum ada transaksi bulan ini</td></tr>
                        <?php else: foreach ($transaksi as $t): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                            <td>
                                <span class="badge-jenis <?= $t['jenis'] == 'masuk' ? 'badge-masuk' : 'badge-keluar' ?>">
                                    <?= $t['jenis'] == 'masuk' ? 'Masuk' : 'Keluar' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($t['kategori']) ?></td>
                            <td><?= htmlspecialchars($t['keterangan'] ?? '-') ?></td>
                            <td class="text-end fw-semibold <?= $t['jenis'] == 'masuk' ? 'text-success' : 'text-danger' ?>">
                                Rp <?= number_format($t['jumlah'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <a href="delete.php?id=<?= $t['id'] ?>&bulan=<?= $bulan ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('Hapus transaksi ini?')">
                                   <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
