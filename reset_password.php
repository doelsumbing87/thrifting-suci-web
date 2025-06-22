<?php
// reset_password.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();

$message = '';
$message_type = '';

$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

// Validasi awal token dan email dari URL
// Cek jika ini bukan POST request (yaitu, saat halaman diakses dari link email)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($email) || empty($token)) {
        $message = 'Tautan reset password tidak valid atau tidak lengkap.';
        $message_type = 'error';
    } else {
        try {
            // Cek token di database
            $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
            $stmt->execute([$email, $token]);
            $reset_request = $stmt->fetch();

            if (!$reset_request) {
                $message = 'Tautan reset password tidak valid atau sudah kadaluarsa.';
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Terjadi kesalahan saat memverifikasi tautan.';
            $message_type = 'error';
        }
    }
}


// Proses form reset password jika ada pengiriman POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $email = trim($_POST['email']);
    $token = trim($_POST['token']);
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Pastikan token dan email valid lagi sebelum update
    try {
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
        $stmt->execute([$email, $token]);
        $reset_request_post = $stmt->fetch();

        if (!$reset_request_post) {
            $message = 'Tautan reset password tidak valid atau sudah kadaluarsa. Silakan coba lagi dari awal.';
            $message_type = 'error';
        } elseif (empty($new_password) || empty($confirm_new_password)) {
            $message = 'Password baru dan konfirmasi password harus diisi.';
            $message_type = 'error';
        } elseif ($new_password !== $confirm_new_password) {
            $message = 'Konfirmasi password baru tidak cocok.';
            $message_type = 'error';
        } elseif (strlen($new_password) < 6) {
            $message = 'Password baru minimal 6 karakter.';
            $message_type = 'error';
        } else {
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password di tabel users
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);

            // Hapus token reset dari database agar tidak bisa digunakan lagi
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            $message = 'Password Anda berhasil direset! Silakan login dengan password baru Anda.';
            $message_type = 'success';
            // Redirect ke halaman login setelah reset berhasil
            header('Location: login.php?reset=true');
            exit();
        }
    } catch (PDOException $e) {
        $message = 'Terjadi kesalahan database saat mereset password: ' . $e->getMessage();
        $message_type = 'error';
    }
}

include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php // Header ?>

    <main class="form-container">
        <h2>Reset Password Anda</h2>
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php 
        // Tampilkan form hanya jika tidak ada error dan token/email ada di URL (GET request)
        // atau jika ini POST request dan token/email masih valid
        if ($message_type !== 'error' && (($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($email) && !empty($token) && isset($reset_request)) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($reset_request_post)))): 
        ?>
            <form action="reset_password.php" method="POST">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="form-group">
                    <label for="new_password">Password Baru:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_new_password">Konfirmasi Password Baru:</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        <?php else: ?>
            <?php if ($message_type === 'error'): // Hanya tampilkan link kembali jika ada error ?>
                <p>Silakan kembali ke halaman <a href="forgot_password.php">lupa password</a> untuk meminta tautan baru.</p>
            <?php endif; ?>
        <?php endif; ?>
        
        <p>Kembali ke <a href="login.php">halaman login</a></p>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>