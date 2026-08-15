<?php
// =========================================================
// Kumpulan fungsi bantuan
// =========================================================

function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function clean($conn, $data) {
    return htmlspecialchars(trim($conn->real_escape_string($data)));
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
