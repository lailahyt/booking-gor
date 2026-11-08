@extends('layouts.app')

@section('content')
    @php
        $actionLabels = [
            'confirm' => ['Konfirmasi', 'btn-success', null],
            'pay' => ['Tandai Lunas', 'btn-outline-primary', null],
            'complete' => ['Selesai', 'btn-outline-success', null],
            'no_show' => ['Tidak Datang', 'btn-outline-dark', 'Tandai booking ini sebagai tidak datang?'],
            'cancel' => ['Batalkan', 'btn-outline-danger', 'Batalkan booking ini?'],
        ];
    @endphp

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Kelola Booking</h4>
            <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">Booking Manual</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($pendingCount > 0)
            <div class="alert alert-warning">
                Ada <strong>{{ $pendingCount }}</strong> booking yang menunggu konfirmasi.
                <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="alert-link">Lihat</a>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.bookings.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-6 col-md-3">
                <label for="date" class="form-label mb-1">Tanggal</label>
                <input type="date" id="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-6 col-md-3">
                <label for="court_id" class="form-label mb-1">Lapangan</label>
                <select id="court_id" name="court_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($courts as $court)
                        <option value="{{ $court->id }}" @selected(request('court_id') == $court->id)>{{ $court->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="status" class="form-label mb-1">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2 align-items-center">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="semua" value="1" id="semua"
                        @checked(request()->boolean('semua'))>
                    <label class="form-check-label small" for="semua">Termasuk yang lewat</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Reset</a>
                <span class="text-muted small ms-2">Tanpa filter tanggal, yang tampil hanya hari ini dan seterusnya.</span>
            </div>
        </form>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                        @forelse ($bookings as $booking)
                            <tr>
                                <td class="small">{{ $booking->code }}</td>
                                <td>
                                    {{ $booking->customer_name }}
                                    <div class="small">
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $booking->customer_phone) }}"
                                            target="_blank" rel="noopener" class="text-decoration-none">
                                            {{ $booking->customer_phone }}
                                        </a>
                                        @if ($booking->source === 'admin')
                                            <span class="badge bg-light text-dark border">manual</span>
                                        @endif
                                    </div>
                                    @if ($booking->notes)
                                        <div class="small text-muted">{{ $booking->notes }}</div>
                                    @endif
                                </td>
                                <td>{{ $booking->court->name }}</td>
                                <td>{{ $booking->booking_date->locale('id')->translatedFormat('D, j M Y') }}</td>
                                <td>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</td>
                                <td>Rp{{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'light text-dark border' }}">
                                        {{ $booking->payment_status === 'paid' ? 'Lunas' : 'Belum' }}
                                    </span>
                                    <div class="small text-muted">
                                        {{ $booking->payment_method === 'online' ? 'Online' : 'Di GOR' }}</div>
                                </td>
                                <td><span
                                        class="badge bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span>
                                </td>
                                <td class="text-end text-nowrap">
                                    @foreach ($booking->adminActions() as $action)
                                        @php [$label, $class, $ask] = $actionLabels[$action]; @endphp
                                        <form method="POST"
                                            action="{{ route('admin.bookings.action', [$booking, $action]) }}"
                                            class="d-inline"
                                            @if ($ask) onsubmit="return confirm('{{ $ask }}');" @endif>
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm {{ $class }}">{{ $label }}</button>
                                        </form>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Tidak ada booking.</td>
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
