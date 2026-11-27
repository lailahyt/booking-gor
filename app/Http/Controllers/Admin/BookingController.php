<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Services\BookingService;
use App\Support\Minutes;
use App\Support\Phone;
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

    public function create()
    {
        $courts = Court::active()->orderBy('name')->get();

        // Daftar jam mulai yang sah per lapangan dan per paket, dibuat server
        // dari jam buka, jam tutup, dan langkah waktu tiap lapangan.
        // Formulir hanya menukar isi pilihan jam; server tetap memeriksa ulang saat disimpan.
        $startOptions = [];

        foreach ($courts as $court) {
            foreach (BookingService::PACKAGE_HOURS as $hours) {
                $startOptions[$court->id][$hours] = array_map(
                    fn($start) => [
                        'start' => $start,
                        'end' => Minutes::format(Minutes::parse($start) + $hours * 60),
                    ],
                    $this->bookings->startTimes($court, $hours)
                );
            }
        }

        return view('admin.bookings.create', [
            'courts' => $courts,
            'minDate' => today()->toDateString(),
            'maxDate' => today()->addDays(BookingService::MAX_DAYS_AHEAD)->toDateString(),
            'packageHours' => BookingService::PACKAGE_HOURS,
            'startOptions' => $startOptions,
        ]);
    }

    public function store(Request $request)
    {
        // rapikan nomor (hapus spasi, tanda hubung, kurung) sebelum divalidasi
        $request->merge([
            'customer_phone' => preg_replace('/[\s\-().]/', '', (string) $request->input('customer_phone')),
        ]);

        $data = $request->validate([
            'court_id' => ['required', 'exists:courts,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'duration' => ['required', 'integer', 'in:' . implode(',', BookingService::PACKAGE_HOURS)],
            'start' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'regex:' . Phone::PATTERN],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'court_id.required' => 'Pilih lapangan.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date_format' => 'Tanggal tidak valid.',
            'duration.required' => 'Pilih paket.',
            'duration.in' => 'Paket harus ' . implode(' jam atau ', BookingService::PACKAGE_HOURS) . ' jam.',
            'start.required' => 'Pilih jam mulai.',
            'start.regex' => 'Jam mulai tidak valid.',
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'customer_phone.required' => 'Nomor WhatsApp wajib diisi.',
            'customer_phone.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890',
        ]);

        $court = Court::findOrFail($data['court_id']);
        $endMinute = Minutes::parse($data['start']) + (int) $data['duration'] * 60;

        // jam selesai tidak boleh melewati tengah malam
        if ($endMinute >= 24 * 60) {
            return back()->withInput()->with(
                'error',
                'Jam selesai melewati tengah malam. Pilih jam mulai yang lebih awal.'
            );
        }

        try {
            $booking = $this->bookings->createManual(
                $court,
                $data['date'],
                $data['start'],
                Minutes::format($endMinute),
                trim($data['customer_name']),
                Phone::normalize($data['customer_phone']),
                $data['notes'] ?? null,
                $request->boolean('paid')
            );
        } catch (BookingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.bookings.index', ['date' => $booking->booking_date->toDateString()])
            ->with('success', 'Booking manual dibuat dengan kode ' . $booking->code . '.');
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
