<?php

namespace App\Models;

use App\Support\Minutes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    use HasFactory;

    // Pilihan langkah jam mulai (menit) yang boleh diatur admin
    public const STEP_OPTIONS = [30, 60];

    protected $fillable = [
        'name',
        'floor_type',
        'description',
        'photo',
        'price_3_hours',
        'price_4_hours',
        'open_time',
        'close_time',
        'slot_step_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_3_hours' => 'integer',
            'price_4_hours' => 'integer',
            'slot_step_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Harga paket untuk jumlah jam tertentu (3 atau 4).
     * Satu-satunya sumber harga: dipakai server saat membuat booking.
     */
    public function priceForHours(int $hours): int
    {
        return match ($hours) {
            3 => (int) $this->price_3_hours,
            4 => (int) $this->price_4_hours,
            default => throw new \InvalidArgumentException("Paket {$hours} jam tidak tersedia."),
        };
    }

    // Jam buka dalam menit sejak tengah malam
    public function openMinutes(): int
    {
        return Minutes::parse($this->open_time) ?? 0;
    }

    // Jam tutup dalam menit sejak tengah malam
    public function closeMinutes(): int
    {
        return Minutes::parse($this->close_time) ?? 0;
    }

    // Langkah jam mulai (menit). Nilai di luar pilihan dianggap 60 agar aman.
    public function stepMinutes(): int
    {
        $step = (int) $this->slot_step_minutes;

        return in_array($step, self::STEP_OPTIONS, true) ? $step : 60;
    }
}
