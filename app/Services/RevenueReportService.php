<?php

namespace App\Services;

use App\Models\Booking;

class RevenueReportService
{
    /**
     * Laporan pendapatan untuk rentang tanggal main (kedua ujung ikut dihitung).
     *
     * Aturan pendapatan sama dengan dashboard: booking yang sudah ditandai lunas
     * dan tidak dibatalkan, dihitung berdasarkan tanggal main.
     *
     * @param string $from Format Y-m-d
     * @param string $to   Format Y-m-d
     */
    public function build(string $from, string $to, ?int $courtId = null): array
    {
        $bookings = Booking::with('court')
            ->whereBetween('booking_date', [$from, $to])
            ->where('payment_status', Booking::PAYMENT_PAID)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->when($courtId, fn($query) => $query->where('court_id', $courtId))
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        // jam main dihitung dari menit (selesai - mulai), jadi jam setengah ikut benar
        $minutesOf = fn(Booking $booking) => $booking->durationMinutes();

        $perCourt = $bookings
            ->groupBy('court_id')
            ->map(fn($rows) => [
                'court' => $rows->first()->court->name,
                'count' => $rows->count(),
                'hours' => $this->toHours((int) $rows->sum($minutesOf)),
                'revenue' => (int) $rows->sum('total_price'),
            ])
            ->sortByDesc('revenue')
            ->values();

        return [
            'bookings' => $bookings,
            'count' => $bookings->count(),
            'hours' => $this->toHours((int) $bookings->sum($minutesOf)),
            'revenue' => (int) $bookings->sum('total_price'),
            'perCourt' => $perCourt,
        ];
    }

    // Menit menjadi jam: bilangan bulat bila pas (paket 3 dan 4 jam), selain itu satu desimal
    private function toHours(int $minutes): int|float
    {
        return $minutes % 60 === 0 ? intdiv($minutes, 60) : round($minutes / 60, 1);
    }
}
