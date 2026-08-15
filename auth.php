<?php
// =========================================================
// Cek session login. Panggil file ini di paling atas
// setiap halaman yang butuh login.
// =========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

/**
 * Panggil fungsi ini di halaman yang HANYA boleh diakses admin
 * (mis. hapus data, kelola user, kelola kategori).
 */
function require_admin() {
    if ($_SESSION['role'] !== 'admin') {
        die('Akses ditolak. Halaman ini khusus untuk Admin.');
    }
}
