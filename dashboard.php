<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Dashboard';

// Total jenis barang
$total_barang = $conn->query('SELECT COUNT(*) AS total FROM barang')->fetch_assoc()['total'];

// Total nilai stok (harga_beli * stok)
$nilai_stok = $conn->query('SELECT SUM(harga_beli * stok) AS total FROM barang')->fetch_assoc()['total'] ?? 0;

// Barang dengan stok di bawah minimum
$stok_menipis = $conn->query('SELECT * FROM barang WHERE stok <= stok_minimum ORDER BY stok ASC');

// Transaksi hari ini
$masuk_hari_ini = $conn->query("SELECT COUNT(*) AS total FROM transaksi_masuk WHERE DATE(tanggal) = CURDATE()")->fetch_assoc()['total'];
$keluar_hari_ini = $conn->query("SELECT COUNT(*) AS total FROM transaksi_keluar WHERE DATE(tanggal) = CURDATE()")->fetch_assoc()['total'];

include __DIR__ . '/includes/header.php';
?>

<h1>Dashboard</h1>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-label">Total Jenis Barang</span>
        <span class="stat-value"><?= $total_barang ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Estimasi Nilai Stok</span>
        <span class="stat-value"><?= rupiah($nilai_stok) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Transaksi Masuk Hari Ini</span>
        <span class="stat-value"><?= $masuk_hari_ini ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Transaksi Keluar Hari Ini</span>
        <span class="stat-value"><?= $keluar_hari_ini ?></span>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Stok</th>
            <th>Stok Minimum</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($stok_menipis->num_rows === 0): ?>
            <tr><td colspan="4" class="text-center">Semua stok aman 👍</td></tr>
        <?php else: ?>
            <?php while ($row = $stok_menipis->fetch_assoc()): ?>
                <tr class="row-warning">
                    <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td><?= $row['stok'] ?></td>
                    <td><?= $row['stok_minimum'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/includes/footer.php'; ?>
