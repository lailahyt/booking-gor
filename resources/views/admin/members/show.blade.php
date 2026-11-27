@extends('layouts.app')

@section('title', 'Member ' . $member->name)

@section('content')
    <div class="container page-narrow">
        @include('partials.flash')

        <a href="{{ route('admin.members.index') }}" class="small text-decoration-none">&larr; Member</a>

        <div class="page-head mt-2">
            <h1 class="h4">{{ $member->name }}</h1>
            <span class="badge text-bg-{{ $member->isOngoing() ? 'success' : 'secondary' }}">
                {{ $member->isOngoing() ? 'Berlangsung' : 'Selesai' }}
            </span>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <dl class="detail-list">
                    <div>
                        <dt>WhatsApp</dt>
                        <dd>
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $member->phone) }}" target="_blank"
                                rel="noopener">{{ $member->phone }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt>Lapangan</dt>
                        <dd>{{ $member->court->name }}</dd>
                    </div>
                    <div>
                        <dt>Jadwal tetap</dt>
                        <dd>Tiap {{ $member->weekdayLabel() }}, {{ substr($member->start_time, 0, 5) }}
                            ({{ $member->duration_hours }} jam)</dd>
                    </div>
                    <div>
                        <dt>Periode</dt>
                        <dd>
                            {{ $member->starts_on->locale('id')->translatedFormat('j F Y') }} -
                            {{ $member->endsOn()->locale('id')->translatedFormat('j F Y') }}
                            ({{ $member->sessions }} sesi)
                        </dd>
                    </div>
                    <div>
                        <dt>Biaya periode</dt>
                        <dd>Rp{{ number_format($member->monthly_fee, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>Pembayaran</dt>
                        <dd>
                            @if ($member->isPaid())
                                <span class="badge text-bg-success">Lunas</span>
                            @else
                                <span class="badge text-bg-light border">Belum bayar</span>
                            @endif
                        </dd>
                    </div>
                    @if ($member->notes)
                        <div>
                            <dt>Catatan</dt>
                            <dd>{{ $member->notes }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="d-grid gap-2 mt-3">
                    @unless ($member->isPaid())
                        <form method="POST" action="{{ route('admin.members.pay', $member) }}" data-once
                            onsubmit="return confirm('Catat pembayaran member ini lunas untuk seluruh periode?');">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100" data-loading="Memproses...">
                                Tandai Lunas (seluruh periode)
                            </button>
                        </form>
                    @endunless

                    <a href="{{ route('admin.members.create', ['perpanjang' => $member->id]) }}"
                        class="btn btn-outline-primary">Perpanjang</a>
                </div>
            </div>
        </div>

        <h2 class="h6 mb-2">Sesi</h2>
        <div class="d-grid gap-3 mb-3">
            @foreach ($member->bookings as $booking)
                <article class="booking-card">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                        <span class="fw-semibold">
                            {{ $booking->booking_date->locale('id')->translatedFormat('l, j F Y') }}
                        </span>
                        <span class="badge text-bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span>
                    </div>
                    <div class="booking-card__meta">
                        {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                        &middot; <span class="small">{{ $booking->code }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <strong>Rp{{ number_format($booking->total_price, 0, ',', '.') }}</strong>
                        @if ($booking->payment_status === 'paid')
                            <span class="badge text-bg-success">Lunas</span>
                        @else
                            <span class="badge text-bg-light border">Belum bayar</span>
                        @endif
                    </div>

                    @if (count($booking->adminActions()) > 0)
                        <div class="action-group mt-3">
                            @include('admin.bookings._actions', ['booking' => $booking])
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="note-box">
            Berhalangan satu minggu? Batalkan sesi itu di sini, lalu buat jadwal pengganti lewat
            <a href="{{ route('admin.bookings.create') }}">Booking Manual</a>.
            Pendapatan sesi yang dibatalkan tidak dihitung di laporan.
        </div>
    </div>
@endsection
