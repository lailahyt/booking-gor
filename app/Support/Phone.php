<?php

namespace App\Support;

class Phone
{
    // 08..., 628..., atau +628... diikuti 8 sampai 12 angka
    public const PATTERN = '/^(\+62|62|0)8[0-9]{8,12}$/';

    /**
     * Ubah ke format 62... (tanpa +), format yang dipakai tautan wa.me.
     * Contoh: 081234567890 menjadi 6281234567890.
     */
    public static function normalize(string $phone): string
    {
        $phone = ltrim(preg_replace('/[^0-9+]/', '', $phone), '+');

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}
