<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 4px;
        }

        h2 {
            font-size: 13px;
            margin: 18px 0 6px;
        }

        .muted {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .right {
            text-align: right;
        }

        .summary td {
            border: none;
            padding: 2px 0;
        }

        .total td {
            font-weight: bold;
            background: #f7f7f7;
        }
    </style>
</head>

<body>
    <h1>Laporan Pendapatan</h1>
    <div class="muted">
        Periode {{ $from->locale('id')->translatedFormat('j F Y') }} sampai
        {{ $to->locale('id')->translatedFormat('j F Y') }}
        &middot; Lapangan: {{ $courtName ?? 'Semua' }}
    </div>
    <div class="muted">Hanya booking lunas dan tidak dibatalkan, berdasarkan tanggal main.</div>

    <h2>Ringkasan</h2>
    <table class="summary" style="width: auto;">
        <tr>
            <td>Total pendapatan</td>
            <td>: <strong>Rp{{ number_format($report['revenue'], 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Jumlah booking lunas</td>
            <td>: {{ $report['count'] }}</td>
        </tr>
        <tr>
            <td>Total jam main</td>
            <td>: {{ $report['hours'] }} jam</td>
        </tr>
    </table>

    <h2>Per lapangan</h2>
    <table>
        <thead>
            <tr>
                <th>Lapangan</th>
                <th class="right">Booking</th>
                <th class="right">Jam</th>
                <th class="right">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['perCourt'] as $row)
                <tr>
                    <td>{{ $row['court'] }}</td>
                    <td class="right">{{ $row['count'] }}</td>
                    <td class="right">{{ $row['hours'] }}</td>
                    <td class="right">Rp{{ number_format($row['revenue'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rincian booking</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode</th>
                <th>Lapangan</th>
                <th>Jam</th>
                <th>Pelanggan</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['bookings'] as $booking)
                <tr>
                    <td>{{ $booking->booking_date->format('d/m/Y') }}</td>
                    <td>{{ $booking->code }}</td>
                    <td>{{ $booking->court->name }}</td>
                    <td>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</td>
                    <td>{{ $booking->customer_name }}</td>
                    <td class="right">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">Tidak ada booking pada periode ini.</td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="5" class="right">TOTAL</td>
                <td class="right">Rp{{ number_format($report['revenue'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p class="muted" style="margin-top: 14px;">Dicetak {{ now()->locale('id')->translatedFormat('j F Y, H:i') }}</p>
</body>

</html>
