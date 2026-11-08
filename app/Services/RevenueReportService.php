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

        // jam main = selisih jam selesai dan jam mulai (booking selalu jam bulat)
        $hoursOf = fn(Booking $booking) => (int) substr($booking->end_time, 0, 2)
            - (int) substr($booking->start_time, 0, 2);

        $perCourt = $bookings
            ->groupBy('court_id')
            ->map(fn($rows) => [
                'court' => $rows->first()->court->name,
                'count' => $rows->count(),
                'hours' => (int) $rows->sum($hoursOf),
                'revenue' => (int) $rows->sum('total_price'),
            ])
            ->sortByDesc('revenue')
            ->values();

        return [
            'bookings' => $bookings,
            'count' => $bookings->count(),
            'hours' => (int) $bookings->sum($hoursOf),
            'revenue' => (int) $bookings->sum('total_price'),
            'perCourt' => $perCourt,
        ];
    }
}
