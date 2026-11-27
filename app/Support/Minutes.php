<?php

namespace App\Support;

/**
 * Waktu dalam menit sejak tengah malam, misalnya 19:30 = 1170.
 * Semua perhitungan jam mulai, jam selesai, dan durasi memakai menit
 * supaya jam setengah (:30) ikut benar.
 */
final class Minutes
{
    /**
     * Ubah 'HH:MM' atau 'HH:MM:SS' menjadi menit. Mengembalikan null bila formatnya salah.
     */
    public static function parse(?string $time): ?int
    {
        if ($time === null || ! preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/', $time, $m)) {
            return null;
        }

        return ((int) $m[1]) * 60 + (int) $m[2];
    }

    /**
     * Ubah menit menjadi 'HH:MM'.
     */
    public static function format(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
