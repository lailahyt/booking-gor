<?php

return [
    'name' => env('GOR_NAME', 'GOR Bunyamin'),

    // Nomor WhatsApp admin untuk tombol "Kirim ke WhatsApp".
    // Format 62..., hanya angka (tanpa +, spasi, atau tanda hubung).
    'whatsapp' => env('GOR_WHATSAPP', ''),

    // Member bulanan
    'member' => [
        'fee' => 450000,          // biaya satu periode (rupiah)
        'sessions' => 4,          // jumlah main per periode (1 kali per minggu)
        'max_start_days' => 60,   // sesi pertama maksimal sekian hari ke depan
    ],
];
