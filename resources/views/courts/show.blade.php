@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('courts.index') }}" class="text-decoration-none small">&larr; Semua lapangan</a>

        <div class="mt-2 mb-3">
            <h4 class="mb-1">{{ $court->name }}</h4>
            <p class="text-muted mb-0">
                Rp{{ number_format($court->price_per_hour, 0, ',', '.') }} / jam
                @if ($court->floor_type)
                    &middot; Lantai {{ $court->floor_type }}
                @endif
            </p>
        </div>

        <form method="GET" action="{{ route('courts.show', $court) }}" class="row g-2 align-items-end mb-4">
            <div class="col-auto">
                <label for="date" class="form-label mb-1">Tanggal</label>
                <input type="date" id="date" name="date" class="form-control" value="{{ $date->toDateString() }}"
                    min="{{ $minDate }}" max="{{ $maxDate }}" onchange="this.form.submit()">
            </div>
            <div class="col-auto">
                <noscript><button type="submit" class="btn btn-outline-primary">Lihat</button></noscript>
            </div>
        </form>

        <div class="d-flex gap-3 small mb-3">
            <span><span class="badge bg-success-subtle text-success border border-success">&nbsp;</span> Kosong</span>
            <span><span class="badge bg-secondary-subtle text-secondary border">&nbsp;</span> Terisi</span>
            <span><span class="badge bg-light text-muted border">&nbsp;</span> Sudah lewat</span>
        </div>

        <div class="row g-2">
            @foreach ($slots as $slot)
                <div class="col-6 col-md-4 col-lg-3">
                    @if ($slot['status'] === 'free')
                        <div class="border border-success rounded p-2 text-center bg-success-subtle text-success">
                            {{ $slot['label'] }}
                            <div class="small">Kosong</div>
                        </div>
                    @elseif ($slot['status'] === 'booked')
                        <div class="border rounded p-2 text-center bg-secondary-subtle text-secondary">
                            {{ $slot['label'] }}
                            <div class="small">Terisi</div>
                        </div>
                    @else
                        <div class="border rounded p-2 text-center bg-light text-muted">
                            {{ $slot['label'] }}
                            <div class="small">Sudah lewat</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection
