@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h4 class="mb-0">Dashboard Admin</h4>
                <div class="text-muted small">
                    {{ $today->locale('id')->translatedFormat('l, j F Y') }}
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">Booking Manual</a>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Kelola Booking</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Booking hari ini</div>
                        <div class="fs-3 fw-semibold">{{ $todayBookings->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 {{ $pendingCount > 0 ? 'border-warning' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small">Menunggu konfirmasi</div>
                        <div class="fs-3 fw-semibold">{{ $pendingCount }}</div>
                        @if ($pendingCount > 0)
                            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="small">Lihat</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Pendapatan hari ini</div>
                        <div class="fs-3 fw-semibold">Rp{{ number_format($revenueToday, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Pendapatan {{ $today->locale('id')->translatedFormat('F Y') }}</div>
                        <div class="fs-3 fw-semibold">Rp{{ number_format($revenueMonth, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-muted small mb-4">
            Pendapatan hanya menghitung booking yang sudah ditandai lunas dan tidak dibatalkan, berdasarkan tanggal main.
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Jadwal hari ini</span>
                        <a href="{{ route('admin.bookings.index', ['date' => $todayDate]) }}" class="small">Kelola</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Jam</th>
                                    <th>Lapangan</th>
                                    <th>Pelanggan</th>
                                    <th>Bayar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($todayBookings as $booking)
                                    <tr>
                                        <td>{{ substr($booking->start_time, 0, 5) }} -
                                            {{ substr($booking->end_time, 0, 5) }}</td>
                                        <td>{{ $booking->court->name }}</td>
                                        <td>{{ $booking->customer_name }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'light text-dark border' }}">
                                                {{ $booking->payment_status === 'paid' ? 'Lunas' : 'Belum' }}
                                            </span>
                                        </td>
                                        <td><span
                                                class="badge bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada booking hari ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">Booking per lapangan bulan ini</div>
                    <ul class="list-group list-group-flush">
                        @forelse ($courtUsage as $court)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $court->name }}
                                @unless ($court->is_active)
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endunless
                                <span class="fw-semibold ms-auto">{{ $court->month_bookings }}</span>
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
