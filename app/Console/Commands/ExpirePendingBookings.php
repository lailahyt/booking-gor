<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpirePendingBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Batalkan booking menunggu yang melewati batas waktu konfirmasi';

    public function handle(BookingService $bookings): int
    {
        $count = $bookings->expirePending();

        $this->info("{$count} booking dibatalkan.");

        return self::SUCCESS;
    }
}
