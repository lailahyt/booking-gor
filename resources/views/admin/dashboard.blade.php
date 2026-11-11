@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container">
    @include('partials.flash')

    <div class="page-head">
        <div>
            <h1 class="h4">Dashboard</h1>
            <div class="text-muted small">{{ $today->locale('id')->translatedFormat('l, j F Y') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">Booking Manual</a>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Kelola Booking</a>
        </div>
    </div>

    <div class="row g-3 mb-2">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">Booking hari ini</div>
                <div class="stat-card__value">{{ $todayBookings->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card {{ $pendingCount > 0 ? 'stat-card--warning' : '' }}">
                <div class="stat-card__label">Menunggu konfirmasi</div>
                <div class="stat-card__value">{{ $pendingCount }}</div>
                @if ($pendingCount > 0)
                    <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="small fw-semibold">Lihat dan konfirmasi</a>
                @endif
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">Pendapatan hari ini</div>
                <div class="stat-card__value">Rp{{ number_format($revenueToday, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">Pendapatan {{ $today->locale('id')->translatedFormat('F Y') }}</div>
                <div class="stat-card__value">Rp{{ number_format($revenueMonth, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <p class="text-muted small mb-4">
        Pendapatan hanya menghitung booking yang sudah ditandai lunas dan tidak dibatalkan, berdasarkan tanggal main.
    </p>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Jadwal hari ini</span>
                    <a href="{{ route('admin.bookings.index', ['date' => $todayDate]) }}" class="small">Kelola</a>
                </div>

                @forelse ($todayBookings as $booking)
                    <div class="list-row d-flex align-items-center gap-3">
                        <div class="time-pill">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</div>

                        <div class="grow min-w-0">
                            <div class="fw-semibold text-truncate">{{ $booking->customer_name }}</div>
                            <div class="small text-muted">{{ $booking->court->name }}</div>
                        </div>

                        <div class="text-end">
                            <span class="badge text-bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span>
                            <div class="mt-1">
                                @if ($booking->payment_status === 'paid')
                                    <span class="badge text-bg-success">Lunas</span>
                                @else
                                    <span class="badge text-bg-light border">Belum bayar</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada booking hari ini.</div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Booking per lapangan bulan ini</div>
                <ul class="list-group list-group-flush">
                    @forelse ($courtUsage as $court)
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                            <span class="min-w-0 text-truncate">{{ $court->name }}</span>
                            @unless ($court->is_active)
                                <span class="badge text-bg-secondary">Nonaktif</span>
                            @endunless
                            <span class="fw-bold ms-auto">{{ $court->month_bookings }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Belum ada lapangan.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection