<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat booking tidak memenuhi aturan (jam bentrok, durasi salah, dst).
 * Pesannya aman ditampilkan langsung ke pelanggan.
 */
class BookingException extends RuntimeException
{
}
