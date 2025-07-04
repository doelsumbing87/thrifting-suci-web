<?php
// my_orders.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();

$message = '';
$message_type = '';

// Load konfigurasi pembayaran
$payment_config = require_once __DIR__ . '/config/payment.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my_orders.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];

// Cek apakah ada pesan dari checkout
if (isset($_GET['status']) && $_GET['status'] === 'success' && isset($_GET['order_id'])) {
    $message = 'Pesanan Anda #' . htmlspecialchars($_GET['order_id']) . ' berhasil dibuat!';
    $message_type = 'success';
} elseif (isset($_GET['error']) && $_GET['error'] === 'stock_issue') {
    $message = 'Ada masalah stok pada beberapa produk di keranjang Anda. Silakan cek kembali keranjang Anda.';
    $message_type = 'error';
}


try {
    // Ambil semua pesanan untuk user yang sedang login
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt_orders->execute([$user_id]);
    $orders = $stmt_orders->fetchAll();

    // Untuk setiap pesanan, ambil item-itemnya
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
    $message = 'Terjadi kesalahan database saat mengambil riwayat pesanan: ' . $e->getMessage();
    $message_type = 'error';
    $orders = [];
}

// Sertakan header tampilan (ini sudah berisi <html>, <head>, dan <body> pembuka)
include __DIR__ . '/includes/header.php';
?>

    <main class="container">
        <h1>Riwayat Pesanan Saya</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3>Pesanan #<?php echo htmlspecialchars($order['id']); ?></h3>
                        <span class="order-status <?php echo htmlspecialchars($order['status']); ?>">
                            Status: <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                        </span>
                    </div>
                    <p>Tanggal Pesanan: <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
                    <p>Alamat Pengiriman: <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    <p>Metode Pembayaran: <?php echo htmlspecialchars($order['payment_method']); ?></p>
                    <p>Status Pembayaran: <?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?></p>

                    <?php
                    // Tampilkan detail bank jika metode pembayaran adalah transfer bank dan statusnya unpaid/pending
                    if (strpos($order['payment_method'], 'Transfer Bank -') === 0 && $order['payment_status'] === 'unpaid') {
                        $bank_code = strtolower(str_replace('Transfer Bank - ', '', $order['payment_method'])); // misal: "bca"
                        $bank_detail = $payment_config['bank_transfers'][$bank_code] ?? null;

                        if ($bank_detail) {
                            echo '<div style="background-color: #e9f7ef; padding: 15px; border-left: 5px solid #28a745; margin-top: 15px; border-radius: 4px;">';
                            echo '<h4>Instruksi Pembayaran:</h4>';
                            echo '<p>Mohon transfer sejumlah **Rp ' . number_format($order['total_amount'], 0, ',', '.') . '** ke rekening berikut:</p>';
                            echo '<p><strong>Bank:</strong> ' . htmlspecialchars($bank_detail['name']) . '</p>';
                            echo '<p><strong>Nomor Rekening:</strong> ' . htmlspecialchars($bank_detail['account_number']) . '</p>';
                            echo '<p><strong>Atas Nama:</strong> ' . htmlspecialchars($bank_detail['account_name']) . '</p>';
                            echo '<p><strong>Cabang:</strong> ' . htmlspecialchars($bank_detail['branch']) . '</p>';
                            echo '<p>Setelah melakukan transfer, pesanan Anda akan segera diproses. Jangan lupa konfirmasi pembayaran Anda kepada admin.</p>';
                            echo '</div>';
                        }
                    } elseif ($order['payment_method'] === 'COD' && $order['payment_status'] === 'unpaid') {
                        echo '<div style="background-color: #e9f7ef; padding: 15px; border-left: 5px solid #28a745; margin-top: 15px; border-radius: 4px;">';
                        echo '<h4>Instruksi Pembayaran:</h4>';
                        echo '<p>' . htmlspecialchars($payment_config['cod_info']) . '</p>';
                        echo '<p>Mohon siapkan uang tunai sejumlah **Rp ' . number_format($order['total_amount'], 0, ',', '.') . '** saat kurir mengantarkan pesanan Anda.</p>';
                        echo '</div>';
                    }
                    ?>

                    <h4>Detail Item:</h4>
                    <ul class="order-item-list">
                        <?php if (!empty($order['items'])): ?>
                            <?php foreach ($order['items'] as $item): ?>
                                <li>
                                    <img src="assets/images/products/<?php echo htmlspecialchars($item['product_image'] ?: 'product_placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    <div class="order-item-details">
                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                        <span>Kuantitas: <?php echo htmlspecialchars($item['quantity']); ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                        <br>
                                        <span>Subtotal: Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Tidak ada item untuk pesanan ini.</li>
                        <?php endif; ?>
                    </ul>
                    <div class="order-total-amount">
                        Total Pesanan: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 50px;">Anda belum memiliki riwayat pesanan.</p>
            <p style="text-align: center;"><a href="products.php" class="button">Mulai Belanja</a></p>
        <?php endif; ?>
    </main>

<?php
// Sertakan footer tampilan (ini akan berisi </body> dan </html> penutup)
include __DIR__ . '/includes/footer.php';
?>