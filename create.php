<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

$page_title = 'Tambah Barang';
$error = '';
$kategori_list = $conn->query('SELECT * FROM kategori ORDER BY nama_kategori ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = trim($_POST['kode_barang'] ?? '');
    $nama = trim($_POST['nama_barang'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0) ?: null;
    $satuan = trim($_POST['satuan'] ?? 'pcs');
    $harga_beli = (float)($_POST['harga_beli'] ?? 0);
    $harga_jual = (float)($_POST['harga_jual'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $stok_minimum = (int)($_POST['stok_minimum'] ?? 5);

    if ($kode === '' || $nama === '') {
        $error = 'Kode dan nama barang wajib diisi.';
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO barang (kode_barang, nama_barang, kategori_id, satuan, harga_beli, harga_jual, stok, stok_minimum)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssisddii', $kode, $nama, $kategori_id, $satuan, $harga_beli, $harga_jual, $stok, $stok_minimum);
        if ($stmt->execute()) {
            header('Location: index.php?msg=Barang berhasil ditambahkan');
            exit;
        } else {
            $error = $stmt->errno === 1062 ? 'Kode barang sudah digunakan.' : 'Gagal menyimpan data.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<h1>Tambah Barang</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="form-card">
    <label>Kode Barang</label>
    <input type="text" name="kode_barang" required autofocus>

    <label>Nama Barang</label>
    <input type="text" name="nama_barang" required>

    <label>Kategori</label>
    <select name="kategori_id">
        <option value="">-- Pilih Kategori --</option>
        <?php while ($k = $kategori_list->fetch_assoc()): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Satuan</label>
    <input type="text" name="satuan" value="pcs" required>

    <label>Harga Beli</label>
    <input type="number" step="0.01" min="0" name="harga_beli" required>

    <label>Harga Jual</label>
    <input type="number" step="0.01" min="0" name="harga_jual" required>

    <label>Stok Awal</label>
    <input type="number" min="0" name="stok" value="0" required>

    <label>Stok Minimum</label>
    <input type="number" min="0" name="stok_minimum" value="5" required>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
