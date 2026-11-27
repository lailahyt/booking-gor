<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Services\BookingService;
use App\Support\Minutes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CourtListingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index()
    {
        $courts = Court::active()->orderBy('name')->get();

        // Satu query untuk semua lapangan: booking hari ini yang menghalangi jam.
        $bookingsToday = Booking::where('booking_date', today()->toDateString())
            ->blocking()
            ->get(['court_id', 'start_time', 'end_time'])
            ->groupBy('court_id');

        // Jumlah pilihan jam mulai yang masih kosong hari ini (paket terpendek),
        // dengan aturan yang sama dengan halaman jadwal.
        $freeToday = [];

        foreach ($courts as $court) {
            $starts = $this->buildStarts(
                $court,
                today(),
                $this->defaultHours(),
                $bookingsToday->get($court->id, collect())
            );

            $freeToday[$court->id] = collect($starts)->where('status', 'free')->count();
        }

        return view('courts.index', compact('courts', 'freeToday'));
    }

    public function show(Request $request, Court $court)
    {
        abort_unless($court->is_active, 404);

        $date = $this->resolveDate($request->query('date'));
        $hours = $this->resolveHours($request->query('hours'));

        $bookings = Booking::where('court_id', $court->id)
            ->where('booking_date', $date->toDateString())
            ->blocking()
            ->get(['start_time', 'end_time']);

        return view('courts.show', [
            'court' => $court,
            'courts' => Court::active()->orderBy('name')->get(),
            'date' => $date,
            'hours' => $hours,
            'packages' => collect(BookingService::PACKAGE_HOURS)
                ->mapWithKeys(fn($h) => [$h => $court->priceForHours($h)])
                ->all(),
            'days' => collect(range(0, BookingService::MAX_DAYS_AHEAD))->map(fn($i) => today()->addDays($i)),
            'starts' => $this->buildStarts($court, $date, $hours, $bookings),
            'minDate' => today()->toDateString(),
            'maxDate' => today()->addDays(BookingService::MAX_DAYS_AHEAD)->toDateString(),
        ]);
    }

    /**
     * Daftar jam mulai satu lapangan pada satu tanggal untuk satu paket (3 atau 4 jam).
     * Jam mulai yang blok durasinya tidak muat sebelum jam tutup tidak ikut ditampilkan.
     * Status tiap jam mulai: free (kosong), booked (blok bentrok dengan booking lain),
     * past (sudah dimulai hari ini).
     * Bentrok: mulai_lama < selesai_blok DAN selesai_lama > mulai_blok (dalam menit).
     */
    private function buildStarts(Court $court, Carbon $date, int $hours, Collection $bookings): array
    {
        $nowMinute = now()->hour * 60 + now()->minute;

        $taken = $bookings->map(fn($b) => [
            Minutes::parse($b->start_time) ?? 0,
            Minutes::parse($b->end_time) ?? 0,
        ]);

        $starts = [];

        foreach ($this->bookings->startTimes($court, $hours) as $start) {
            $startMinute = Minutes::parse($start);
            $endMinute = $startMinute + $hours * 60;

            $isPast = $date->isToday() && $startMinute <= $nowMinute;
            $isBooked = $taken->contains(fn($range) => $range[0] < $endMinute && $range[1] > $startMinute);

            $starts[] = [
                'start' => $start,
                'end' => Minutes::format($endMinute),
                'status' => $isBooked ? 'booked' : ($isPast ? 'past' : 'free'),
            ];
        }

        return $starts;
    }

    // Paket bawaan saat pelanggan belum memilih: yang terpendek
    private function defaultHours(): int
    {
        return min(BookingService::PACKAGE_HOURS);
    }

    private function resolveHours(mixed $input): int
    {
        $hours = filter_var($input, FILTER_VALIDATE_INT);

        return in_array($hours, BookingService::PACKAGE_HOURS, true) ? $hours : $this->defaultHours();
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
            || $date->gt(today()->addDays(BookingService::MAX_DAYS_AHEAD))
        ) {
            return today();
        }

        return $date;
    }
}
