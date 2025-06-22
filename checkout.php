<?php
// checkout.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();

$message = '';
$message_type = '';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

// Redirect jika keranjang kosong
if (empty($_SESSION['cart'])) {
    header('Location: cart.php?error=empty_cart_checkout');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_info = null; // Untuk menyimpan info user yang akan mengisi form

// Ambil info user dari database untuk mengisi form awal
try {
    $stmt_user = $pdo->prepare("SELECT fullname, email, address, phone FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user_info = $stmt_user->fetch();

    if (!$user_info) {
        session_destroy();
        header('Location: login.php?error=invalid_user');
        exit();
    }

} catch (PDOException $e) {
    $message = 'Terjadi kesalahan saat mengambil data pengguna: ' . $e->getMessage();
    $message_type = 'error';
}


$subtotal = 0;
foreach ($_SESSION['cart'] as $product_id => $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Proses checkout jika ada pengiriman POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $shipping_address = trim($_POST['shipping_address']);
    $payment_method = trim($_POST['payment_method']);
    $phone_number = trim($_POST['phone']); // Mengambil nomor telepon dari form

    if (empty($shipping_address) || empty($payment_method) || empty($phone_number)) {
        $message = 'Alamat pengiriman, nomor telepon, dan metode pembayaran harus diisi.';
        $message_type = 'error';
    } else {
        try {
            $pdo->beginTransaction(); // Mulai transaksi database

            // 1. Buat pesanan baru di tabel `orders`
            // Saat ini tabel orders tidak memiliki kolom phone_number.
            // Anda bisa tambahkan kolom ini di phpMyAdmin jika diperlukan.
            $stmt_order = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status, payment_status)
                VALUES (?, ?, ?, ?, 'pending', 'unpaid')
            ");
            $stmt_order->execute([$user_id, $subtotal, $shipping_address, $payment_method]);
            $order_id = $pdo->lastInsertId(); // Dapatkan ID pesanan yang baru dibuat

            // 2. Tambahkan item-item dari keranjang ke `order_items` dan kurangi stok produk
            foreach ($_SESSION['cart'] as $product_id => $item) {
                // Pastikan stok tersedia (validasi ganda di sisi server)
                $stmt_stock = $pdo->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE"); // Lock row
                $stmt_stock->execute([$product_id]);
                $current_stock = $stmt_stock->fetchColumn();

                if ($current_stock < $item['quantity']) {
                    $pdo->rollBack(); // Batalkan transaksi
                    $message = 'Stok tidak mencukupi untuk produk: ' . htmlspecialchars($item['name']) . '. Sisa stok: ' . $current_stock;
                    $message_type = 'error';
                    // Kosongkan keranjang untuk produk yang bermasalah atau keseluruhan
                    unset($_SESSION['cart'][$product_id]); // Hapus produk bermasalah dari keranjang
                    header('Location: cart.php?error=stock_issue'); // Redirect kembali ke keranjang
                    exit();
                }

                $stmt_item = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt_item->execute([$order_id, $product_id, $item['quantity'], $item['price']]);

                // Kurangi stok produk
                $stmt_update_stock = $pdo->prepare("UPDATE products SET stock = stock - ?, updated_at = NOW() WHERE id = ?");
                $stmt_update_stock->execute([$item['quantity'], $product_id]);
            }

            $pdo->commit(); // Commit transaksi
            
            // Hapus keranjang setelah pesanan berhasil
            unset($_SESSION['cart']);

            $message = 'Pesanan Anda berhasil dibuat! Nomor Pesanan: #' . $order_id . '.';
            $message_type = 'success';
            // Redirect ke halaman detail pesanan atau riwayat pesanan
            header('Location: my_orders.php?order_id=' . $order_id . '&status=success');
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback jika ada kesalahan
            $message = 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}


// Sertakan header tampilan (ini sudah berisi <html>, <head>, dan <body> pembuka)
include __DIR__ . '/includes/header.php';
?>

    <main class="container">
        <h1>Checkout Pesanan Anda</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            <div class="checkout-form-section">
                <h2>Informasi Pengiriman & Pembayaran</h2>
                <form action="checkout.php" method="POST">
                    <input type="hidden" name="action" value="place_order">
                    
                    <div class="form-group">
                        <label for="shipping_address">Alamat Pengiriman:</label>
                        <textarea id="shipping_address" name="shipping_address" required><?php echo htmlspecialchars($user_info['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="phone">Nomor Telepon:</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>" required>
                        </div>

                    <div class="form-group">
                        <label>Metode Pembayaran:</label>
                        <div class="payment-methods">
                            <label>
                                <input type="radio" name="payment_method" value="Transfer Bank" required checked> Transfer Bank
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="COD"> Cash On Delivery (COD)
                            </label>
                            </div>
                    </div>

                    <button type="submit" class="button">Konfirmasi Pesanan</button>
                </form>
            </div>

            <div class="checkout-summary-section">
                <h2>Ringkasan Pesanan</h2>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                        <div class="summary-item">
                            <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo htmlspecialchars($item['quantity']); ?>)</span>
                            <span>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="summary-total">
                        <span>Subtotal:</span>
                        <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                    </div>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="cart.php" class="button" style="background-color: #6c757d;">Kembali ke Keranjang</a>
                    </p>
                <?php else: ?>
                    <p style="text-align: center;">Keranjang Anda kosong. Tidak ada yang bisa di-checkout.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php
// Sertakan footer tampilan (ini akan berisi </body> dan </html> penutup)
include __DIR__ . '/includes/footer.php';
?>
