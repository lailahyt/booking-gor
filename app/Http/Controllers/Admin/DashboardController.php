<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();
        $todayDate = $today->toDateString();
        $monthStart = $today->copy()->startOfMonth()->toDateString();
        $monthEnd = $today->copy()->endOfMonth()->toDateString();

        // Booking yang dihitung "terpakai": semua kecuali yang dibatalkan.
        $notCancelled = [
            Booking::STATUS_PENDING,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_NO_SHOW,
        ];

        // Jadwal hari ini, urut dari jam paling awal.
        $todayBookings = Booking::with('court')
            ->where('booking_date', $todayDate)
            ->whereIn('status', $notCancelled)
            ->orderBy('start_time')
            ->get();

        // Booking menunggu yang belum kedaluwarsa.
        $pendingCount = Booking::where('status', Booking::STATUS_PENDING)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();

        // Pendapatan = booking yang sudah lunas dan tidak dibatalkan,
        // dihitung berdasarkan tanggal main.
        $revenue = fn($query) => $query
            ->where('payment_status', Booking::PAYMENT_PAID)
            ->where('status', '!=', Booking::STATUS_CANCELLED);

        $revenueToday = $revenue(Booking::where('booking_date', $todayDate))->sum('total_price');

        $revenueMonth = $revenue(
            Booking::whereBetween('booking_date', [$monthStart, $monthEnd])
        )->sum('total_price');

        // Jumlah booking per lapangan bulan ini (yang dikonfirmasi, selesai, atau tidak datang).
        $courtUsage = Court::withCount(['bookings as month_bookings' => function ($q) use ($monthStart, $monthEnd) {
            $q->whereBetween('booking_date', [$monthStart, $monthEnd])
                ->whereIn('status', [
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_COMPLETED,
                    Booking::STATUS_NO_SHOW,
                ]);
        }])
            ->orderByDesc('month_bookings')
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'today' => $today,
            'todayDate' => $todayDate,
            'todayBookings' => $todayBookings,
            'pendingCount' => $pendingCount,
            'revenueToday' => (int) $revenueToday,
            'revenueMonth' => (int) $revenueMonth,
            'courtUsage' => $courtUsage,
        ]);
    }
}
