<?php
// admin/orders.php

require_once __DIR__ . '/../includes/functions.php';
$pdo = getDbConnection();

$page_title = 'Manajemen Pesanan';
$message = '';
$message_type = '';

// Handle aksi update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    // Validasi status yang diizinkan
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        $message = 'Status pesanan tidak valid.';
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$new_status, $order_id])) {
                $message = 'Status pesanan #' . htmlspecialchars($order_id) . ' berhasil diperbarui menjadi ' . ucfirst($new_status) . '.';
                $message_type = 'success';
            } else {
                $message = 'Gagal memperbarui status pesanan #' . htmlspecialchars($order_id) . '.';
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Terjadi kesalahan database saat memperbarui status pesanan: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Ambil semua pesanan dari database
$orders = [];
try {
    $stmt_orders = $pdo->prepare("
        SELECT o.*, u.username, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC
    ");
    $stmt_orders->execute();
    $orders = $stmt_orders->fetchAll();

    // Untuk setiap pesanan, ambil detail itemnya
    foreach ($orders as &$order) { // Gunakan & untuk referensi agar bisa diubah
        $stmt_items = $pdo->prepare("
            SELECT oi.*, p.name AS product_name, p.image AS product_image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$order['id']]);
        $order['items'] = $stmt_items->fetchAll();
    }
    unset($order); // Putuskan referensi setelah loop

} catch (PDOException $e) {
    $message = 'Terjadi kesalahan database saat mengambil daftar pesanan: ' . $e->getMessage();
    $message_type = 'error';
    $orders = [];
}


include __DIR__ . '/includes/header.php';
?>

<h1>Manajemen Pesanan</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if (!empty($orders)): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID Pesanan</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($order['username']); ?>
                        <br><small><?php echo htmlspecialchars($order['email']); ?></small>
                    </td>
                    <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                    <td><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></td>
                    <td>
                        <form action="orders.php" method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="update_order_status">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                    <td><?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?></td>
                    <td class="actions-buttons">
                        <button type="button" class="button edit-btn" onclick="toggleOrderDetails(<?php echo htmlspecialchars($order['id']); ?>)">Lihat Detail</button>
                    </td>
                </tr>
                <tr class="order-detail-row" id="order-detail-<?php echo htmlspecialchars($order['id']); ?>" style="display: none;">
                    <td colspan="7">
                        <div style="padding: 15px; background-color: #fefefe; border: 1px dashed #ddd; margin: 10px 0;">
                            <h4>Detail Pesanan #<?php echo htmlspecialchars($order['id']); ?>:</h4>
                            <p><strong>Alamat Pengiriman:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                            <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                            
                            <h5>Item Pesanan:</h5>
                            <ul style="list-style: none; padding: 0;">
                                <?php if (!empty($order['items'])): ?>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li style="display: flex; align-items: center; margin-bottom: 8px;">
                                            <img src="../assets/images/products/<?php echo htmlspecialchars($item['product_image'] ?: 'product_placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 2px; margin-right: 10px;">
                                            <span>
                                                <?php echo htmlspecialchars($item['product_name']); ?> (x<?php echo htmlspecialchars($item['quantity']); ?>) - Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>Tidak ada item untuk pesanan ini.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align: center; margin-top: 50px;">Belum ada pesanan yang masuk.</p>
<?php endif; ?>

<script>
function toggleOrderDetails(orderId) {
    var row = document.getElementById('order-detail-' + orderId);
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>