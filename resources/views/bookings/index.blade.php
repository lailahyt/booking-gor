@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Booking Saya</h4>
            <a href="{{ route('courts.index') }}" class="btn btn-primary">Pesan Lapangan</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
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
                        @forelse ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->code }}</td>
                                <td>{{ $booking->court->name }}</td>
                                <td>{{ $booking->booking_date->locale('id')->translatedFormat('D, j M Y') }}</td>
                                <td>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</td>
                                <td>Rp{{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                <td><span
                                        class="badge bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('bookings.show', $booking) }}"
                                        class="btn btn-sm btn-outline-primary">Detail</a>

                                    @if ($booking->canBeCancelledByCustomer())
                                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Batalkan booking {{ $booking->code }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada booking.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $bookings->links() }}
        </div>
    </div>
@endsection
