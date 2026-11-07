<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CourtListingController extends Controller
{
    public const MAX_DAYS_AHEAD = 14;

    public function index()
    {
        $courts = Court::active()->orderBy('name')->get();

        return view('courts.index', compact('courts'));
    }

    public function show(Request $request, Court $court)
    {
        abort_unless($court->is_active, 404);

        $date = $this->resolveDate($request->query('date'));

        $bookings = Booking::where('court_id', $court->id)
            ->where('booking_date', $date->toDateString())
            ->blocking()
            ->get(['start_time', 'end_time']);

        $open = (int) substr($court->open_time, 0, 2);
        $close = (int) substr($court->close_time, 0, 2);

        $slots = [];

        for ($hour = $open; $hour < $close; $hour++) {
            $start = sprintf('%02d:00:00', $hour);
            $end = sprintf('%02d:00:00', $hour + 1);

            $isPast = $date->isToday() && $hour <= now()->hour;
            $isBooked = $bookings->contains(
                fn($b) => $b->start_time < $end && $b->end_time > $start
            );

            $slots[] = [
                'label' => substr($start, 0, 5) . ' - ' . substr($end, 0, 5),
                'start' => $start,
                'end' => $end,
                'status' => $isBooked ? 'booked' : ($isPast ? 'past' : 'free'),
            ];
        }

        return view('courts.show', [
            'court' => $court,
            'date' => $date,
            'slots' => $slots,
            'minDate' => today()->toDateString(),
            'maxDate' => today()->addDays(self::MAX_DAYS_AHEAD)->toDateString(),
        ]);
    }

    private function resolveDate(?string $input): Carbon
    {
        if (! $input || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
            return today();
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $input)->startOfDay();
        } catch (\Throwable $e) {
            return today();
        }

        // tolak tanggal yang tidak nyata (misal 2026-02-31) atau di luar rentang
        if (
            $date->format('Y-m-d') !== $input
            || $date->lt(today())
            || $date->gt(today()->addDays(self::MAX_DAYS_AHEAD))
        ) {
            return today();
        }

        return $date;
    }
}
