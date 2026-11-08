@extends('layouts.app')

@section('content')
    <div class="container">
        <h4 class="mb-3">Laporan Pendapatan</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-6 col-md-3">
                <label for="from" class="form-label mb-1">Dari tanggal</label>
                <input type="date" id="from" name="from" class="form-control" value="{{ $from->toDateString() }}"
                    required>
            </div>
            <div class="col-6 col-md-3">
                <label for="to" class="form-label mb-1">Sampai tanggal</label>
                <input type="date" id="to" name="to" class="form-control" value="{{ $to->toDateString() }}"
                    required>
            </div>
            <div class="col-6 col-md-3">
                <label for="court_id" class="form-label mb-1">Lapangan</label>
                <select id="court_id" name="court_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($courts as $court)
                        <option value="{{ $court->id }}" @selected($courtId === $court->id)>{{ $court->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </div>
        </form>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('admin.reports.excel', $query) }}" class="btn btn-outline-success">Unduh Excel</a>
            <a href="{{ route('admin.reports.pdf', $query) }}" class="btn btn-outline-danger">Unduh PDF</a>
        </div>

        <div class="text-muted small mb-3">
            Periode {{ $from->locale('id')->translatedFormat('j F Y') }} sampai
            {{ $to->locale('id')->translatedFormat('j F Y') }}.
            Hanya booking yang sudah ditandai lunas dan tidak dibatalkan, berdasarkan tanggal main.
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total pendapatan</div>
                        <div class="fs-3 fw-semibold">Rp{{ number_format($report['revenue'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Jumlah booking lunas</div>
                        <div class="fs-3 fw-semibold">{{ $report['count'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total jam main</div>
                        <div class="fs-3 fw-semibold">{{ $report['hours'] }} jam</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Per lapangan</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Lapangan</th>
                            <th class="text-end">Booking</th>
                            <th class="text-end">Jam</th>
                            <th class="text-end">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['perCourt'] as $row)
                            <tr>
                                <td>{{ $row['court'] }}</td>
                                <td class="text-end">{{ $row['count'] }}</td>
                                <td class="text-end">{{ $row['hours'] }}</td>
                                <td class="text-end">Rp{{ number_format($row['revenue'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Tidak ada data pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Rincian booking</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kode</th>
                            <th>Lapangan</th>
                            <th>Jam</th>
                            <th>Pelanggan</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($detail as $booking)
                            <tr>
                                <td>{{ $booking->booking_date->locale('id')->translatedFormat('D, j M Y') }}</td>
                                <td class="small">{{ $booking->code }}</td>
                                <td>{{ $booking->court->name }}</td>
                                <td>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</td>
                                <td>{{ $booking->customer_name }}</td>
                                <td class="text-end">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Tidak ada booking pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $detail->links() }}
        </div>
    </div>
@endsection
