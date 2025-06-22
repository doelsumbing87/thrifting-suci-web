<?php
// forgot_password.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Mohon masukkan alamat email yang valid.';
        $message_type = 'error';
    } else {
        try {
            // 1. Cek apakah email terdaftar
            $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // 2. Hapus token lama untuk email ini (jika ada)
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);

                // 3. Buat token unik
                $token = bin2hex(random_bytes(32)); // Token 64 karakter hex
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token berlaku 1 jam

                // 4. Simpan token ke database
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires_at]);

                // 5. Kirim email reset password
                // Sesuaikan 'http://localhost/TOKO_THRIFTING_SUCI/' dengan URL proyek Anda di server XAMPP
                $base_url = "http://" . $_SERVER['HTTP_HOST'] . "/TOKO_THRIFTING_SUCI/";
                $resetLink = $base_url . "reset_password.php?token=" . $token . "&email=" . urlencode($email);


                $subject = "Reset Password untuk Akun TOKO THRIFTING SUCI Anda";
                $body = "
                    <p>Halo " . htmlspecialchars($user['username']) . ",</p>
                    <p>Anda menerima email ini karena Anda (atau seseorang lain) meminta reset password untuk akun Anda di TOKO THRIFTING SUCI.</p>
                    <p>Untuk mereset password Anda, silakan klik tautan berikut:</p>
                    <p><a href=\"{$resetLink}\">{$resetLink}</a></p>
                    <p>Tautan ini akan kadaluarsa dalam 1 jam.</p>
                    <p>Jika Anda tidak meminta reset password ini, Anda bisa mengabaikan email ini.</p>
                    <br>
                    <p>Terima kasih,</p>
                    <p>Tim TOKO THRIFTING SUCI</p>
                ";
                $altBody = "Halo " . htmlspecialchars($user['username']) . ",\n\nAnda menerima email ini karena Anda (atau seseorang lain) meminta reset password untuk akun Anda di TOKO THRIFTING SUCI.\n\nUntuk mereset password Anda, silakan kunjungi tautan berikut: {$resetLink}\n\nTautan ini akan kadaluarsa dalam 1 jam.\n\nJika Anda tidak meminta reset password ini, Anda bisa mengabaikan email ini.\n\nTerima kasih,\nTim TOKO THRIFTING SUCI";

                if (sendEmail($email, $user['username'], $subject, $body, $altBody)) {
                    $message = 'Jika email Anda terdaftar, tautan reset password telah dikirim ke email Anda. Silakan cek kotak masuk Anda.';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal mengirim email reset password. Silakan coba lagi nanti.';
                    $message_type = 'error';
                }
            } else {
                // Jangan beritahu user apakah email terdaftar atau tidak untuk alasan keamanan
                $message = 'Jika email Anda terdaftar, tautan reset password telah dikirim ke email Anda. Silakan cek kotak masuk Anda.';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Terjadi kesalahan database: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php // Header ?>

    <main class="form-container">
        <h2>Lupa Password Anda?</h2>
        <p>Masukkan alamat email Anda untuk menerima tautan reset password.</p>
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <label for="email">Email Anda:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Kirim Tautan Reset</button>
        </form>
        <p>Kembali ke <a href="login.php">halaman login</a></p>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>