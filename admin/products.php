<?php
// admin/products.php

require_once __DIR__ . '/../includes/functions.php';
$pdo = getDbConnection();

$page_title = 'Manajemen Produk';
$message = '';
$message_type = '';

// Proses aksi DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $product_id_to_delete = (int)$_POST['product_id'];

    try {
        // Ambil nama file gambar sebelum menghapus record
        $stmt_img = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt_img->execute([$product_id_to_delete]);
        $image_to_delete = $stmt_img->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$product_id_to_delete])) {
            // Hapus file gambar jika ada dan bukan placeholder
            if ($image_to_delete && $image_to_delete !== 'product_placeholder.jpg' && file_exists(__DIR__ . '/../assets/images/products/' . $image_to_delete)) {
                unlink(__DIR__ . '/../assets/images/products/' . $image_to_delete);
            }
            $message = 'Produk berhasil dihapus.';
            $message_type = 'success';
        } else {
            $message = 'Gagal menghapus produk.';
            $message_type = 'error';
        }
    } catch (PDOException $e) {
        $message = 'Terjadi kesalahan database saat menghapus produk: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Ambil semua produk dari database
$products = [];
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Terjadi kesalahan database saat mengambil daftar produk: ' . $e->getMessage();
    $message_type = 'error';
}

include __DIR__ . '/includes/header.php';
?>

<h1>Manajemen Produk</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="add-new-button">
    <a href="add_edit_product.php" class="button">Tambah Produk Baru</a>
</div>

<?php if (!empty($products)): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Gambar</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                    <td>
                        <img src="../assets/images/products/<?php echo htmlspecialchars($product['image'] ?: 'product_placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="admin-product-img">
                    </td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                    <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($product['stock']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($product['status'])); ?></td>
                    <td class="actions-buttons">
                        <a href="add_edit_product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="button edit-btn">Edit</a>
                        <form action="products.php" method="POST" style="display: inline-block;">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                            <button type="submit" class="button delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align: center; margin-top: 50px;">Belum ada produk yang terdaftar.</p>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>