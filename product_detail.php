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
        // Logika menambahkan ke keranjang
        // Keranjang akan disimpan di $_SESSION['cart']
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Periksa apakah produk sudah ada di keranjang
        if (isset($_SESSION['cart'][$item_product_id])) {
            $_SESSION['cart'][$item_product_id]['quantity'] += $quantity;
        } else {
            // Tambahkan produk baru ke keranjang
            $_SESSION['cart'][$item_product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }

        $message = htmlspecialchars($product['name']) . ' telah ditambahkan ke keranjang.';
        $message_type = 'success';
    } else {
        $message = 'Gagal menambahkan produk ke keranjang. Kuantitas tidak valid atau produk tidak ditemukan.';
        $message_type = 'error';
    }
}


include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name']) : 'Detail Produk'; ?> - TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* CSS Tambahan untuk Product Detail */
        .product-detail-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 30px;
        }
        .product-detail-image {
            flex: 1;
            min-width: 300px;
            text-align: center;
        }
        .product-detail-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .product-detail-info {
            flex: 2;
            min-width: 400px;
        }
        .product-detail-info h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #333;
        }
        .product-detail-info .category {
            font-size: 1rem;
            color: #666;
            margin-bottom: 15px;
            display: block;
        }
        .product-detail-info .price {
            font-size: 2rem;
            color: #007bff;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .product-detail-info .description {
            margin-bottom: 20px;
            line-height: 1.8;
            color: #555;
        }
        .product-detail-info .stock {
            font-size: 1rem;
            color: #666;
            margin-bottom: 20px;
        }
        .quantity-input {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .quantity-input label {
            margin-right: 10px;
            font-weight: bold;
        }
        .quantity-input input[type="number"] {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

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

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>