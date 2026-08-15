<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Barang Masuk';
$error = '';
$success = '';

$barang_list = $conn->query('SELECT id, kode_barang, nama_barang, stok FROM barang ORDER BY nama_barang ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barang_id = (int)($_POST['barang_id'] ?? 0);
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $user_id = $_SESSION['user_id'];

    if ($barang_id <= 0 || $jumlah <= 0) {
        $error = 'Pilih barang dan isi jumlah dengan benar (jumlah harus lebih dari 0).';
    } else {
        // Gunakan transaction supaya insert log & update stok konsisten
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare(
                'INSERT INTO transaksi_masuk (barang_id, jumlah, keterangan, user_id) VALUES (?, ?, ?, ?)'
            );
            $stmt->bind_param('iisi', $barang_id, $jumlah, $keterangan, $user_id);
            $stmt->execute();

            $upd = $conn->prepare('UPDATE barang SET stok = stok + ? WHERE id = ?');
            $upd->bind_param('ii', $jumlah, $barang_id);
            $upd->execute();

            $conn->commit();
            $success = 'Barang masuk berhasil dicatat & stok bertambah.';
            $barang_list = $conn->query('SELECT id, kode_barang, nama_barang, stok FROM barang ORDER BY nama_barang ASC');
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Gagal menyimpan transaksi.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<h1>Input Barang Masuk</h1>

<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="POST" class="form-card">
    <label>Barang</label>
    <select name="barang_id" required>
        <option value="">-- Pilih Barang --</option>
        <?php while ($b = $barang_list->fetch_assoc()): ?>
            <option value="<?= $b['id'] ?>">
                <?= htmlspecialchars($b['kode_barang']) ?> - <?= htmlspecialchars($b['nama_barang']) ?> (stok: <?= $b['stok'] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Jumlah Masuk</label>
    <input type="number" name="jumlah" min="1" required>

    <label>Keterangan (opsional)</label>
    <input type="text" name="keterangan" placeholder="mis. pembelian dari supplier X">

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
