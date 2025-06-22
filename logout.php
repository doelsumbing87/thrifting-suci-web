<?php
// logout.php

require_once __DIR__ . '/includes/functions.php'; // Memulai session_start()

// Hapus semua variabel sesi
$_SESSION = [];

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login
header('Location: login.php');
exit();
?>