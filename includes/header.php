<?php
// includes/header.php
// Di sini Anda bisa menempatkan navigasi, logo, dll.
// Untuk saat ini, kita biarkan sederhana.

// Pastikan session sudah dimulai, biasanya dari functions.php yang di-require pertama
// Jika belum, bisa ditambahkan di sini: session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'TOKO THRIFTING SUCI'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    </head>
<body>
<header>
    <nav>
        <div class="logo">
            <a href="index.php">TOKO THRIFTING SUCI</a>
        </div>
        <ul>
            <li><a href="products.php">Produk</a></li>
            <li><a href="cart.php">Keranjang</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="my_orders.php">Pesanan Saya</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Daftar</a></li>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li><a href="admin/dashboard.php">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>