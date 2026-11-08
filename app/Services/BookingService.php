<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public const MIN_HOURS = 1;
    public const MAX_HOURS = 4;
    public const MAX_DAYS_AHEAD = 14;
    public const MAX_ACTIVE_BOOKINGS = 3;
    public const CONFIRM_WINDOW_HOURS = 2;

    /**
     * Periksa aturan dasar booking. Jam diberikan dalam format 'HH:00'.
     * Mengembalikan jumlah jam, atau melempar BookingException.
     */
    public function checkRange(Court $court, string $date, string $start, string $end, bool $allowOngoing = false): int
    {
        if (! $court->is_active) {
            throw new BookingException('Lapangan ini sedang tidak tersedia.');
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new BookingException('Tanggal tidak valid.');
        }

        try {
            $day = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            throw new BookingException('Tanggal tidak valid.');
        }

        // menolak tanggal yang tidak nyata, misalnya 2026-02-31
        if ($day->format('Y-m-d') !== $date) {
            throw new BookingException('Tanggal tidak valid.');
        }

        if ($day->lt(today())) {
            throw new BookingException('Tanggal yang dipilih sudah lewat.');
        }

        if ($day->gt(today()->addDays(self::MAX_DAYS_AHEAD))) {
            throw new BookingException('Pemesanan hanya bisa untuk ' . self::MAX_DAYS_AHEAD . ' hari ke depan.');
        }

        $pattern = '/^([01]\d|2[0-3]):00$/';

        if (! preg_match($pattern, $start) || ! preg_match($pattern, $end)) {
            throw new BookingException('Jam harus berupa jam bulat, misalnya 19:00.');
        }

        $startHour = (int) substr($start, 0, 2);
        $endHour = (int) substr($end, 0, 2);

        if ($endHour <= $startHour) {
            throw new BookingException('Jam selesai harus setelah jam mulai.');
        }

        $hours = $endHour - $startHour;

        if ($hours < self::MIN_HOURS || $hours > self::MAX_HOURS) {
            throw new BookingException(
                'Durasi booking minimal ' . self::MIN_HOURS . ' jam dan maksimal ' . self::MAX_HOURS . ' jam.'
            );
        }

        $open = (int) substr($court->open_time, 0, 2);
        $close = (int) substr($court->close_time, 0, 2);

        if ($startHour < $open || $endHour > $close) {
            throw new BookingException(sprintf(
                'Jam yang dipilih di luar jam operasional (%02d:00 - %02d:00).',
                $open,
                $close
            ));
        }

        if ($day->isToday()) {
            // pelanggan: jam yang sudah dimulai tidak bisa dipesan.
            // admin ($allowOngoing): jam yang sedang berjalan masih boleh (pelanggan datang langsung).
            $passed = $allowOngoing ? $endHour <= now()->hour : $startHour <= now()->hour;

            if ($passed) {
                throw new BookingException('Jam yang dipilih sudah lewat.');
            }
        }

        return $hours;
    }

    /**
     * Pengecekan cepat tanpa penguncian, untuk halaman ringkasan.
     * Pengecekan yang menentukan tetap dilakukan di create().
     */
    public function isAvailable(Court $court, string $date, string $start, string $end): bool
    {
        return ! Booking::overlapping($court->id, $date, $start . ':00', $end . ':00')->exists();
    }

    /**
     * Membuat booking. Seluruh proses berjalan dalam satu transaksi.
     */
    public function create(
        User $user,
        Court $court,
        string $date,
        string $start,
        string $end,
        ?string $notes = null
    ): Booking {
        $hours = $this->checkRange($court, $date, $start, $end);

        $startTime = $start . ':00';
        $endTime = $end . ':00';

        return DB::transaction(function () use ($user, $court, $date, $startTime, $endTime, $hours, $notes) {
            // Kunci baris lapangan: booking untuk lapangan yang sama diproses satu per satu.
            // Kalau dua orang menekan tombol bersamaan, yang kedua menunggu yang pertama selesai,
            // lalu cek bentrok di bawah akan melihat booking yang pertama.
            $lockedCourt = Court::whereKey($court->id)->lockForUpdate()->firstOrFail();

            if (! $lockedCourt->is_active) {
                throw new BookingException('Lapangan ini sedang tidak tersedia.');
            }

            // booking menunggu yang sudah kedaluwarsa tidak dihitung
            $activeCount = Booking::where('user_id', $user->id)
                ->active()
                ->where('booking_date', '>=', today()->toDateString())
                ->count();

            if ($activeCount >= self::MAX_ACTIVE_BOOKINGS) {
                throw new BookingException(
                    'Kamu sudah punya ' . $activeCount . ' booking aktif. Batas maksimal adalah '
                        . self::MAX_ACTIVE_BOOKINGS . ' booking aktif.'
                );
            }

            if (Booking::overlapping($lockedCourt->id, $date, $startTime, $endTime)->exists()) {
                throw new BookingException('Maaf, jam ini baru saja dipesan orang lain. Silakan pilih jam lain.');
            }

            // Batas waktu konfirmasi admin, tapi tidak boleh melewati jam mulai.
            $startAt = Carbon::parse($date . ' ' . $startTime);
            $expiresAt = now()->addHours(self::CONFIRM_WINDOW_HOURS);

            if ($startAt->lt($expiresAt)) {
                $expiresAt = $startAt;
            }

            return Booking::create([
                'code' => $this->generateCode(),
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'customer_phone' => $user->phone,
                'court_id' => $lockedCourt->id,
                'booking_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_price' => $hours * $lockedCourt->price_per_hour,
                'status' => Booking::STATUS_PENDING,
                'payment_method' => Booking::METHOD_ON_SITE,
                'payment_status' => Booking::PAYMENT_UNPAID,
                'source' => Booking::SOURCE_WEB,
                'expires_at' => $expiresAt,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Booking manual oleh admin (pesanan WhatsApp atau pelanggan yang datang langsung).
     * Langsung berstatus dikonfirmasi, tanpa akun pelanggan, dan tidak kena batas booking aktif.
     * Aturan bentrok dan penguncian sama seperti create().
     */
    public function createManual(
        Court $court,
        string $date,
        string $start,
        string $end,
        string $name,
        string $phone,
        ?string $notes = null,
        bool $paid = false
    ): Booking {
        $hours = $this->checkRange($court, $date, $start, $end, allowOngoing: true);

        $startTime = $start . ':00';
        $endTime = $end . ':00';

        return DB::transaction(function () use ($court, $date, $startTime, $endTime, $hours, $name, $phone, $notes, $paid) {
            $lockedCourt = Court::whereKey($court->id)->lockForUpdate()->firstOrFail();

            if (Booking::overlapping($lockedCourt->id, $date, $startTime, $endTime)->exists()) {
                throw new BookingException('Jam ini sudah terisi booking lain. Pilih jam atau lapangan yang lain.');
            }

            return Booking::create([
                'code' => $this->generateCode(),
                'user_id' => null,
                'customer_name' => $name,
                'customer_phone' => $phone,
                'court_id' => $lockedCourt->id,
                'booking_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_price' => $hours * $lockedCourt->price_per_hour,
                'status' => Booking::STATUS_CONFIRMED,
                'payment_method' => Booking::METHOD_ON_SITE,
                'payment_status' => $paid ? Booking::PAYMENT_PAID : Booking::PAYMENT_UNPAID,
                'source' => Booking::SOURCE_ADMIN,
                'expires_at' => null,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Membatalkan semua booking menunggu yang melewati batas waktu konfirmasi.
     * Dijalankan penjadwal tiap menit. Mengembalikan jumlah booking yang dibatalkan.
     *
     * Satu perintah UPDATE saja, jadi aman bila admin mengonfirmasi di saat yang sama:
     * booking yang sudah berstatus dikonfirmasi tidak ikut terpilih.
     */
    public function expirePending(): int
    {
        return Booking::where('status', Booking::STATUS_PENDING)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancel_reason' => 'Tidak dikonfirmasi tepat waktu',
            ]);
    }

    /**
     * Pembatalan oleh pelanggan. Booking dikunci selama proses supaya tidak
     * bertabrakan dengan konfirmasi admin di saat yang sama.
     */
    public function cancelByCustomer(Booking $booking, User $user): Booking
    {
        return DB::transaction(function () use ($booking, $user) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($locked->user_id !== $user->id) {
                throw new BookingException('Booking ini bukan milikmu.');
            }

            if (
                ! in_array($locked->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED], true)
                || $locked->isExpired()
            ) {
                throw new BookingException('Booking ini sudah tidak bisa dibatalkan.');
            }

            if (! $locked->canBeCancelledByCustomer()) {
                throw new BookingException(
                    'Pembatalan hanya bisa dilakukan paling lambat ' . Booking::CANCEL_LIMIT_HOURS
                        . ' jam sebelum jam main. Silakan hubungi admin.'
                );
            }

            $locked->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancel_reason' => 'Dibatalkan oleh pelanggan',
            ]);

            return $locked;
        });
    }

    /**
     * Aksi admin pada booking: confirm, cancel, pay, complete, no_show.
     * Aturan aksi mana yang boleh ada di Booking::adminActions().
     * Mengembalikan pesan sukses, atau melempar BookingException.
     */
    public function applyAdminAction(Booking $booking, string $action): string
    {
        return DB::transaction(function () use ($booking, $action) {
            // Kunci lapangan dulu (sama seperti create()), lalu bookingnya.
            Court::whereKey($booking->court_id)->lockForUpdate()->firstOrFail();
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if (! in_array($action, $locked->adminActions(), true)) {
                throw new BookingException('Aksi ini tidak bisa dilakukan pada status booking saat ini.');
            }

            switch ($action) {
                case 'confirm':
                    if ($locked->startsAt()->lte(now())) {
                        throw new BookingException('Waktu main booking ini sudah lewat, jadi tidak bisa dikonfirmasi.');
                    }

                    // Booking yang kedaluwarsa jamnya mungkin sudah dipesan orang lain.
                    $conflict = Booking::overlapping(
                        $locked->court_id,
                        $locked->booking_date->toDateString(),
                        $locked->start_time,
                        $locked->end_time
                    )->where('id', '!=', $locked->id)->exists();

                    if ($conflict) {
                        throw new BookingException('Jam ini sudah dipakai booking lain, jadi booking ini tidak bisa dikonfirmasi.');
                    }

                    $locked->update(['status' => Booking::STATUS_CONFIRMED, 'expires_at' => null]);

                    return 'Booking dikonfirmasi.';

                case 'cancel':
                    $wasPaid = $locked->payment_status === Booking::PAYMENT_PAID;

                    $locked->update([
                        'status' => Booking::STATUS_CANCELLED,
                        'cancelled_at' => now(),
                        'cancel_reason' => 'Dibatalkan oleh admin',
                    ]);

                    return $wasPaid
                        ? 'Booking dibatalkan. Booking ini sudah lunas, jadi ingat memproses pengembalian dana secara manual.'
                        : 'Booking dibatalkan.';

                case 'pay':
                    $locked->update(['payment_status' => Booking::PAYMENT_PAID]);

                    return 'Pembayaran dicatat lunas.';

                case 'complete':
                    $locked->update(['status' => Booking::STATUS_COMPLETED]);

                    return 'Booking ditandai selesai.';

                case 'no_show':
                    $locked->update(['status' => Booking::STATUS_NO_SHOW]);

                    return 'Booking ditandai tidak datang.';
            }

            throw new BookingException('Aksi tidak dikenal.');
        });
    }

    private function generateCode(): string
    {
        do {
            $code = 'BK-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));
        } while (Booking::where('code', $code)->exists());

        return $code;
    }
}
