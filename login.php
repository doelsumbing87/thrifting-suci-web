<?php
// login.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection(); // Dapatkan koneksi database

$message = '';
$message_type = '';

// Cek apakah ada pesan registrasi sukses atau reset password sukses dari redirect
if (isset($_GET['registered']) && $_GET['registered'] === 'true') {
    $message = 'Registrasi berhasil! Silakan login.';
    $message_type = 'success';
} elseif (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    $message = 'Password Anda berhasil direset! Silakan login dengan password baru Anda.';
    $message_type = 'success';
}


// Proses form login jika ada pengiriman POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    // Validasi input
    if (empty($username_or_email) || empty($password)) {
        $message = 'Username/Email dan password harus diisi.';
        $message_type = 'error';
    } else {
        try {
            // Cari pengguna berdasarkan username atau email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username_or_email, $username_or_email]);
            $user = $stmt->fetch();

            // Verifikasi password
            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil, simpan data pengguna ke sesi
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['user_role'] = $user['role']; // Simpan peran pengguna (user/admin)

                // Redirect berdasarkan peran pengguna
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php'); // Arahkan ke dashboard admin
                } else {
                    header('Location: my_orders.php'); // Arahkan ke dashboard user atau halaman pesanan
                }
                exit();
            } else {
                $message = 'Username/Email atau password salah.';
                $message_type = 'error';
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
    <title>Login - TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php // Header akan tampil di sini ?>

    <main class="form-container">
        <h2>Login ke Akun Anda</h2>
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username_or_email">Username atau Email:</label>
                <input type="text" id="username_or_email" name="username_or_email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        <p><a href="forgot_password.php">Lupa Password?</a></p>
    </main>

    <?php // Footer akan tampil di sini ?>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>