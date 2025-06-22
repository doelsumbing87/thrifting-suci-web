<?php
// my_orders.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();

$message = '';
$message_type = '';

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

include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* CSS Tambahan untuk My Orders */
        .order-card {
            background-color: #fff;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ddd;
        }
        .order-header h3 {
            margin: 0;
            font-size: 1.4rem;
            color: #333;
        }
        .order-status {
            font-weight: bold;
            color: #007bff; /* Default */
        }
        .order-status.pending { color: #ffc107; }
        .order-status.processing { color: #17a2b8; }
        .order-status.shipped { color: #007bff; }
        .order-status.delivered { color: #28a745; }
        .order-status.cancelled { color: #dc3545; }

        .order-item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .order-item-list li {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dotted #eee;
        }
        .order-item-list li:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .order-item-list img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        .order-item-details {
            flex-grow: 1;
        }
        .order-item-details strong {
            display: block;
            font-size: 1.1rem;
            color: #333;
        }
        .order-item-details span {
            color: #666;
            font-size: 0.9rem;
        }
        .order-total-amount {
            text-align: right;
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

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

                    <h4>Detail Item:</h4>
                    <ul class="order-item-list">
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

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>