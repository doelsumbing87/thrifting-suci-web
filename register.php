<?php
// register.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection(); // Dapatkan koneksi database

$message = '';
$message_type = '';

// Proses form registrasi jika ada pengiriman POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = 'Semua field harus diisi.';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak valid.';
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'Konfirmasi password tidak cocok.';
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password minimal 6 karakter.';
        $message_type = 'error';
    } else {
        try {
            // Periksa apakah username atau email sudah ada
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $message = 'Username atau email sudah terdaftar.';
                $message_type = 'error';
            } else {
                // Hash password sebelum menyimpan
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Masukkan data pengguna baru ke database
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$username, $email, $hashed_password]);

                $message = 'Registrasi berhasil! Silakan login.';
                $message_type = 'success';
                // Redirect ke halaman login setelah registrasi berhasil
                header('Location: login.php?registered=true');
                exit();
            }
        } catch (PDOException $e) {
            $message = 'Terjadi kesalahan database: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Sertakan header tampilan
include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php // Header akan tampil di sini ?>

    <main class="form-container">
        <h2>Daftar Akun Baru</h2>
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </main>

    <?php // Footer akan tampil di sini ?>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>