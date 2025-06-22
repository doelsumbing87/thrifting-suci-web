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
            $new_quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
                // Ambil stok terbaru dari database untuk validasi
                try {
                    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
                    $stmt->execute([$product_id]);
                    $product_db = $stmt->fetch();

                    if ($product_db && $new_quantity > 0 && $new_quantity <= $product_db['stock']) {
                        $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                        $message = 'Kuantitas produk berhasil diperbarui.';
                        $message_type = 'success';
                    } elseif ($new_quantity > $product_db['stock']) {
                        $message = 'Stok tidak mencukupi untuk kuantitas yang diminta.';
                        $message_type = 'error';
                    } else {
                        // Jika kuantitas 0 atau negatif, anggap ingin menghapus
                        unset($_SESSION['cart'][$product_id]);
                        $message = 'Produk dihapus dari keranjang.';
                        $message_type = 'success';
                    }
                } catch (PDOException $e) {
                    $message = 'Terjadi kesalahan saat validasi stok: ' . $e->getMessage();
                    $message_type = 'error';
                }
            } else {
                $message = 'Produk tidak ditemukan di keranjang.';
                $message_type = 'error';
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

include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* CSS Tambahan untuk Cart */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .cart-table th, .cart-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: middle;
        }
        .cart-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .cart-table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .cart-item-name a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .cart-item-name a:hover {
            text-decoration: underline;
        }
        .cart-quantity input[type="number"] {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .cart-item-actions button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .cart-item-actions button:hover {
            background-color: #c82333;
        }
        .cart-summary {
            margin-top: 30px;
            text-align: right;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .cart-actions .button {
            padding: 12px 20px;
            font-size: 1rem;
        }
        .button.clear-cart {
            background-color: #ffc107;
            color: #333;
        }
        .button.clear-cart:hover {
            background-color: #e0a800;
        }
        .button.checkout {
            background-color: #28a745;
        }
        .button.checkout:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

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
                                    <button type="submit" name="action" value="remove_item" formaction="cart.php" formmethod="POST" onclick="return confirm('Yakin ingin menghapus produk ini dari keranjang?');">Hapus</button>
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

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>