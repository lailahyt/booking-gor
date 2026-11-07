@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 720px;">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Detail Booking</h4>
            <span class="badge bg-{{ $booking->statusColor() }} fs-6">{{ $booking->statusLabel() }}</span>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Kode booking</dt>
                    <dd class="col-sm-8"><strong>{{ $booking->code }}</strong></dd>

                    <dt class="col-sm-4">Lapangan</dt>
                    <dd class="col-sm-8">{{ $booking->court->name }}</dd>

                    <dt class="col-sm-4">Tanggal</dt>
                    <dd class="col-sm-8">{{ $booking->booking_date->locale('id')->translatedFormat('l, j F Y') }}</dd>

                    <dt class="col-sm-4">Jam</dt>
                    <dd class="col-sm-8">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                    </dd>

                    <dt class="col-sm-4">Total</dt>
                    <dd class="col-sm-8">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</dd>

                    <dt class="col-sm-4">Pembayaran</dt>
                    <dd class="col-sm-8">
                        {{ $booking->payment_method === 'online' ? 'Online' : 'Bayar di GOR' }}
                        ({{ $booking->payment_status === 'paid' ? 'Lunas' : 'Belum bayar' }})
                    </dd>

                    <dt class="col-sm-4">Atas nama</dt>
                    <dd class="col-sm-8">{{ $booking->customer_name }}</dd>

                    @if ($booking->notes)
                        <dt class="col-sm-4">Catatan</dt>
                        <dd class="col-sm-8">{{ $booking->notes }}</dd>
                    @endif

                    @if ($booking->status === 'cancelled' && $booking->cancelled_at)
                        <dt class="col-sm-4">Dibatalkan</dt>
                        <dd class="col-sm-8 mb-0">
                            {{ $booking->cancelled_at->locale('id')->translatedFormat('j F Y, H:i') }}
                            @if ($booking->cancel_reason)
                                &middot; {{ $booking->cancel_reason }}
                            @endif
                        </dd>
                    @endif
                </dl>
            </div>
        </div>

        @if ($booking->isExpired())
            <div class="alert alert-secondary small">
                Booking ini kedaluwarsa karena tidak dikonfirmasi admin tepat waktu, jadi jamnya sudah dibuka kembali.
                Silakan pesan ulang kalau masih ingin bermain.
            </div>
        @elseif ($booking->status === 'pending')
            <div class="alert alert-warning small">
                Menunggu konfirmasi admin
                @if ($booking->expires_at)
                    sampai pukul {{ $booking->expires_at->format('H:i') }}.
                @endif
                Setelah itu booking dianggap batal.
            </div>

            @if ($booking->whatsappUrl())
                <a href="{{ $booking->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-success">
                    Kirim ke WhatsApp Admin
                </a>
            @endif
        @endif

        @if ($booking->canBeCancelledByCustomer())
            <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="d-inline"
                onsubmit="return confirm('Batalkan booking ini?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Batalkan Booking</button>
            </form>
        @elseif (in_array($booking->status, ['pending', 'confirmed'], true) && !$booking->isExpired())
            <p class="text-muted small mt-3 mb-0">
                Pembatalan sendiri hanya bisa paling lambat {{ \App\Models\Booking::CANCEL_LIMIT_HOURS }} jam sebelum jam
                main.
                Untuk membatalkan sekarang, silakan hubungi admin.
            </p>
        @endif

        <div class="mt-3">
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">Booking Saya</a>
            <a href="{{ route('courts.index') }}" class="btn btn-outline-secondary">Daftar Lapangan</a>
        </div>
    </div>
@endsection
