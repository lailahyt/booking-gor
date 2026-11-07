<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index(Request $request)
    {
        $date = (string) $request->query('date');
        $showAll = $request->boolean('semua');

        $query = Booking::with('court');

        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            // tanggal tertentu
            $query->where('booking_date', $date);
        } elseif (! $showAll) {
            // bawaan: hari ini dan seterusnya
            $query->where('booking_date', '>=', today()->toDateString());
        }

        if ($request->filled('court_id')) {
            $query->where('court_id', (int) $request->query('court_id'));
        }

        $statuses = [
            Booking::STATUS_PENDING,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_CANCELLED,
            Booking::STATUS_NO_SHOW,
            Booking::STATUS_COMPLETED,
        ];

        if (in_array($request->query('status'), $statuses, true)) {
            $query->where('status', $request->query('status'));
        }

        // riwayat lengkap: terbaru dulu; selain itu: yang paling dekat dulu
        if ($showAll && $date === '') {
            $query->orderByDesc('booking_date')->orderByDesc('start_time');
        } else {
            $query->orderBy('booking_date')->orderBy('start_time');
        }

        $bookings = $query->paginate(15)->withQueryString();

        $pendingCount = Booking::where('status', Booking::STATUS_PENDING)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'courts' => Court::orderBy('name')->get(),
            'pendingCount' => $pendingCount,
            'statusOptions' => [
                Booking::STATUS_PENDING => 'Menunggu Konfirmasi',
                Booking::STATUS_CONFIRMED => 'Dikonfirmasi',
                Booking::STATUS_COMPLETED => 'Selesai',
                Booking::STATUS_NO_SHOW => 'Tidak Datang',
                Booking::STATUS_CANCELLED => 'Dibatalkan',
            ],
        ]);
    }

    public function action(Request $request, Booking $booking, string $action)
    {
        try {
            $message = $this->bookings->applyAdminAction($booking, $action);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }
}
