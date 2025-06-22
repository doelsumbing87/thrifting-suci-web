<?php
// admin/users.php

require_once __DIR__ . '/../includes/functions.php';
$pdo = getDbConnection();

$page_title = 'Manajemen Pengguna';
$message = '';
$message_type = '';

// Ambil semua pengguna dari database, kecuali yang role-nya 'admin'
$users = [];
try {
    $stmt = $pdo->prepare("SELECT id, username, email, fullname, phone, address, role, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Terjadi kesalahan database saat mengambil daftar pengguna: ' . $e->getMessage();
    $message_type = 'error';
}


include __DIR__ . '/includes/header.php';
?>

<h1>Manajemen Pengguna</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if (!empty($users)): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Nama Lengkap</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Terdaftar Sejak</th>
                </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['fullname'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($user['address'] ?: '-')); ?></td>
                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                    </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align: center; margin-top: 50px;">Belum ada pengguna terdaftar.</p>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>