@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 720px;">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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
                        <dd class="col-sm-8 mb-0">{{ $booking->notes }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @if ($booking->status === 'pending')
            <div class="alert alert-warning small">
                Menunggu konfirmasi admin
                @if ($booking->expires_at)
                    sampai pukul {{ $booking->expires_at->format('H:i') }}
                @endif
                . Setelah itu booking dianggap batal.
            </div>

            @if ($booking->whatsappUrl())
                <a href="{{ $booking->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-success">
                    Kirim ke WhatsApp Admin
                </a>
            @endif
        @endif

        <a href="{{ route('courts.index') }}" class="btn btn-outline-secondary">Kembali ke daftar lapangan</a>
    </div>
@endsection
