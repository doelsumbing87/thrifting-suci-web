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
    $payment_method = trim($_POST['payment_method']); // Ini akan berisi "Transfer Bank - BCA" atau "COD"
    $phone_number = trim($_POST['phone']); 

    if (empty($shipping_address) || empty($payment_method) || empty($phone_number)) {
        $message = 'Alamat pengiriman, nomor telepon, dan metode pembayaran harus diisi.';
        $message_type = 'error';
    } else {
        try {
            $pdo->beginTransaction(); 

            $stmt_order = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status, payment_status)
                VALUES (?, ?, ?, ?, 'pending', 'unpaid')
            ");
            $stmt_order->execute([$user_id, $subtotal, $shipping_address, $payment_method]);
            $order_id = $pdo->lastInsertId(); 

            foreach ($_SESSION['cart'] as $product_id => $item) {
                $stmt_stock = $pdo->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE"); 
                $stmt_stock->execute([$product_id]);
                $current_stock = $stmt_stock->fetchColumn();

                if ($current_stock < $item['quantity']) {
                    $pdo->rollBack(); 
                    $message = 'Stok tidak mencukupi untuk produk: ' . htmlspecialchars($item['name']) . '. Sisa stok: ' . $current_stock;
                    $message_type = 'error';
                    unset($_SESSION['cart'][$product_id]); 
                    header('Location: cart.php?error=stock_issue'); 
                    exit();
                }

                $stmt_item = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt_item->execute([$order_id, $product_id, $item['quantity'], $item['price']]);

                $stmt_update_stock = $pdo->prepare("UPDATE products SET stock = stock - ?, updated_at = NOW() WHERE id = ?");
                $stmt_update_stock->execute([$item['quantity'], $product_id]);
            }

            $pdo->commit(); 
            
            unset($_SESSION['cart']);

            $message = 'Pesanan Anda berhasil dibuat! Nomor Pesanan: #' . $order_id . '.';
            $message_type = 'success';
            // Redirect ke halaman detail pesanan atau riwayat pesanan
            header('Location: my_orders.php?order_id=' . $order_id . '&status=success');
            exit();

        } catch (PDOException | Exception $e) { 
            $pdo->rollBack(); 
            $message = 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}


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
                                <input type="radio" name="payment_method" value="COD" required checked> Cash On Delivery (COD)
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="placeholder_bank_transfer" id="radio-transfer-bank"> Transfer Bank
                            </label>
                            <div id="bank-options" style="margin-left: 20px;">
                                <label>
                                    <input type="radio" name="payment_method" value="Transfer Bank - BCA">
                                    <img src="assets/images/payment_methods/bca.png" alt="Logo BCA"> BCA
                                </label>
                                <label>
                                    <input type="radio" name="payment_method" value="Transfer Bank - BRI">
                                    <img src="assets/images/payment_methods/bri.png" alt="Logo BRI"> BRI
                                </label>
                                <label>
                                    <input type="radio" name="payment_method" value="Transfer Bank - BNI">
                                    <img src="assets/images/payment_methods/bni.png" alt="Logo BNI"> BNI
                                </label>
                            </div>
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
include __DIR__ . '/includes/footer.php';
?>