-- Hapus database jika sudah ada untuk memulai dari awal (OPSIONAL, HANYA UNTUK PENGEMBANGAN)
-- DROP DATABASE IF EXISTS `toko_thrifting_suci`;

-- Buat database baru
CREATE DATABASE IF NOT EXISTS `toko_thrifting_suci`;

-- Gunakan database yang baru dibuat
USE `toko_thrifting_suci`;

--
-- Struktur Tabel untuk `users`
--
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user', -- 'user' untuk pembeli, 'admin' untuk administrator
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur Tabel untuk `categories`
--
CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur Tabel untuk `products`
--
CREATE TABLE `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL, -- Foreign Key ke tabel categories
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `stock` INT(11) NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL, -- Path ke gambar produk
  `status` ENUM('available', 'sold_out', 'hidden') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur Tabel untuk `orders`
--
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL, -- Foreign Key ke tabel users (pembeli)
  `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
  `shipping_address` TEXT NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `payment_status` ENUM('paid', 'unpaid', 'refunded') NOT NULL DEFAULT 'unpaid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur Tabel untuk `order_items`
--
CREATE TABLE `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL, -- Foreign Key ke tabel orders
  `product_id` INT(11) NOT NULL, -- Foreign Key ke tabel products
  `quantity` INT(11) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL, -- Harga produk saat dipesan (bisa beda dari harga sekarang)
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tambahkan tabel password_resets untuk fitur lupa password
--
CREATE TABLE `password_resets` (
  `email` VARCHAR(100) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL, -- Waktu kadaluarsa token
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tambahkan indeks pada kolom email untuk pencarian lebih cepat
CREATE INDEX idx_password_resets_email ON password_resets (email);


--
-- Menambahkan data dummy untuk pengujian (OPSIONAL)
--

-- Data Dummy untuk `users`
INSERT INTO `users` (`username`, `email`, `password`, `fullname`, `address`, `phone`, `role`) VALUES
('admin_suci', 'admin@suci.com', '$2y$10$wN3W.3.S6J0N4G8J5O9J2.R5N1G0J3L5O4W3T7I4E1A9D0K1H6G5F2E1C0B9A8', 'Admin Toko Suci', 'Jl. Admin No. 1, Bandar Lampung', '081234567890', 'admin'),
('user_pembeli', 'user@pembeli.com', '$2y$10$wN3W.3.S6J0N4G8J5O9J2.R5N1G0J3L5O4W3T7I4E1A9D0K1H6G5F2E1C0B9A8', 'Pembeli Pertama', 'Jl. Pembeli No. 10, Lampung', '085678901234', 'user');

-- Catatan: Password '$2y$10$wN3W.3.S6J0N4G8J5O9J2.R5N1G0J3L5O4W3T7I4E1A9D0K1H6G5F2E1C0B9A8' adalah hasil hash untuk 'password123'.
-- Anda harus selalu menghash password sebelum menyimpannya di database di aplikasi nyata.

-- Data Dummy untuk `categories`
INSERT INTO `categories` (`name`, `description`) VALUES
('Kemeja', 'Berbagai jenis kemeja thrift pria dan wanita.'),
('Celana', 'Celana jeans, chino, cargo, dll.'),
('Jaket', 'Jaket denim, bomber, parka, dll.'),
('Sweater', 'Sweater dan hoodie.'),
('Aksesoris', 'Topi, syal, dll.');

-- Data Dummy untuk `products`
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `stock`, `image`, `status`) VALUES
(1, 'Kemeja Flanel Vintage', 'Kemeja flanel unisex, kondisi sangat baik, ukuran L.', 75000.00, 5, 'kemeja_flanel.jpg', 'available'),
(2, 'Jeans Levis 501 Bekas', 'Jeans Levi\'s 501 original bekas, kondisi 8/10, ukuran 32.', 150000.00, 2, 'jeans_levis.jpg', 'available'),
(3, 'Jaket Denim Oversize', 'Jaket denim tebal, model oversize, kondisi 9/10, ukuran XL.', 120000.00, 3, 'jaket_denim.jpg', 'available'),
(1, 'Kemeja Denim Biru', 'Kemeja denim ringan, cocok untuk sehari-hari, ukuran M.', 60000.00, 8, 'kemeja_denim.jpg', 'available'),
(4, 'Sweater Rajut Wol', 'Sweater rajut hangat, motif unik, ukuran L.', 90000.00, 4, 'sweater_rajut.jpg', 'available'),
(2, 'Celana Chino Slim Fit', 'Celana chino bekas kualitas tinggi, warna krem, ukuran 30.', 85000.00, 6, 'celana_chino.jpg', 'available'),
(3, 'Hoodie Pullover Hitam', 'Hoodie tebal dengan kantong depan, kondisi bagus, ukuran M.', 95000.00, 3, 'hoodie_hitam.jpg', 'available');


-- Data Dummy untuk `orders` (Diasumsikan user_id 2 adalah 'user_pembeli')
INSERT INTO `orders` (`user_id`, `total_amount`, `status`, `shipping_address`, `payment_method`, `payment_status`) VALUES
(2, 225000.00, 'processing', 'Jl. Pembeli No. 10, Lampung, Kode Pos 35123', 'Transfer Bank', 'paid'),
(2, 60000.00, 'pending', 'Jl. Pembeli No. 10, Lampung, Kode Pos 35123', 'COD', 'unpaid');

-- Data Dummy untuk `order_items` (mengacu ke order_id dan product_id dari data dummy di atas)
INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 75000.00), -- Order 1, Kemeja Flanel Vintage
(1, 2, 1, 150000.00), -- Order 1, Jeans Levis 501
(2, 4, 1, 60000.00);  -- Order 2, Kemeja Denim Biru