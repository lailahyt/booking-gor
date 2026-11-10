@extends('layouts.app')

@section('title', 'Booking Saya')

@section('content')
    <div class="container">
        @include('partials.flash')

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Booking Saya</h1>
            <a href="{{ route('courts.index') }}" class="btn btn-primary btn-sm">Pesan Lapangan</a>
        </div>

        @if ($bookings->isEmpty())
            <div class="card empty-state">
                <div>
                    <span class="empty-state__icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </span>
                </div>
                <h2 class="h5">Belum ada booking</h2>
                <p class="mb-3">Pesan lapangan pertamamu dalam beberapa ketukan.</p>
                <div>
                    <a href="{{ route('courts.index') }}" class="btn btn-primary">Pesan lapangan</a>
                </div>
            </div>
        @else
            {{-- Ponsel: kartu --}}
            <div class="d-md-none d-grid gap-3">
                @foreach ($bookings as $booking)
                    <article class="booking-card">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="booking-card__code">{{ $booking->code }}</span>
                            <span class="badge text-bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span>
                        </div>

                        <h2 class="h6 mb-1">{{ $booking->court->name }}</h2>
                        <div class="booking-card__meta">
                            {{ $booking->booking_date->locale('id')->translatedFormat('D, j M Y') }}
                            &middot; {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                        </div>
                        <div class="fw-bold mt-2">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</div>

                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('bookings.show', $booking) }}"
                                class="btn btn-outline-primary btn-sm flex-fill">Detail</a>

                            @if ($booking->canBeCancelledByCustomer())
                                <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="flex-fill"
                                    data-once onsubmit="return confirm('Batalkan booking {{ $booking->code }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                        data-loading="Memproses...">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Layar lebar: tabel --}}
            <div class="card d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Lapangan</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td class="small">{{ $booking->code }}</td>
                                    <td>{{ $booking->court->name }}</td>
                                    <td>{{ $booking->booking_date->locale('id')->translatedFormat('D, j M Y') }}</td>
                                    <td>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                                    </td>
                                    <td>Rp{{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                    <td><span
                                            class="badge text-bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('bookings.show', $booking) }}"
                                            class="btn btn-sm btn-outline-primary">Detail</a>

                                        @if ($booking->canBeCancelledByCustomer())
                                            <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                                                class="d-inline" data-once
                                                onsubmit="return confirm('Batalkan booking {{ $booking->code }}?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-loading="Memproses...">Batalkan</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
@endsection
