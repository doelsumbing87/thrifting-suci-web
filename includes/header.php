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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <nav class="main-nav"> <div class="logo">
            <a href="index.php">TOKO THRIFTING SUCI</a>
        </div>
        <div class="hamburger-menu"> <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
        <ul class="nav-links"> <li><a href="products.php"><i class="fas fa-tshirt"></i> Produk</a></li>
            <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="my_orders.php"><i class="fas fa-box-open"></i> Pesanan Saya</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="register.php"><i class="fas fa-user-plus"></i> Daftar</a></li>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li><a href="admin/dashboard.php"><i class="fas fa-user-shield"></i> Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>