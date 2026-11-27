<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Member extends Model
{
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'name',
        'phone',
        'court_id',
        'starts_on',
        'start_time',
        'duration_hours',
        'sessions',
        'monthly_fee',
        'payment_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'duration_hours' => 'integer',
            'sessions' => 'integer',
            'monthly_fee' => 'integer',
        ];
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Tanggal sesi terakhir periode ini
    public function endsOn(): Carbon
    {
        return $this->starts_on->copy()->addWeeks($this->sessions - 1);
    }

    // Nama hari main, misalnya "Selasa"
    public function weekdayLabel(): string
    {
        return $this->starts_on->copy()->locale('id')->translatedFormat('l');
    }

    // Periode masih berlangsung bila sesi terakhir belum lewat
    public function isOngoing(): bool
    {
        return $this->endsOn()->gte(today());
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }
}
