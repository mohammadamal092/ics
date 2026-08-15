<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Barang Keluar';
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
        // Cek stok cukup atau tidak sebelum insert
        $cek = $conn->prepare('SELECT stok FROM barang WHERE id = ?');
        $cek->bind_param('i', $barang_id);
        $cek->execute();
        $stok_sekarang = $cek->get_result()->fetch_assoc()['stok'] ?? 0;

        if ($jumlah > $stok_sekarang) {
            $error = "Stok tidak cukup. Stok tersedia hanya $stok_sekarang.";
        } else {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare(
                    'INSERT INTO transaksi_keluar (barang_id, jumlah, keterangan, user_id) VALUES (?, ?, ?, ?)'
                );
                $stmt->bind_param('iisi', $barang_id, $jumlah, $keterangan, $user_id);
                $stmt->execute();

                $upd = $conn->prepare('UPDATE barang SET stok = stok - ? WHERE id = ?');
                $upd->bind_param('ii', $jumlah, $barang_id);
                $upd->execute();

                $conn->commit();
                $success = 'Barang keluar berhasil dicatat & stok berkurang.';
                $barang_list = $conn->query('SELECT id, kode_barang, nama_barang, stok FROM barang ORDER BY nama_barang ASC');
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Gagal menyimpan transaksi.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<h1>Input Barang Keluar</h1>

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

    <label>Jumlah Keluar</label>
    <input type="number" name="jumlah" min="1" required>

    <label>Keterangan (opsional)</label>
    <input type="text" name="keterangan" placeholder="mis. penjualan ke pelanggan">

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
