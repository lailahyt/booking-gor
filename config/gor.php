<?php

return [
    'name' => env('GOR_NAME', 'GOR Bulutangkis'),

    // Nomor WhatsApp admin untuk tombol "Kirim ke WhatsApp".
    // Format 62..., hanya angka (tanpa +, spasi, atau tanda hubung).
    'whatsapp' => env('GOR_WHATSAPP', ''),
];
