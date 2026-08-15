<?php
// Diasumsikan session sudah di-start oleh auth.php sebelum file ini di-include
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Inventory Control System</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-brand">📦 Inventory Control System</div>
    <ul class="navbar-menu">
        <li><a href="/dashboard.php">Dashboard</a></li>
        <li><a href="/barang/index.php">Barang</a></li>
        <li><a href="/kategori/index.php">Kategori</a></li>
        <li><a href="/transaksi/masuk.php">Barang Masuk</a></li>
        <li><a href="/transaksi/keluar.php">Barang Keluar</a></li>
        <li><a href="/transaksi/riwayat.php">Riwayat</a></li>
    </ul>
    <div class="navbar-user">
        <span><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?> (<?= htmlspecialchars($_SESSION['role'] ?? '') ?>)</span>
        <a href="/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>
<main class="container">
