@extends('layouts.app')

@section('title', 'Konfirmasi Booking')

@section('content')
    <div class="container page-narrow page-with-bar">
        @include('partials.flash')

        <a href="{{ route('courts.show', ['court' => $court, 'date' => $date->toDateString()]) }}"
            class="small text-decoration-none">&larr; Ubah pilihan jam</a>

        <h1 class="h4 mt-2 mb-1">Konfirmasi booking</h1>
        <p class="text-muted mb-3">Periksa detailnya, lalu konfirmasi.</p>

        <form method="POST" action="{{ route('bookings.store', $court) }}" data-once>
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <input type="hidden" name="start" value="{{ $start }}">
            <input type="hidden" name="end" value="{{ $end }}">

            <div class="card mb-3">
                <div class="card-body">
                    <div class="text-muted small">{{ $date->locale('id')->translatedFormat('l, j F Y') }}</div>
                    <div class="summary-time mb-1">{{ $start }} - {{ $end }}</div>
                    <div class="text-muted mb-3">{{ $court->name }} &middot; {{ $hours }} jam</div>

                    <dl class="detail-list">
                        <div>
                            <dt>Harga per jam</dt>
                            <dd>Rp{{ number_format($court->price_per_hour, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt>Durasi</dt>
                            <dd>{{ $hours }} jam</dd>
                        </div>
                        <div>
                            <dt>Total</dt>
                            <dd class="fs-5">Rp{{ number_format($total, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt>Pembayaran</dt>
                            <dd>Bayar di GOR saat datang</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Catatan (opsional)</label>
                <input id="notes" type="text" name="notes" maxlength="255"
                    class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes') }}"
                    placeholder="Contoh: kami berempat">
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="note-box">
                Booking akan menunggu konfirmasi admin. Jika belum dikonfirmasi dalam {{ $confirmWindow }} jam,
                booking dianggap batal dan jam kembali tersedia.
            </div>

            {{-- Bilah tombol yang menempel di bawah layar --}}
            <div class="action-bar">
                <div class="container page-narrow action-bar__inner">
                    <div class="action-bar__info">
                        <div class="action-bar__sub">Total pembayaran</div>
                        <div class="action-bar__title">{{ $hours }} jam</div>
                    </div>
                    <div class="action-bar__price fs-5">Rp{{ number_format($total, 0, ',', '.') }}</div>
                    <button type="submit" class="btn btn-primary" data-loading="Memproses...">Konfirmasi Booking</button>
                </div>
            </div>
        </form>
    </div>
@endsection
