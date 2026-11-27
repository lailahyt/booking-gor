@extends('layouts.app')

@section('title', 'Jadwal ' . $court->name)

@section('content')
    @php
        $freeCount = collect($starts)->where('status', 'free')->count();
        $lastStart = collect($starts)->last()['start'] ?? null;
        $dateParam = $date->toDateString();
    @endphp

    <div class="container schedule-page">
        @include('partials.flash')

        <a href="{{ route('courts.index') }}" class="small text-decoration-none">&larr; Semua lapangan</a>

        <div class="mt-2 mb-3">
            <h1 class="h4 mb-1">{{ $court->name }}</h1>
            <div class="text-muted small">
                3 jam Rp{{ number_format($packages[3], 0, ',', '.') }}
                &middot; 4 jam Rp{{ number_format($packages[4], 0, ',', '.') }}
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
                    <a href="{{ route('courts.show', ['court' => $c, 'date' => $dateParam, 'hours' => $hours]) }}"
                        class="chip {{ $c->id === $court->id ? 'is-active' : '' }}"
                        @if ($c->id === $court->id) aria-current="page" @endif>
                        {{ $c->name }}
                    </a>
                @endforeach
            </nav>
        @endif

        {{-- 1. Pilihan tanggal --}}
        <nav class="date-strip" aria-label="Pilih tanggal">
            @foreach ($days as $day)
                @php $active = $day->isSameDay($date); @endphp
                <a href="{{ route('courts.show', ['court' => $court, 'date' => $day->toDateString(), 'hours' => $hours]) }}"
                    class="date-chip {{ $active ? 'is-active' : '' }}"
                    @if ($active) aria-current="date" @endif>
                    <span class="date-chip__dow">{{ $day->locale('id')->translatedFormat('D') }}</span>
                    <span class="date-chip__day">{{ $day->format('j') }}</span>
                    <span class="date-chip__mon">{{ $day->locale('id')->translatedFormat('M') }}</span>
                </a>
            @endforeach
        </nav>

        <h2 class="h6 mt-2 mb-2">{{ $date->locale('id')->translatedFormat('l, j F Y') }}</h2>

        {{-- 2. Pilihan paket --}}
        <div class="package-picker mb-3" role="group" aria-label="Pilih paket">
            @foreach ($packages as $packageHours => $packagePrice)
                <a href="{{ route('courts.show', ['court' => $court, 'date' => $dateParam, 'hours' => $packageHours]) }}"
                    class="package-card {{ $packageHours === $hours ? 'is-active' : '' }}"
                    @if ($packageHours === $hours) aria-current="true" @endif>
                    <span class="package-card__name">Paket {{ $packageHours }} jam</span>
                    <span class="package-card__price">Rp{{ number_format($packagePrice, 0, ',', '.') }}</span>
                </a>
            @endforeach
        </div>

        {{-- 3. Pilihan jam mulai --}}
        <h3 class="h6 mb-1">Pilih jam mulai</h3>
        <p class="text-muted small mb-2">
            Ketuk satu jam mulai. Waktu selesai otomatis {{ $hours }} jam setelahnya.
            @if ($lastStart)
                Jam mulai terakhir untuk paket ini {{ $lastStart }}, karena lapangan tutup pukul
                {{ substr($court->close_time, 0, 5) }}.
            @endif
        </p>

        <div class="legend mb-3" aria-hidden="true">
            <span class="legend__item"><span class="legend__dot"></span> Kosong</span>
            <span class="legend__item"><span class="legend__dot legend__dot--selected"></span> Dipilih</span>
            <span class="legend__item"><span class="legend__dot legend__dot--booked"></span> Tidak tersedia</span>
        </div>

        @if ($freeCount === 0)
            <div class="alert alert-light border">
                Tidak ada jam mulai yang tersedia untuk paket {{ $hours }} jam pada tanggal ini. Coba paket lain,
                tanggal lain, atau lapangan lain.
            </div>
        @endif

        <form method="GET" action="{{ route('bookings.create', $court) }}" id="slot-form">
            <input type="hidden" name="date" value="{{ $dateParam }}">
            <input type="hidden" name="start" id="f-start">
            <input type="hidden" name="end" id="f-end">

            <div class="slot-grid" id="slot-grid" data-price="{{ $packages[$hours] }}" data-hours="{{ $hours }}">
                @foreach ($starts as $slot)
                    @if ($slot['status'] === 'free')
                        <button type="button" class="slot slot--free slot-btn" data-start="{{ $slot['start'] }}"
                            data-end="{{ $slot['end'] }}" aria-pressed="false">
                            <span class="slot__time">{{ $slot['start'] }}</span>
                            <span class="slot__until">s/d {{ $slot['end'] }}</span>
                        </button>
                    @elseif ($slot['status'] === 'booked')
                        <div class="slot slot--booked" aria-disabled="true">
                            <span class="slot__time">{{ $slot['start'] }}</span>
                            <span class="slot__until">Terisi</span>
                        </div>
                    @else
                        <div class="slot slot--past" aria-disabled="true">
                            <span class="slot__time">{{ $slot['start'] }}</span>
                            <span class="slot__until">Lewat</span>
                        </div>
                    @endif
                @endforeach
            </div>

            @guest
                <div class="alert alert-info small mt-3 mb-0">
                    Untuk memesan, kamu perlu masuk atau daftar dulu. Pilihan jammu tidak hilang.
                </div>
            @endguest

            {{-- Bilah ringkasan: muncul begitu ada jam mulai yang dipilih --}}
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
