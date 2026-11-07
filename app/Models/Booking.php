<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    use HasFactory;

    // Status booking
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW   = 'no_show';
    public const STATUS_COMPLETED = 'completed';

    // Metode dan status bayar
    public const METHOD_ON_SITE = 'on_site';
    public const METHOD_ONLINE  = 'online';

    public const PAYMENT_UNPAID   = 'unpaid';
    public const PAYMENT_PAID     = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';

    public const CANCEL_LIMIT_HOURS = 3;

    // Sumber booking
    public const SOURCE_WEB   = 'web';
    public const SOURCE_ADMIN = 'admin';

    protected $fillable = [
        'code',
        'user_id',
        'customer_name',
        'customer_phone',
        'court_id',
        'booking_date',
        'start_time',
        'end_time',
        'total_price',
        'status',
        'payment_method',
        'payment_status',
        'source',
        'expires_at',
        'cancelled_at',
        'cancel_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'total_price'  => 'integer',
            'expires_at'   => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // URL memakai kode booking, bukan nomor urut, supaya tidak mudah ditebak
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function statusLabel(): string
    {
        if ($this->isExpired()) {
            return 'Kedaluwarsa';
        }

        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Konfirmasi',
            self::STATUS_CONFIRMED => 'Dikonfirmasi',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_NO_SHOW => 'Tidak Datang',
            self::STATUS_COMPLETED => 'Selesai',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        if ($this->isExpired()) {
            return 'secondary';
        }

        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_NO_SHOW => 'dark',
            default => 'secondary',
        };
    }

    // Waktu mulai main sebagai tanggal-jam lengkap
    public function startsAt(): Carbon
    {
        return Carbon::parse($this->booking_date->toDateString() . ' ' . $this->start_time);
    }

    // Booking menunggu yang melewati batas konfirmasi
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    // Boleh dibatalkan sendiri: masih aktif dan lebih dari 3 jam sebelum mulai
    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true)
            && ! $this->isExpired()
            && $this->startsAt()->gt(now()->addHours(self::CANCEL_LIMIT_HOURS));
    }

    // Booking yang masih berlaku: dikonfirmasi, atau menunggu yang belum kedaluwarsa
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('status', self::STATUS_CONFIRMED)
                ->orWhere(function (Builder $q) {
                    $q->where('status', self::STATUS_PENDING)
                        ->where(function (Builder $q) {
                            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        });
                });
        });
    }

    public function whatsappUrl(): ?string
    {
        $number = preg_replace('/\D/', '', (string) config('gor.whatsapp'));

        if ($number === '') {
            return null;
        }

        $text = "Halo admin, saya sudah membuat booking lapangan.\n"
            . "Kode: {$this->code}\n"
            . "Lapangan: {$this->court->name}\n"
            . 'Tanggal: ' . $this->booking_date->locale('id')->translatedFormat('l, j F Y') . "\n"
            . 'Jam: ' . substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5) . "\n"
            . 'Total: Rp' . number_format($this->total_price, 0, ',', '.');

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($text);
    }

    /**
     * Booking yang dianggap "mengisi slot":
     * confirmed, completed, dan pending yang belum kedaluwarsa.
     * cancelled dan no_show tidak menghalangi.
     */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_COMPLETED])
                ->orWhere(function (Builder $q) {
                    $q->where('status', self::STATUS_PENDING)
                        ->where(function (Builder $q) {
                            $q->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                });
        });
    }

    /**
     * Booking yang bentrok dengan rentang waktu tertentu.
     * Bentrok jika: mulai_lama < selesai_baru DAN selesai_lama > mulai_baru.
     * Jam selesai = jam mulai booking lain (21:00 vs 21:00) tidak dianggap bentrok.
     *
     * Jam diberikan dalam format 'H:i:s', tanggal 'Y-m-d'.
     */
    public function scopeOverlapping(Builder $query, int $courtId, string $date, string $start, string $end): Builder
    {
        return $query->where('court_id', $courtId)
            ->where('booking_date', $date)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->blocking();
    }

    // Aksi admin yang boleh dilakukan pada booking ini
    public function adminActions(): array
    {
        $actions = [];

        if ($this->status === self::STATUS_PENDING) {
            $actions[] = 'confirm'; // yang kedaluwarsa pun boleh, dicek bentrok saat dikonfirmasi
        }

        if (in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true)) {
            $actions[] = 'cancel';
        }

        if (
            in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_COMPLETED], true)
            && $this->payment_status === self::PAYMENT_UNPAID
        ) {
            $actions[] = 'pay';
        }

        // selesai dan tidak datang hanya setelah jam main dimulai
        if ($this->status === self::STATUS_CONFIRMED && $this->startsAt()->lte(now())) {
            if ($this->payment_status === self::PAYMENT_PAID) {
                $actions[] = 'complete'; // harus lunas dulu supaya pendapatan tercatat
            }
            $actions[] = 'no_show';
        }

        return $actions;
    }
}
