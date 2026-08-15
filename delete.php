<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare('DELETE FROM barang WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();

header('Location: index.php?msg=Barang berhasil dihapus');
exit;
