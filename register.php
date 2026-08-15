<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($username === '' || $nama === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $check->bind_param('s', $username);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $error = 'Username sudah digunakan, pilih username lain.';
        } else {
            // Akun baru dari form registrasi selalu berperan sebagai 'staff'
            // (role 'admin' hanya dibuat manual lewat database untuk keamanan)
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $role = 'staff';
            $stmt = $conn->prepare('INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $username, $hashed, $nama, $role);
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Registrasi gagal, coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar - Inventory Control System</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-box">
    <h2>📦 Inventory Control System</h2>
    <h3>Daftar Akun Staff</h3>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" class="btn btn-primary">Daftar</button>
    </form>

    <p><a href="login.php">Sudah punya akun? Login</a></p>
</div>
</body>
</html>
