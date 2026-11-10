@extends('layouts.app')

@section('title', 'Jadwal ' . $court->name)

@section('content')
    @php
        $freeCount = collect($slots)->where('status', 'free')->count();
    @endphp

    <div class="container schedule-page">
        @include('partials.flash')

        <a href="{{ route('courts.index') }}" class="small text-decoration-none">&larr; Semua lapangan</a>

        <div class="mt-2 mb-3">
            <h1 class="h4 mb-1">{{ $court->name }}</h1>
            <div class="text-muted small">
                Rp{{ number_format($court->price_per_hour, 0, ',', '.') }} / jam
                @if ($court->floor_type)
                    &middot; Lantai {{ $court->floor_type }}
                @endif
                &middot; Buka {{ substr($court->open_time, 0, 5) }} - {{ substr($court->close_time, 0, 5) }}
            </div>
        </div>

        {{-- Pilihan lapangan --}}
        @if ($courts->count() > 1)
            <nav class="chip-row mb-3" aria-label="Pilih lapangan">
                @foreach ($courts as $c)
                    <a href="{{ route('courts.show', ['court' => $c, 'date' => $date->toDateString()]) }}"
                        class="chip {{ $c->id === $court->id ? 'is-active' : '' }}"
                        @if ($c->id === $court->id) aria-current="page" @endif>
                        {{ $c->name }}
                    </a>
                @endforeach
            </nav>
        @endif

        {{-- Pilihan tanggal --}}
        <nav class="date-strip" aria-label="Pilih tanggal">
            @foreach ($days as $day)
                @php $active = $day->isSameDay($date); @endphp
                <a href="{{ route('courts.show', ['court' => $court, 'date' => $day->toDateString()]) }}"
                    class="date-chip {{ $active ? 'is-active' : '' }}"
                    @if ($active) aria-current="date" @endif>
                    <span class="date-chip__dow">{{ $day->locale('id')->translatedFormat('D') }}</span>
                    <span class="date-chip__day">{{ $day->format('j') }}</span>
                    <span class="date-chip__mon">{{ $day->locale('id')->translatedFormat('M') }}</span>
                </a>
            @endforeach
        </nav>

        <h2 class="h6 mt-2 mb-1">{{ $date->locale('id')->translatedFormat('l, j F Y') }}</h2>
        <p class="text-muted small mb-2">
            Ketuk jam mulai, lalu ketuk jam berikutnya untuk menambah durasi (maksimal
            {{ \App\Services\BookingService::MAX_HOURS }} jam).
        </p>

        <div class="legend mb-3" aria-hidden="true">
            <span class="legend__item"><span class="legend__dot"></span> Kosong</span>
            <span class="legend__item"><span class="legend__dot legend__dot--selected"></span> Dipilih</span>
            <span class="legend__item"><span class="legend__dot legend__dot--booked"></span> Terisi</span>
        </div>

        @if ($freeCount === 0)
            <div class="alert alert-light border">
                Tidak ada jam kosong pada tanggal ini. Coba tanggal lain atau lapangan lain.
            </div>
        @endif

        <form method="GET" action="{{ route('bookings.create', $court) }}" id="slot-form">
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <input type="hidden" name="start" id="f-start">
            <input type="hidden" name="end" id="f-end">

            <div class="slot-grid" id="slot-grid" data-price="{{ $court->price_per_hour }}"
                data-max="{{ \App\Services\BookingService::MAX_HOURS }}">
                @foreach ($slots as $slot)
                    @if ($slot['status'] === 'free')
                        <button type="button" class="slot slot--free slot-btn" data-index="{{ $loop->index }}"
                            data-start="{{ $slot['start'] }}" data-end="{{ $slot['end'] }}" aria-pressed="false">
                            <span class="slot__time">{{ substr($slot['start'], 0, 5) }}</span>
                            <span class="slot__until">s/d {{ substr($slot['end'], 0, 5) }}</span>
                        </button>
                    @elseif ($slot['status'] === 'booked')
                        <div class="slot slot--booked" aria-disabled="true">
                            <span class="slot__time">{{ substr($slot['start'], 0, 5) }}</span>
                            <span class="slot__until">Terisi</span>
                        </div>
                    @else
                        <div class="slot slot--past" aria-disabled="true">
                            <span class="slot__time">{{ substr($slot['start'], 0, 5) }}</span>
                            <span class="slot__until">Lewat</span>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="slot-note mt-2" id="slot-note" aria-live="polite"></div>

            @guest
                <div class="alert alert-info small mt-2 mb-0">
                    Untuk memesan, kamu perlu masuk atau daftar dulu. Pilihan jammu tidak hilang.
                </div>
            @endguest

            {{-- Bilah ringkasan: muncul begitu ada jam yang dipilih --}}
            <div class="action-bar" id="slot-bar" hidden>
                <div class="container action-bar__inner">
                    <div class="action-bar__info">
                        <div class="action-bar__title" id="bar-title"></div>
                        <div class="action-bar__sub">
                            {{ $court->name }} &middot; {{ $date->locale('id')->translatedFormat('D, j M') }}
                        </div>
                    </div>
                    <div class="action-bar__price" id="bar-total"></div>
                    <button type="submit" class="btn btn-primary" id="btn-continue" disabled>Lanjut Pesan</button>
                </div>
            </div>
        </form>
    </div>
@endsection
