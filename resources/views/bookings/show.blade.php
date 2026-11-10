@extends('layouts.app')

@section('title', 'Booking ' . $booking->code)

@section('content')
    @php
        $expired = $booking->isExpired();
        $isOwner = Auth::id() === $booking->user_id;
        $canCancel = $isOwner && $booking->canBeCancelledByCustomer();
        $waUrl = $isOwner && $booking->status === 'pending' && !$expired ? $booking->whatsappUrl() : null;
        $hours = (int) substr($booking->end_time, 0, 2) - (int) substr($booking->start_time, 0, 2);
        $paymentMethod = $booking->payment_method === 'online' ? 'Online' : 'Bayar di GOR';
        $paymentStatus = match ($booking->payment_status) {
            'paid' => 'Lunas',
            'refunded' => 'Dikembalikan',
            default => 'Belum bayar',
        };
    @endphp

    <div class="container page-narrow">
        @include('partials.flash')

        @if ($isOwner)
            <a href="{{ route('bookings.index') }}" class="small text-decoration-none">&larr; Booking Saya</a>
        @else
            <a href="{{ route('admin.bookings.index') }}" class="small text-decoration-none">&larr; Kelola Booking</a>
        @endif

        <div class="status-banner status-banner--{{ $booking->statusColor() }} mt-2 mb-3">
            <div class="status-banner__title">{{ $booking->statusLabel() }}</div>
            <div class="small">
                @if ($expired)
                    Booking ini kedaluwarsa karena tidak dikonfirmasi admin tepat waktu, jadi jamnya sudah dibuka kembali.
                    Silakan pesan ulang kalau masih ingin bermain.
                @elseif ($booking->status === 'pending')
                    Menunggu konfirmasi admin

                    @if ($booking->expires_at)
                        sampai pukul {{ $booking->expires_at->format('H:i') }}
                    @endif.

                    Kirim detailnya ke WhatsApp agar lebih cepat dikonfirmasi.
                    Setelah batas waktu, booking dianggap batal.
                @elseif ($booking->status === 'confirmed')
                    Jamnya sudah aman. Datang sesuai jadwal dan bayar di kasir GOR.

                    @if ($booking->payment_status === 'paid')
                        Pembayaran sudah tercatat lunas.
                    @endif
                @elseif ($booking->status === 'cancelled')
                    Booking ini dibatalkan

                    @if ($booking->cancelled_at)
                        pada {{ $booking->cancelled_at->locale('id')->translatedFormat('j F Y, H:i') }}
                    @endif.

                    @if ($booking->cancel_reason)
                        ({{ $booking->cancel_reason }})
                    @endif
                @elseif ($booking->status === 'no_show')
                    Kamu tercatat tidak datang pada jadwal ini.
                @elseif ($booking->status === 'completed')
                    Terima kasih sudah bermain!
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <div class="small text-muted">Kode booking</div>
                        <div class="fw-bold fs-5">{{ $booking->code }}</div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-copy="{{ $booking->code }}">Salin</button>
                </div>

                <dl class="detail-list">
                    <div>
                        <dt>Lapangan</dt>
                        <dd>{{ $booking->court->name }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal</dt>
                        <dd>{{ $booking->booking_date->locale('id')->translatedFormat('l, j F Y') }}</dd>
                    </div>
                    <div>
                        <dt>Jam</dt>
                        <dd>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                            ({{ $hours }} jam)</dd>
                    </div>
                    <div>
                        <dt>Total</dt>
                        <dd>Rp{{ number_format($booking->total_price, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>Pembayaran</dt>
                        <dd>{{ $paymentMethod }} ({{ $paymentStatus }})</dd>
                    </div>
                    <div>
                        <dt>Atas nama</dt>
                        <dd>{{ $booking->customer_name }}</dd>
                    </div>
                    @if ($booking->notes)
                        <div>
                            <dt>Catatan</dt>
                            <dd>{{ $booking->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="d-grid gap-2">
            @if ($waUrl)
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="me-1"
                        style="vertical-align: -4px;">
                        <path
                            d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                    </svg>
                    Kirim ke WhatsApp Admin
                </a>
            @endif

            @if ($canCancel)
                <form method="POST" action="{{ route('bookings.cancel', $booking) }}" data-once
                    onsubmit="return confirm('Batalkan booking ini?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100" data-loading="Memproses...">Batalkan
                        Booking</button>
                </form>
            @elseif ($isOwner && in_array($booking->status, ['pending', 'confirmed'], true) && !$expired)
                <p class="small text-muted text-center mb-0">
                    Pembatalan sendiri hanya bisa paling lambat {{ \App\Models\Booking::CANCEL_LIMIT_HOURS }} jam sebelum
                    jam main.
                    Untuk membatalkan sekarang, silakan hubungi admin.
                </p>
            @endif

            <a href="{{ route('courts.index') }}" class="btn btn-outline-secondary">Pesan lapangan lagi</a>
        </div>
    </div>
@endsection
