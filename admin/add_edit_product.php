<?php
// admin/add_edit_product.php

require_once __DIR__ . '/../includes/functions.php';
$pdo = getDbConnection();

$page_title = 'Tambah/Edit Produk';
$message = '';
$message_type = '';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = [
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'category_id' => '',
    'image' => '', // Nama file gambar yang sudah ada
    'status' => 'available'
];
$is_edit = false;

// Ambil data produk jika dalam mode edit
if ($product_id > 0) {
    $is_edit = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $fetched_product = $stmt->fetch();
        if ($fetched_product) {
            $product = array_merge($product, $fetched_product);
            $page_title = 'Edit Produk: ' . htmlspecialchars($product['name']);
        } else {
            $message = 'Produk tidak ditemukan.';
            $message_type = 'error';
            $is_edit = false; // Kembali ke mode tambah jika produk tidak ditemukan
        }
    } catch (PDOException $e) {
        $message = 'Terjadi kesalahan database saat mengambil data produk: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Ambil semua kategori untuk dropdown
$categories = [];
try {
    $stmt_cat = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt_cat->fetchAll();
} catch (PDOException $e) {
    $message = 'Terjadi kesalahan database saat mengambil kategori: ' . $e->getMessage();
    $message_type = 'error';
}


// Proses form submit (Tambah atau Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $status = $_POST['status'];
    $current_image = $_POST['current_image'] ?? ''; // Gambar yang sudah ada

    $upload_dir = __DIR__ . '/../assets/images/products/';
    $image_filename = $current_image; // Default menggunakan gambar yang sudah ada

    // Validasi input
    if (empty($name) || empty($description) || $price <= 0 || $stock < 0 || $category_id <= 0) {
        $message = 'Harap isi semua field yang wajib dan pastikan nilai valid.';
        $message_type = 'error';
    } elseif (!in_array($status, ['available', 'sold_out', 'hidden'])) {
        $message = 'Status produk tidak valid.';
        $message_type = 'error';
    } else {
        // Proses upload gambar
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_ext)) {
                $new_file_name = uniqid('product_', true) . '.' . $file_ext;
                $target_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_name, $target_path)) {
                    // Hapus gambar lama jika ada dan bukan placeholder
                    if ($current_image && $current_image !== 'product_placeholder.jpg' && file_exists($upload_dir . $current_image)) {
                        unlink($upload_dir . $current_image);
                    }
                    $image_filename = $new_file_name;
                } else {
                    $message = 'Gagal mengupload gambar.';
                    $message_type = 'error';
                }
            } else {
                $message = 'Format gambar tidak didukung. Hanya JPG, JPEG, PNG, GIF yang diizinkan.';
                $message_type = 'error';
            }
        }
    }

    // Jika tidak ada error dari validasi input atau upload gambar
    if ($message_type !== 'error') {
        try {
            if ($is_edit) {
                // Update produk yang sudah ada
                $stmt = $pdo->prepare("
                    UPDATE products
                    SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $description, $price, $stock, $category_id, $image_filename, $status, $product_id]);
                $message = 'Produk berhasil diperbarui.';
                $message_type = 'success';
            } else {
                // Tambah produk baru
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, description, price, stock, category_id, image, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $description, $price, $stock, $category_id, $image_filename, $status]);
                $product_id = $pdo->lastInsertId(); // Dapatkan ID produk baru
                $is_edit = true; // Beralih ke mode edit setelah tambah
                $message = 'Produk baru berhasil ditambahkan.';
                $message_type = 'success';
            }
            // Update data $product untuk tampilan form
            $product['name'] = $name;
            $product['description'] = $description;
            $product['price'] = $price;
            $product['stock'] = $stock;
            $product['category_id'] = $category_id;
            $product['image'] = $image_filename;
            $product['status'] = $status;


        } catch (PDOException $e) {
            $message = 'Terjadi kesalahan database: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1><?php echo htmlspecialchars($page_title); ?></h1>

<?php if ($message): ?>
    <div class="message <?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="admin-form-container">
    <form action="add_edit_product.php<?php echo $is_edit ? '?id=' . htmlspecialchars($product_id) : ''; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Nama Produk:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Deskripsi:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="price">Harga:</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        </div>
        <div class="form-group">
            <label for="stock">Stok:</label>
            <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
        </div>
        <div class="form-group">
            <label for="category_id">Kategori:</label>
            <select id="category_id" name="category_id" required>
                <option value="">Pilih Kategori</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="image">Gambar Produk:</label>
            <input type="file" id="image" name="image" accept="image/*">
            <?php if ($product['image']): ?>
                <div class="current-image">
                    <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image">
                    <span>File saat ini: <?php echo htmlspecialchars($product['image']); ?></span>
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                </div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="available" <?php echo ($product['status'] === 'available') ? 'selected' : ''; ?>>Tersedia</option>
                <option value="sold_out" <?php echo ($product['status'] === 'sold_out') ? 'selected' : ''; ?>>Habis</option>
                <option value="hidden" <?php echo ($product['status'] === 'hidden') ? 'selected' : ''; ?>>Tersembunyi</option>
            </select>
        </div>
        <button type="submit"><?php echo $is_edit ? 'Perbarui Produk' : 'Tambah Produk'; ?></button>
    </form>
    <div class="back-link">
        <a href="products.php" class="button" style="background-color: #6c757d;">Kembali ke Daftar Produk</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>