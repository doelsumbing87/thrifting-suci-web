<?php
// products.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();

$message = '';
$message_type = '';

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0; // 0 untuk semua kategori

try {
    // Ambil semua kategori untuk filter
    $stmt_categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt_categories->fetchAll();

    // Bangun query produk dinamis
    $sql = "SELECT p.id, p.name, p.price, p.image, c.name AS category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'available'";
    $params = [];

    if (!empty($search_query)) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }

    if ($category_filter > 0) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_filter;
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt_products = $pdo->prepare($sql);
    $stmt_products->execute($params);
    $products = $stmt_products->fetchAll();

} catch (PDOException $e) {
    $message = 'Terjadi kesalahan database saat mengambil produk: ' . $e->getMessage();
    $message_type = 'error';
    $products = []; // Pastikan array kosong jika ada error
    $categories = [];
}

// Sertakan header tampilan
include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php // Header akan tampil di sini ?>

    <main class="container">
        <h1>Semua Produk Kami</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="filter-sort-section">
            <form action="products.php" method="GET">
                <div class="form-group">
                    <label for="search">Cari:</label>
                    <input type="text" id="search" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="form-group">
                    <label for="category">Kategori:</label>
                    <select id="category" name="category">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="button">Filter</button>
                <?php if (!empty($search_query) || $category_filter > 0): ?>
                    <a href="products.php" class="button" style="background-color: #6c757d;">Reset Filter</a>
                <?php endif; ?>
            </form>
        </div>


        <div class="product-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?: 'product_placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p>Kategori: <?php echo htmlspecialchars($product['category_name']); ?></p>
                        <p>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                        <a href="product_detail.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="button">Detail Produk</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%;">Tidak ada produk yang ditemukan sesuai kriteria Anda.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php // Footer akan tampil di sini ?>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>