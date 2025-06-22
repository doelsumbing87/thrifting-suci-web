<?php

// config/payment.php

return [
    'bank_transfers' => [
        'bca' => [
            'name' => 'Bank Central Asia (BCA)',
            'account_name' => 'PT. TOKO THRIFTING SUCI',
            'account_number' => '1234567890',
            'branch' => 'KCP Lampung'
        ],
        'bri' => [
            'name' => 'Bank Rakyat Indonesia (BRI)',
            'account_name' => 'PT. TOKO THRIFTING SUCI',
            'account_number' => '0987654321',
            'branch' => 'Cabang Bandar Lampung'
        ],
        'bni' => [
            'name' => 'Bank Negara Indonesia (BNI)',
            'account_name' => 'PT. TOKO THRIFTING SUCI',
            'account_number' => '1122334455',
            'branch' => 'KCU Tanjung Karang'
        ]
    ],
    'cod_info' => 'Pembayaran dilakukan secara tunai saat barang diterima.'
];
