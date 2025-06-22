// assets/js/script.js

// === Fungsionalitas Checkout Page ===
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const radioTransferBank = document.getElementById('radio-transfer-bank');
    const bankOptionsDiv = document.getElementById('bank-options');

    // Fungsi untuk menampilkan/menyembunyikan opsi bank
    function toggleBankOptions() {
        // Cek apakah radio "Transfer Bank" itu sendiri yang dipilih, atau salah satu bank di dalamnya
        const isTransferBankSelected = radioTransferBank.checked ||
                                       document.querySelector('input[name="payment_method"][value^="Transfer Bank -"]:checked');
        
        if (isTransferBankSelected) {
            bankOptionsDiv.style.display = 'block';
            // Pastikan salah satu bank spesifik terpilih jika 'Transfer Bank' generik dipilih
            if (radioTransferBank.checked && !document.querySelector('input[name="payment_method"][value^="Transfer Bank -"]:checked')) {
                // Pilih bank pertama (BCA) secara default jika 'Transfer Bank' generik dipilih
                bankOptionsDiv.querySelector('input[type="radio"][value="Transfer Bank - BCA"]').checked = true;
            }
        } else {
            bankOptionsDiv.style.display = 'none';
        }
    }

    // Set initial state saat halaman dimuat
    toggleBankOptions();

    // Tambahkan event listeners untuk setiap perubahan pada radio button metode pembayaran
    paymentMethods.forEach(radio => {
        radio.addEventListener('change', toggleBankOptions);
    });

    // === Tambahkan fungsionalitas JavaScript lainnya di sini di masa mendatang ===

    // Contoh: Fungsionalitas untuk toggle detail pesanan di admin/orders.php
    // Meskipun fungsi toggleOrderDetails() ada di admin/orders.php,
    // jika Anda ingin memindahkan JS dari PHP, Anda bisa definisikan di sini.
    // Misalnya:
    // window.toggleOrderDetails = function(orderId) {
    //     var row = document.getElementById('order-detail-' + orderId);
    //     if (row.style.display === 'none' || row.style.display === '') {
    //         row.style.display = 'table-row';
    //     } else {
    //         row.style.display = 'none';
    //     }
    // }
});