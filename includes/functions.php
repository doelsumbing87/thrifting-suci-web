<?php

// includes/functions.php

session_start(); // Mulai sesi PHP

// Tambahkan ini di bagian atas functions.php
require_once __DIR__ . '/../vendor/autoload.php'; // Memuat autoload Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Fungsi untuk mendapatkan koneksi PDO ke database.
 * Menggunakan Singleton pattern secara sederhana.
 * @return PDO
 */
function getDbConnection() {
    static $pdo = null; // Menyimpan koneksi agar hanya dibuat sekali

    if ($pdo === null) {
        $config = require __DIR__ . '/../config/database.php'; // Path disesuaikan

        $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $config['user'], $config['password'], $options);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * Fungsi untuk mengirim email menggunakan PHPMailer.
 * @param string $toEmail Email penerima
 * @param string $toName Nama penerima
 * @param string $subject Subjek email
 * @param string $body Isi email (HTML)
 * @param string $altBody Isi email alternatif (plain text)
 * @return bool True jika berhasil, false jika gagal
 */
function sendEmail($toEmail, $toName, $subject, $body, $altBody = '') {
    $mailConfig = require __DIR__ . '/../config/mail.php';

    $mail = new PHPMailer(true); // Instance PHPMailer, true = enable exceptions
    try {
        // Server settings
        $mail->isSMTP();                                            // Set mailer to use SMTP
        $mail->Host       = $mailConfig['host'];                   // Specify main and backup SMTP servers
        $mail->SMTPAuth   = $mailConfig['smtp_auth'];              // Enable SMTP authentication
        $mail->Username   = $mailConfig['username'];               // SMTP username
        $mail->Password   = $mailConfig['password'];               // SMTP password
        $mail->SMTPSecure = $mailConfig['smtp_secure'];            // Enable TLS encryption, `ssl` also accepted
        $mail->Port       = $mailConfig['port'];                   // TCP port to connect to

        // Recipients
        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($toEmail, $toName);     // Add a recipient

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Untuk debugging, bisa ditampilkan errornya.
        // error_log("Mailer Error: {$mail->ErrorInfo}"); // Log error ke file log PHP
        return false;
    }
}

// Anda bisa menambahkan fungsi-fungsi umum lainnya di sini di masa mendatang,
// seperti fungsi untuk sanitasi input, redirect, dll.