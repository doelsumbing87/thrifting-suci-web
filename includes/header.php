<?php
// includes/header.php
// Di sini Anda bisa menempatkan navigasi, logo, dll.
// Untuk saat ini, kita biarkan sederhana.
?>
<header>
    <nav>
        <div class="logo">
            <a href="index.php">TOKO THRIFTING SUCI</a>
        </div>
        <ul>
            <li><a href="products.php">Produk</a></li>
            <li><a href="cart.php">Keranjang</a></li>
            <?php if (isset($_SESSION['user_id'])): // Contoh sederhana, nanti akan diatur di login ?>
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