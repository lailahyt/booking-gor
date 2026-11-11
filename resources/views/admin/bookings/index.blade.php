@extends('layouts.app')

@section('title', 'Kelola Booking')

@section('content')
    @php
        $hasFilter =
            request()->filled('date') ||
            request()->filled('court_id') ||
            request()->filled('status') ||
            request()->boolean('semua');
    @endphp

    <div class="container">
        @include('partials.flash')

        <div class="page-head">
            <h1 class="h4">Kelola Booking</h1>
            <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">Booking Manual</a>
        </div>

        @if ($pendingCount > 0)
            <div class="alert alert-warning">
                Ada <strong>{{ $pendingCount }}</strong> booking yang menunggu konfirmasi.
                <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="alert-link">Lihat</a>
            </div>
        @endif

        {{-- Filter: di ponsel bisa dibuka-tutup, di layar lebar selalu terlihat --}}
        <button class="btn btn-outline-secondary d-md-none mb-2" type="button" data-bs-toggle="collapse"
            data-bs-target="#booking-filters" aria-controls="booking-filters"
            aria-expanded="{{ $hasFilter ? 'true' : 'false' }}">
            Filter
            @if ($hasFilter)
                (aktif)
            @endif
        </button>

        <div class="collapse d-md-block {{ $hasFilter ? 'show' : '' }}" id="booking-filters">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="card card-body mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-3">
                        <label for="date" class="form-label mb-1">Tanggal</label>
                        <input type="date" id="date" name="date" class="form-control"
                            value="{{ request('date') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="court_id" class="form-label mb-1">Lapangan</label>
                        <select id="court_id" name="court_id" class="form-select">
                            <option value="">Semua</option>
                            @foreach ($courts as $court)
                                <option value="{{ $court->id }}" @selected(request('court_id') == $court->id)>{{ $court->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Semua</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="semua" value="1" id="semua"
                                @checked(request()->boolean('semua'))>
                            <label class="form-check-label small" for="semua">Termasuk yang sudah lewat</label>
                        </div>
                    </div>
                    <div class="col-12 d-flex flex-wrap align-items-center gap-2">
                        <button type="submit" class="btn btn-primary">Terapkan</button>
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Reset</a>
                        <span class="text-muted small">Tanpa filter tanggal, yang tampil hanya hari ini dan
                            seterusnya.</span>
                    </div>
                </div>
            </form>
        </div>

        @if ($bookings->isEmpty())
            <div class="card empty-state">Tidak ada booking.</div>
        @else
            {{-- Ponsel: kartu --}}
            <div class="d-md-none d-grid gap-3">
                @foreach ($bookings as $booking)
                    <article class="booking-card">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <span class="booking-card__code">{{ $booking->code }}</span>
                            <span class="badge text-bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span>
                        </div>

                        <div class="fw-semibold">
                            {{ $booking->customer_name }}
                            @if ($booking->source === 'admin')
                                <span class="badge text-bg-light border">manual</span>
                            @endif
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $booking->customer_phone) }}" target="_blank"
                            rel="noopener" class="small">{{ $booking->customer_phone }}</a>

                        <div class="booking-card__meta mt-1">
                            {{ $booking->court->name }}
                            &middot; {{ $booking->booking_date->locale('id')->translatedFormat('D, j M') }}
                            &middot; {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <strong>Rp{{ number_format($booking->total_price, 0, ',', '.') }}</strong>
                            <span>
                                @if ($booking->payment_status === 'paid')
                                    <span class="badge text-bg-success">Lunas</span>
                                @else
                                    <span class="badge text-bg-light border">Belum bayar</span>
                                @endif
                                <span
                                    class="small text-muted">{{ $booking->payment_method === 'online' ? 'Online' : 'Di GOR' }}</span>
                            </span>
                        </div>

                        @if ($booking->notes)
                            <div class="small text-muted mt-1">{{ $booking->notes }}</div>
                        @endif

                        @if (count($booking->adminActions()) > 0)
                            <div class="action-group mt-3">
                                @include('admin.bookings._actions', ['booking' => $booking])
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            {{-- Layar lebar: tabel --}}
            <div class="card d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-admin table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Pelanggan</th>
                                <th>Lapangan</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Total</th>
                                <th>Bayar</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td class="small">{{ $booking->code }}</td>
                                    <td>
                                        {{ $booking->customer_name }}
                                        @if ($booking->source === 'admin')
                                            <span class="badge text-bg-light border">manual</span>
                                        @endif
                                        <div class="small">
                                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $booking->customer_phone) }}"
                                                target="_blank" rel="noopener">{{ $booking->customer_phone }}</a>
                                        </div>
                                        @if ($booking->notes)
                                            <div class="small text-muted">{{ $booking->notes }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $booking->court->name }}</td>
                                    <td>{{ $booking->booking_date->locale('id')->translatedFormat('D, j M Y') }}</td>
                                    <td class="text-nowrap">{{ substr($booking->start_time, 0, 5) }} -
                                        {{ substr($booking->end_time, 0, 5) }}</td>
                                    <td>Rp{{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($booking->payment_status === 'paid')
                                            <span class="badge text-bg-success">Lunas</span>
                                        @else
                                            <span class="badge text-bg-light border">Belum</span>
                                        @endif
                                        <div class="small text-muted">
                                            {{ $booking->payment_method === 'online' ? 'Online' : 'Di GOR' }}</div>
                                    </td>
                                    <td><span
                                            class="badge text-bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="action-group justify-content-end">
                                            @include('admin.bookings._actions', ['booking' => $booking])
                                        </div>
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
