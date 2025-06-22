<?php
// admin/includes/header.php

// Pastikan session sudah dimulai (harusnya sudah di functions.php)
// require_once __DIR__ . '/../../includes/functions.php'; // Ini tidak perlu jika sudah di main file

// Proteksi akses: hanya admin yang bisa masuk
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php?error=unauthorized_access');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - TOKO THRIFTING SUCI</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <style>
        /* CSS Khusus Admin Panel */
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f8f9fa;
        }
        .admin-wrapper {
            display: flex;
            flex: 1;
        }
        .admin-sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .admin-sidebar h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
        }
        .admin-sidebar ul li {
            margin-bottom: 10px;
        }
        .admin-sidebar ul li a {
            display: block;
            color: #dee2e6;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .admin-sidebar ul li a:hover, .admin-sidebar ul li a.active {
            background-color: #007bff;
            color: #fff;
        }
        .admin-content {
            flex: 1;
            padding: 30px;
        }
        .admin-content h1 {
            color: #343a40;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            border-left: 5px solid #007bff;
        }
        .stat-card h3 {
            margin-top: 0;
            color: #6c757d;
        }
        .stat-card p {
            font-size: 2.5rem;
            font-weight: bold;
            color: #343a40;
            margin: 10px 0 0;
        }
        /* Admin table styles (re-use from cart/my_orders but make more specific) */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden; /* For rounded corners */
        }
        .admin-table th, .admin-table td {
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            text-align: left;
            vertical-align: middle;
        }
        .admin-table th {
            background-color: #e9ecef;
            font-weight: bold;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        .admin-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .admin-table tbody tr:hover {
            background-color: #e2e6ea;
        }
        .admin-table .actions-buttons .button {
            padding: 6px 10px;
            font-size: 0.85rem;
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
            border-radius: 4px;
        }
        .admin-table .actions-buttons .edit-btn {
            background-color: #ffc107;
            color: #333;
        }
        .admin-table .actions-buttons .edit-btn:hover {
            background-color: #e0a800;
        }
        .admin-table .actions-buttons .delete-btn {
            background-color: #dc3545;
            color: #fff;
        }
        .admin-table .actions-buttons .delete-btn:hover {
            background-color: #c82333;
        }
        .admin-product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            vertical-align: middle;
            margin-right: 10px;
        }
        .add-new-button {
            margin-bottom: 20px;
            text-align: right;
        }
        /* Form admin */
        .admin-form-container {
            max-width: 800px; /* Lebih lebar untuk admin form */
            margin-top: 30px;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-form-container .form-group label {
            font-weight: bold;
            color: #333;
        }
        .admin-form-container .form-group input[type="file"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: auto;
        }
        .admin-form-container button[type="submit"] {
            width: auto;
            padding: 10px 25px;
            font-size: 1rem;
            margin-top: 20px;
        }
        .admin-form-container .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        .admin-form-container .current-image {
            margin-top: 10px;
            margin-bottom: 15px;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
            display: flex;
            align-items: center;
        }
        .admin-form-container .current-image img {
            max-width: 100px;
            height: auto;
            margin-right: 15px;
            border-radius: 4px;
        }
        .admin-form-container .current-image span {
            font-style: italic;
            color: #555;
        }

    </style>
</head>
<body>
    <div class="admin-wrapper">
        <div class="admin-sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'add_edit_product.php') ? 'active' : ''; ?>">Produk</a></li>
                <li><a href="orders.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">Pesanan</a></li>
                <li><a href="users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">Pengguna</a></li>
                <li><a href="../index.php">Kembali ke Situs</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="admin-content">
            <?php // Konten spesifik halaman admin akan dimulai di sini ?>