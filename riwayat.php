<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Riwayat Transaksi';

$filter_tanggal = trim($_GET['tanggal'] ?? '');

$query = "
    (SELECT 'Masuk' AS jenis, tm.tanggal, b.kode_barang, b.nama_barang, tm.jumlah, tm.keterangan, u.nama_lengkap
     FROM transaksi_masuk tm
     JOIN barang b ON tm.barang_id = b.id
     JOIN users u ON tm.user_id = u.id)
    UNION ALL
    (SELECT 'Keluar' AS jenis, tk.tanggal, b.kode_barang, b.nama_barang, tk.jumlah, tk.keterangan, u.nama_lengkap
     FROM transaksi_keluar tk
     JOIN barang b ON tk.barang_id = b.id
     JOIN users u ON tk.user_id = u.id)
    ORDER BY tanggal DESC
";

$result = $conn->query($query);

include __DIR__ . '/../includes/header.php';
?>

<h1>Riwayat Transaksi</h1>

<table class="table">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>Keterangan</th>
            <th>Oleh</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="7" class="text-center">Belum ada transaksi.</td></tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td>
                        <span class="badge <?= $row['jenis'] === 'Masuk' ? 'badge-in' : 'badge-out' ?>">
                            <?= $row['jenis'] ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td><?= $row['jumlah'] ?></td>
                    <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
