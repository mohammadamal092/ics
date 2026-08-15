<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Katalog Barang';

$keyword = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $stmt = $conn->prepare(
        'SELECT b.*, k.nama_kategori FROM barang b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         WHERE b.nama_barang LIKE ? OR b.kode_barang LIKE ?
         ORDER BY b.nama_barang ASC'
    );
    $like = "%$keyword%";
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query(
        'SELECT b.*, k.nama_kategori FROM barang b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         ORDER BY b.nama_barang ASC'
    );
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Katalog Barang</h1>
    <?php if (is_admin()): ?>
        <a href="create.php" class="btn btn-primary">+ Tambah Barang</a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<form method="GET" class="search-form">
    <input type="text" name="q" placeholder="Cari kode atau nama barang..." value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit" class="btn btn-secondary">Cari</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th>Harga Beli</th>
            <th>Harga Jual</th>
            <th>Stok</th>
            <?php if (is_admin()): ?><th>Aksi</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="8" class="text-center">Belum ada data barang.</td></tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="<?= $row['stok'] <= $row['stok_minimum'] ? 'row-warning' : '' ?>">
                    <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['satuan']) ?></td>
                    <td><?= rupiah($row['harga_beli']) ?></td>
                    <td><?= rupiah($row['harga_jual']) ?></td>
                    <td><?= $row['stok'] ?></td>
                    <?php if (is_admin()): ?>
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-small btn-edit">Edit</a>
                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-small btn-delete"
                           onclick="return confirm('Yakin hapus barang ini?');">Hapus</a>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
