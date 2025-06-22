<?php
// admin/dashboard.php

require_once __DIR__ . '/../includes/functions.php'; // Sesuaikan path ke functions.php
$pdo = getDbConnection();

$page_title = 'Dashboard Admin';
$total_products = 0;
$total_orders = 0;
$total_users = 0;
$pending_orders = 0;

try {
    // Hitung total produk
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    // Hitung total pesanan
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt->fetchColumn();

    // Hitung total pengguna (hanya role 'user')
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $total_users = $stmt->fetchColumn();

    // Hitung pesanan pending
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $pending_orders = $stmt->fetchColumn();

} catch (PDOException $e) {
    // Handle error, mungkin tampilkan pesan di dashboard atau log
    error_log("Error fetching admin stats: " . $e->getMessage());
    // Biarkan nilai default 0 jika ada error
}


include __DIR__ . '/includes/header.php';
?>

<h1>Dashboard Admin</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Produk</h3>
        <p><?php echo htmlspecialchars($total_products); ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Pesanan</h3>
        <p><?php echo htmlspecialchars($total_orders); ?></p>
    </div>
    <div class="stat-card">
        <h3>Pesanan Pending</h3>
        <p><?php echo htmlspecialchars($pending_orders); ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Pelanggan</h3>
        <p><?php echo htmlspecialchars($total_users); ?></p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>