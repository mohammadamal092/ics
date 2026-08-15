<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

$page_title = 'Edit Barang';
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT * FROM barang WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$barang = $stmt->get_result()->fetch_assoc();

if (!$barang) {
    header('Location: index.php?msg=Barang tidak ditemukan');
    exit;
}

$kategori_list = $conn->query('SELECT * FROM kategori ORDER BY nama_kategori ASC');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = trim($_POST['kode_barang'] ?? '');
    $nama = trim($_POST['nama_barang'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0) ?: null;
    $satuan = trim($_POST['satuan'] ?? 'pcs');
    $harga_beli = (float)($_POST['harga_beli'] ?? 0);
    $harga_jual = (float)($_POST['harga_jual'] ?? 0);
    $stok_minimum = (int)($_POST['stok_minimum'] ?? 5);

    if ($kode === '' || $nama === '') {
        $error = 'Kode dan nama barang wajib diisi.';
    } else {
        // Catatan: stok TIDAK diedit manual di sini.
        // Perubahan stok harus lewat menu Barang Masuk / Barang Keluar
        // supaya riwayat transaksi tetap konsisten.
        $upd = $conn->prepare(
            'UPDATE barang SET kode_barang=?, nama_barang=?, kategori_id=?, satuan=?, harga_beli=?, harga_jual=?, stok_minimum=?
             WHERE id=?'
        );
        $upd->bind_param('ssisddii', $kode, $nama, $kategori_id, $satuan, $harga_beli, $harga_jual, $stok_minimum, $id);
        if ($upd->execute()) {
            header('Location: index.php?msg=Barang berhasil diperbarui');
            exit;
        } else {
            $error = $upd->errno === 1062 ? 'Kode barang sudah digunakan.' : 'Gagal memperbarui data.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<h1>Edit Barang</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="form-card">
    <label>Kode Barang</label>
    <input type="text" name="kode_barang" value="<?= htmlspecialchars($barang['kode_barang']) ?>" required autofocus>

    <label>Nama Barang</label>
    <input type="text" name="nama_barang" value="<?= htmlspecialchars($barang['nama_barang']) ?>" required>

    <label>Kategori</label>
    <select name="kategori_id">
        <option value="">-- Pilih Kategori --</option>
        <?php while ($k = $kategori_list->fetch_assoc()): ?>
            <option value="<?= $k['id'] ?>" <?= $k['id'] == $barang['kategori_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['nama_kategori']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Satuan</label>
    <input type="text" name="satuan" value="<?= htmlspecialchars($barang['satuan']) ?>" required>

    <label>Harga Beli</label>
    <input type="number" step="0.01" min="0" name="harga_beli" value="<?= $barang['harga_beli'] ?>" required>

    <label>Harga Jual</label>
    <input type="number" step="0.01" min="0" name="harga_jual" value="<?= $barang['harga_jual'] ?>" required>

    <label>Stok Saat Ini (tidak bisa diedit langsung)</label>
    <input type="number" value="<?= $barang['stok'] ?>" disabled>

    <label>Stok Minimum</label>
    <input type="number" min="0" name="stok_minimum" value="<?= $barang['stok_minimum'] ?>" required>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
