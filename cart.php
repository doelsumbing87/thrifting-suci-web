<?php
// cart.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();

$message = '';
$message_type = '';

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle aksi dari form keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    switch ($action) {
        case 'update_quantity':
            // Logika untuk memperbarui kuantitas semua item yang dikirim dari form
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $p_id => $item_data) {
                    $p_id = (int)$p_id;
                    $new_quantity = (int)$item_data['quantity'];

                    if ($p_id > 0 && isset($_SESSION['cart'][$p_id])) {
                        try {
                            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
                            $stmt->execute([$p_id]);
                            $product_db = $stmt->fetch();

                            if ($product_db && $new_quantity > 0 && $new_quantity <= $product_db['stock']) {
                                $_SESSION['cart'][$p_id]['quantity'] = $new_quantity;
                                $message = 'Kuantitas produk berhasil diperbarui.';
                                $message_type = 'success';
                            } elseif ($new_quantity > $product_db['stock']) {
                                $message = 'Stok tidak mencukupi untuk produk: ' . htmlspecialchars($_SESSION['cart'][$p_id]['name']) . '. Sisa stok: ' . $product_db['stock'];
                                $message_type = 'error';
                            } else {
                                // Jika kuantitas 0 atau negatif, hapus item
                                unset($_SESSION['cart'][$p_id]);
                                $message = 'Produk dihapus dari keranjang.';
                                $message_type = 'success';
                            }
                        } catch (PDOException $e) {
                            $message = 'Terjadi kesalahan saat validasi stok: ' . $e->getMessage();
                            $message_type = 'error';
                            break;
                        }
                    }
                }
            }
            break;

        case 'remove_item':
            if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                $message = 'Produk berhasil dihapus dari keranjang.';
                $message_type = 'success';
            } else {
                $message = 'Produk tidak ditemukan di keranjang.';
                $message_type = 'error';
            }
            break;

        case 'clear_cart':
            $_SESSION['cart'] = [];
            $message = 'Keranjang berhasil dikosongkan.';
            $message_type = 'success';
            break;
    }
}

// Hitung total belanja
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Sertakan header tampilan (ini sudah berisi <html>, <head>, dan <body> pembuka)
include __DIR__ . '/includes/header.php';
?>

    <main class="container">
        <h1>Keranjang Belanja Anda</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['cart'])): ?>
            <form action="cart.php" method="POST">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Kuantitas</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                            <tr>
                                <td>
                                    <img src="assets/images/products/<?php echo htmlspecialchars($item['image'] ?: 'product_placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div class="cart-item-name">
                                        <a href="product_detail.php?id=<?php echo htmlspecialchars($product_id); ?>">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </div>
                                </td>
                                <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td class="cart-quantity">
                                    <input type="number" name="items[<?php echo htmlspecialchars($product_id); ?>][quantity]" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1">
                                    <input type="hidden" name="items[<?php echo htmlspecialchars($product_id); ?>][product_id]" value="<?php echo htmlspecialchars($product_id); ?>">
                                </td>
                                <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                <td class="cart-item-actions">
                                    <button type="submit" name="action" value="remove_item" formmethod="POST" style="margin-right: 5px;" onclick="return confirm('Yakin ingin menghapus produk ini dari keranjang?');">Hapus</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-summary">
                    Total Belanja: <span style="color: #28a745;">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                </div>
                <div class="cart-actions">
                    <button type="submit" name="action" value="update_quantity" class="button">Perbarui Kuantitas</button>
                    <button type="submit" name="action" value="clear_cart" class="button clear-cart" onclick="return confirm('Yakin ingin mengosongkan keranjang?');">Kosongkan Keranjang</button>
                    <a href="checkout.php" class="button checkout">Lanjutkan ke Pembayaran</a>
                </div>
            </form>
        <?php else: ?>
            <p style="text-align: center; padding: 50px;">Keranjang belanja Anda kosong. Yuk, <a href="products.php">mulai belanja</a>!</p>
        <?php endif; ?>
    </main>

<?php
// Sertakan footer tampilan (ini akan berisi </body> dan </html> penutup)
include __DIR__ . '/includes/footer.php';
?>
