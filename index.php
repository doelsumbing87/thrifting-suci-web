<?php
// index.php

require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection(); // Dapatkan koneksi database

$message = '';
$message_type = '';

// Coba mendapatkan koneksi database dan produk unggulan
try {
    // Ambil beberapa produk terbaru/unggulan dari database
    $stmt_products = $pdo->query("SELECT id, name, price, image FROM products WHERE status = 'available' ORDER BY created_at DESC LIMIT 6");
    $featured_products = $stmt_products->fetchAll();

} catch (Exception $e) {
    // Jika koneksi gagal atau query bermasalah, tampilkan error dan set produk kosong
    $message = "Koneksi Database Gagal atau Produk tidak dapat dimuat: " . $e->getMessage();
    $message_type = 'error';
    $featured_products = [];
}

// Sertakan header tampilan
include __DIR__ . '/includes/header.php';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php // Konten header akan ada di sini dari includes/header.php ?>

    <main>
        <section class="hero">
            <h1>Temukan Gaya Unik Anda di TOKO THRIFTING SUCI</h1>
            <p>Kualitas Terbaik, Harga Terjangkau, Pilihan Tak Terbatas.</p>
            <a href="products.php" class="button">Lihat Semua Produk</a>
        </section>

        <?php if ($message): ?>
            <section class="message-section" style="margin: 0 20px 30px;">
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="featured-products">
            <h2>Produk Unggulan</h2>
            <div class="product-grid">
                <?php if (count($featured_products) > 0): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?: 'product_placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                            <a href="product_detail.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="button">Detail</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%;">Belum ada produk unggulan yang tersedia saat ini.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

<?php
// Sertakan footer (ini akan menjadi tampilan umum website)
include __DIR__ . '/includes/footer.php';
?>
</body>
</html>