@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 720px;">
        <h4 class="mb-3">Konfirmasi Booking</h4>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Lapangan</dt>
                    <dd class="col-sm-8">{{ $court->name }}</dd>

                    <dt class="col-sm-4">Tanggal</dt>
                    <dd class="col-sm-8">{{ $date->locale('id')->translatedFormat('l, j F Y') }}</dd>

                    <dt class="col-sm-4">Jam</dt>
                    <dd class="col-sm-8">{{ $start }} - {{ $end }} ({{ $hours }} jam)</dd>

                    <dt class="col-sm-4">Harga per jam</dt>
                    <dd class="col-sm-8">Rp{{ number_format($court->price_per_hour, 0, ',', '.') }}</dd>

                    <dt class="col-sm-4">Total</dt>
                    <dd class="col-sm-8"><strong>Rp{{ number_format($total, 0, ',', '.') }}</strong></dd>

                    <dt class="col-sm-4">Pembayaran</dt>
                    <dd class="col-sm-8 mb-0">Bayar di GOR saat datang</dd>
                </dl>
            </div>
        </div>

        <form method="POST" action="{{ route('bookings.store', $court) }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <input type="hidden" name="start" value="{{ $start }}">
            <input type="hidden" name="end" value="{{ $end }}">

            <div class="mb-3">
                <label for="notes" class="form-label">Catatan (opsional)</label>
                <input id="notes" type="text" name="notes" maxlength="255"
                    class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes') }}"
                    placeholder="Contoh: kami berempat">
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-light border small">
                Booking akan menunggu konfirmasi admin. Jika belum dikonfirmasi dalam {{ $confirmWindow }} jam,
                booking dianggap batal dan jam kembali tersedia.
            </div>

            <button type="submit" class="btn btn-primary">Konfirmasi Booking</button>
            <a href="{{ route('courts.show', ['court' => $court, 'date' => $date->toDateString()]) }}"
                class="btn btn-outline-secondary">Kembali</a>
        </form>
    </div>
@endsection
