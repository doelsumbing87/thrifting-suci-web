<?php
// product_detail.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();

$message = '';
$message_type = '';

$product = null;
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name AS category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.id = ? AND p.status = 'available'
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            $message = 'Produk tidak ditemukan atau tidak tersedia.';
            $message_type = 'error';
        }
    } catch (PDOException $e) {
        $message = 'Terjadi kesalahan database: ' . $e->getMessage();
        $message_type = 'error';
    }
} else {
    $message = 'ID Produk tidak valid.';
    $message_type = 'error';
}

// Proses penambahan ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $item_product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($item_product_id === $product_id && $quantity > 0 && $product) {
        // Validasi stok sebelum menambahkan ke keranjang
        if ($quantity > $product['stock']) {
            $message = 'Stok tidak mencukupi. Hanya tersedia ' . htmlspecialchars($product['stock']) . ' unit.';
            $message_type = 'error';
        } else {
            // Logika menambahkan ke keranjang
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // Periksa apakah produk sudah ada di keranjang
            if (isset($_SESSION['cart'][$item_product_id])) {
                // Pastikan total kuantitas tidak melebihi stok
                if (($_SESSION['cart'][$item_product_id]['quantity'] + $quantity) > $product['stock']) {
                    $message = 'Tidak bisa menambahkan. Total kuantitas di keranjang akan melebihi stok yang tersedia (' . htmlspecialchars($product['stock']) . ').';
                    $message_type = 'error';
                } else {
                    $_SESSION['cart'][$item_product_id]['quantity'] += $quantity;
                    $message = htmlspecialchars($product['name']) . ' telah ditambahkan ke keranjang.';
                    $message_type = 'success';
                }
            } else {
                // Tambahkan produk baru ke keranjang
                $_SESSION['cart'][$item_product_id] = [
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'quantity' => $quantity
                ];
                $message = htmlspecialchars($product['name']) . ' telah ditambahkan ke keranjang.';
                $message_type = 'success';
            }
        }
    } else {
        $message = 'Gagal menambahkan produk ke keranjang. Kuantitas tidak valid atau produk tidak ditemukan.';
        $message_type = 'error';
    }
}

// Sertakan header tampilan (ini sudah berisi <html>, <head>, dan <body> pembuka)
include __DIR__ . '/includes/header.php';
?>

    <main class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($product): ?>
            <div class="product-detail-container">
                <div class="product-detail-image">
                    <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?: 'product_placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-detail-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <span class="category">Kategori: <?php echo htmlspecialchars($product['category_name']); ?></span>
                    <p class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                    <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <p class="stock">Stok: <?php echo htmlspecialchars($product['stock']); ?></p>

                    <?php if ($product['stock'] > 0): ?>
                        <form action="product_detail.php?id=<?php echo htmlspecialchars($product['id']); ?>" method="POST">
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                            <div class="quantity-input">
                                <label for="quantity">Jumlah:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock']); ?>">
                            </div>
                            <button type="submit" class="button">Tambahkan ke Keranjang</button>
                        </form>
                    <?php else: ?>
                        <p class="message error">Produk ini sedang tidak tersedia (Stok Habis).</p>
                    <?php endif; ?>
                    <p style="margin-top: 20px;"><a href="products.php">Kembali ke Daftar Produk</a></p>
                </div>
            </div>
        <?php elseif ($message_type === 'error'): ?>
            <p style="text-align: center; padding: 50px;">Produk tidak dapat ditampilkan. <?php echo $message; ?></p>
            <p style="text-align: center;"><a href="products.php" class="button">Kembali ke Daftar Produk</a></p>
        <?php endif; ?>
    </main>

<?php
// Sertakan footer tampilan (ini akan berisi </body> dan </html> penutup)
include __DIR__ . '/includes/footer.php';
<<<<<<< HEAD
?>
=======
?>
>>>>>>> 80e41601e95216a9f3a88498772bc1c8787d7d61
