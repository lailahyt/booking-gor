@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="mb-4">
            <h3 class="mb-1">Booking Lapangan Bulutangkis</h3>
            <p class="text-muted mb-0">Pilih lapangan, lihat jam kosong, lalu pesan.</p>
        </div>

        <div class="row g-3">
            @forelse ($courts as $court)
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $court->name }}</h5>

                            @if ($court->floor_type)
                                <p class="text-muted small mb-1">Lantai: {{ $court->floor_type }}</p>
                            @endif

                            <p class="mb-1 fw-semibold">
                                Rp{{ number_format($court->price_per_hour, 0, ',', '.') }} / jam
                            </p>
                            <p class="text-muted small mb-3">
                                Buka {{ substr($court->open_time, 0, 5) }} - {{ substr($court->close_time, 0, 5) }}
                            </p>

                            <a href="{{ route('courts.show', $court) }}" class="btn btn-primary mt-auto">
                                Lihat Jadwal
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border">Belum ada lapangan yang tersedia.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
