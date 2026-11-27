<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Member;
use App\Support\Minutes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MemberService
{
    public function __construct(private BookingService $bookings) {}

    /**
     * Mendaftarkan satu periode member: 1 baris members + N booking nyata
     * (N = config gor.member.sessions), hari dan jam sama tiap minggu.
     *
     * Semua atau tidak sama sekali: kalau ada satu tanggal bentrok, tidak ada yang dibuat
     * dan pesan error menyebut semua tanggal yang bentrok.
     *
     * @param string $startsOn Tanggal sesi pertama, format Y-m-d
     * @param string $start    Jam mulai, format HH:MM
     */
    public function register(
        Court $court,
        string $name,
        string $phone,
        string $startsOn,
        string $start,
        int $hours,
        int $fee,
        bool $paid = false,
        ?string $notes = null
    ): Member {
        if (! in_array($hours, BookingService::PACKAGE_HOURS, true)) {
            throw new BookingException(
                'Paket harus ' . implode(' jam atau ', BookingService::PACKAGE_HOURS) . ' jam.'
            );
        }

        if ($fee < 1) {
            throw new BookingException('Biaya member harus lebih dari nol.');
        }

        $sessions = (int) config('gor.member.sessions', 4);
        $firstDayLimit = (int) config('gor.member.max_start_days', 60);

        $startMinute = Minutes::parse($start);

        if ($startMinute === null || strlen($start) !== 5) {
            throw new BookingException('Format jam tidak valid, contohnya 19:30.');
        }

        $endMinute = $startMinute + $hours * 60;

        // jam selesai tidak boleh melewati tengah malam
        if ($endMinute >= 24 * 60) {
            throw new BookingException('Jam selesai melewati tengah malam. Pilih jam mulai yang lebih awal.');
        }

        $end = Minutes::format($endMinute);

        try {
            $first = Carbon::createFromFormat('Y-m-d', $startsOn)->startOfDay();
        } catch (\Throwable) {
            throw new BookingException('Tanggal mulai tidak valid.');
        }

        if ($first->format('Y-m-d') !== $startsOn) {
            throw new BookingException('Tanggal mulai tidak valid.');
        }

        // Tanggal tiap sesi: sesi pertama + 7 hari kali urutannya
        $dates = [];

        for ($i = 0; $i < $sessions; $i++) {
            $dates[] = $first->copy()->addWeeks($i)->toDateString();
        }

        // Aturan dasar tiap sesi (jam operasional, langkah waktu, sudah lewat, lapangan aktif).
        // Batas 14 hari tidak berlaku; sesi pertama maksimal $firstDayLimit hari ke depan,
        // sesi berikutnya mengikuti (selisih 7 hari tiap sesi).
        foreach ($dates as $i => $date) {
            try {
                $this->bookings->checkRange(
                    $court,
                    $date,
                    $start,
                    $end,
                    allowOngoing: true,
                    maxDaysAhead: $firstDayLimit + 7 * $i
                );
            } catch (BookingException $e) {
                throw new BookingException(
                    'Sesi ke-' . ($i + 1) . ' (' . $this->dateLabel($date) . '): ' . $e->getMessage()
                );
            }
        }

        $startTime = $start . ':00';
        $endTime = $end . ':00';

        return DB::transaction(function () use (
            $court,
            $name,
            $phone,
            $first,
            $hours,
            $fee,
            $paid,
            $notes,
            $sessions,
            $dates,
            $startTime,
            $endTime
        ) {
            // Kunci lapangan, sama seperti BookingService::create()
            $lockedCourt = Court::whereKey($court->id)->lockForUpdate()->firstOrFail();

            if (! $lockedCourt->is_active) {
                throw new BookingException('Lapangan ini sedang tidak tersedia.');
            }

            // Kumpulkan SEMUA tanggal yang bentrok sebelum membuat apa pun
            $conflicts = [];

            foreach ($dates as $date) {
                if (Booking::overlapping($lockedCourt->id, $date, $startTime, $endTime)->exists()) {
                    $conflicts[] = $this->dateLabel($date);
                }
            }

            if ($conflicts !== []) {
                throw new BookingException(
                    'Jadwal member bentrok dengan booking lain pada: ' . implode('; ', $conflicts)
                        . '. Tidak ada booking yang dibuat. Pilih hari atau jam lain.'
                );
            }

            $member = Member::create([
                'name' => $name,
                'phone' => $phone,
                'court_id' => $lockedCourt->id,
                'starts_on' => $first->toDateString(),
                'start_time' => $startTime,
                'duration_hours' => $hours,
                'sessions' => $sessions,
                'monthly_fee' => $fee,
                'payment_status' => $paid ? Member::PAYMENT_PAID : Member::PAYMENT_UNPAID,
                'notes' => $notes,
            ]);

            // Biaya dibagi rata; seluruh sisa pembagian masuk ke sesi pertama
            $each = intdiv($fee, $sessions);
            $remainder = $fee - $each * $sessions;

            foreach ($dates as $i => $date) {
                Booking::create([
                    'code' => $this->bookings->generateCode(),
                    'user_id' => null,
                    'member_id' => $member->id,
                    'customer_name' => $name,
                    'customer_phone' => $phone,
                    'court_id' => $lockedCourt->id,
                    'booking_date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'total_price' => $each + ($i === 0 ? $remainder : 0),
                    'status' => Booking::STATUS_CONFIRMED,
                    'payment_method' => Booking::METHOD_ON_SITE,
                    'payment_status' => $paid ? Booking::PAYMENT_PAID : Booking::PAYMENT_UNPAID,
                    'source' => Booking::SOURCE_MEMBER,
                    'expires_at' => null,
                    'notes' => null,
                ]);
            }

            return $member;
        });
    }

    /**
     * Tandai seluruh periode lunas: semua sesi yang belum dibatalkan ikut lunas.
     * Sesi yang sudah dibatalkan tidak disentuh (pendapatannya memang tidak dihitung).
     */
    public function markPaid(Member $member): string
    {
        return DB::transaction(function () use ($member) {
            $locked = Member::whereKey($member->id)->lockForUpdate()->firstOrFail();

            if ($locked->isPaid()) {
                throw new BookingException('Periode member ini sudah tercatat lunas.');
            }

            $updated = Booking::where('member_id', $locked->id)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->update(['payment_status' => Booking::PAYMENT_PAID]);

            if ($updated === 0) {
                throw new BookingException('Semua sesi periode ini sudah dibatalkan, jadi tidak ada yang bisa dilunasi.');
            }

            $locked->update(['payment_status' => Member::PAYMENT_PAID]);

            return 'Pembayaran member dicatat lunas untuk ' . $updated . ' sesi.';
        });
    }

    private function dateLabel(string $date): string
    {
        return Carbon::parse($date)->locale('id')->translatedFormat('l, j F Y');
    }
}
