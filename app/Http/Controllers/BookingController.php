<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Court;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    /**
     * Halaman ringkasan sebelum konfirmasi.
     * Dibuka lewat GET supaya setelah login/daftar pelanggan kembali ke sini
     * dengan pilihan jam yang sama.
     */
    public function create(Request $request, Court $court)
    {
        $date = (string) $request->query('date');
        $start = (string) $request->query('start');
        $end = (string) $request->query('end');

        try {
            $hours = $this->bookings->checkRange($court, $date, $start, $end);

            if (! $this->bookings->isAvailable($court, $date, $start, $end)) {
                throw new BookingException('Maaf, jam yang kamu pilih sudah terisi. Silakan pilih jam lain.');
            }
        } catch (BookingException $e) {
            return $this->backToSchedule($court, $date, $e->getMessage());
        }

        return view('bookings.create', [
            'court' => $court,
            'date' => Carbon::parse($date),
            'start' => $start,
            'end' => $end,
            'hours' => $hours,
            'total' => $hours * $court->price_per_hour,
            'confirmWindow' => BookingService::CONFIRM_WINDOW_HOURS,
        ]);
    }

    public function store(Request $request, Court $court)
    {
        $data = $request->validate([
            'date' => ['required', 'string'],
            'start' => ['required', 'string'],
            'end' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();

        if (! $user->phone) {
            return back()->withInput()->with(
                'error',
                'Akunmu belum punya nomor WhatsApp, jadi booking belum bisa dibuat. Daftar dengan akun baru atau isi nomor di data akun.'
            );
        }

        try {
            $booking = $this->bookings->create(
                $user,
                $court,
                $data['date'],
                $data['start'],
                $data['end'],
                $data['notes'] ?? null
            );
        } catch (BookingException $e) {
            return $this->backToSchedule($court, $data['date'], $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking berhasil dibuat. Kirim detailnya ke admin lewat WhatsApp agar cepat dikonfirmasi.');
    }

    public function index(Request $request)
    {
        $bookings = Booking::with('court')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        try {
            $this->bookings->cancelByCustomer($booking, $request->user());
        } catch (BookingException $e) {
            return redirect()->route('bookings.show', $booking)->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking berhasil dibatalkan.');
    }

    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();

        // pelanggan hanya boleh melihat booking miliknya sendiri; admin boleh semuanya
        abort_unless($booking->user_id === $user->id || $user->isAdmin(), 403);

        $booking->load('court');

        return view('bookings.show', compact('booking'));
    }

    private function backToSchedule(Court $court, string $date, string $message)
    {
        return redirect()
            ->route('courts.show', ['court' => $court, 'date' => $date])
            ->with('error', $message);
    }
}
