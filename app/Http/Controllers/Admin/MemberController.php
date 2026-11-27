<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Member;
use App\Services\BookingService;
use App\Services\MemberService;
use App\Support\Minutes;
use App\Support\Phone;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        private BookingService $bookings,
        private MemberService $members
    ) {}

    public function index()
    {
        $members = Member::with('court')
            ->withCount(['bookings as active_sessions' => fn($q) => $q->where('status', '!=', Booking::STATUS_CANCELLED)])
            ->orderByDesc('starts_on')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.members.index', ['members' => $members]);
    }

    public function create(Request $request)
    {
        $courts = Court::active()->orderBy('name')->get();

        // Daftar jam mulai yang sah per lapangan dan per paket (sama seperti Booking Manual)
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

        // "Perpanjang": isi form dari periode lama. Tanggal mulai = hari yang sama,
        // satu minggu setelah sesi terakhir periode lama (atau hari yang sama terdekat bila sudah lewat).
        $prefill = [];
        $previous = $request->filled('perpanjang') ? Member::find((int) $request->query('perpanjang')) : null;

        if ($previous) {
            $next = $previous->endsOn()->addWeek();

            while ($next->lt(today())) {
                $next->addWeek();
            }

            $prefill = [
                'name' => $previous->name,
                'phone' => $previous->phone,
                'court_id' => $previous->court_id,
                'duration' => $previous->duration_hours,
                'start' => substr($previous->start_time, 0, 5),
                'monthly_fee' => $previous->monthly_fee,
                'starts_on' => $next->toDateString(),
                'notes' => $previous->notes,
            ];
        }

        return view('admin.members.create', [
            'courts' => $courts,
            'minDate' => today()->toDateString(),
            'maxDate' => today()->addDays((int) config('gor.member.max_start_days', 60))->toDateString(),
            'packageHours' => BookingService::PACKAGE_HOURS,
            'startOptions' => $startOptions,
            'sessions' => (int) config('gor.member.sessions', 4),
            'defaultFee' => (int) config('gor.member.fee', 450000),
            'prefill' => $prefill,
            'previous' => $previous,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => preg_replace('/[\s\-().]/', '', (string) $request->input('phone')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:' . Phone::PATTERN],
            'court_id' => ['required', 'exists:courts,id'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'duration' => ['required', 'integer', 'in:' . implode(',', BookingService::PACKAGE_HOURS)],
            'start' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'monthly_fee' => ['required', 'integer', 'min:1000', 'max:100000000'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama member wajib diisi.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890',
            'court_id.required' => 'Pilih lapangan.',
            'starts_on.required' => 'Tanggal sesi pertama wajib diisi.',
            'starts_on.date_format' => 'Tanggal tidak valid.',
            'duration.required' => 'Pilih paket.',
            'duration.in' => 'Paket harus ' . implode(' jam atau ', BookingService::PACKAGE_HOURS) . ' jam.',
            'start.required' => 'Pilih jam mulai.',
            'start.regex' => 'Jam mulai tidak valid.',
            'monthly_fee.required' => 'Biaya wajib diisi.',
            'monthly_fee.integer' => 'Biaya harus berupa angka tanpa titik atau koma.',
            'monthly_fee.min' => 'Biaya minimal Rp1.000.',
        ]);

        $court = Court::findOrFail($data['court_id']);

        try {
            $member = $this->members->register(
                $court,
                trim($data['name']),
                Phone::normalize($data['phone']),
                $data['starts_on'],
                $data['start'],
                (int) $data['duration'],
                (int) $data['monthly_fee'],
                $request->boolean('paid'),
                $data['notes'] ?? null
            );
        } catch (BookingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.members.show', $member)
            ->with('success', 'Member dicatat. ' . $member->sessions . ' sesi sudah dikunci di jadwal.');
    }

    public function show(Member $member)
    {
        $member->load([
            'court',
            'bookings' => fn($q) => $q->orderBy('booking_date'),
        ]);

        return view('admin.members.show', ['member' => $member]);
    }

    public function pay(Member $member)
    {
        try {
            $message = $this->members->markPaid($member);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }
}
