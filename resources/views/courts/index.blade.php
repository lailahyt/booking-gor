@extends('layouts.app')

@section('title', 'Daftar Lapangan')

@section('content')
    <div class="container">
        @include('partials.flash')

        <section class="hero mb-4">
            <h1>Booking lapangan bulutangkis</h1>
            <p class="mb-3">Lihat jam kosong, pilih jam, dan pesan dalam beberapa ketukan.</p>
            <a href="#lapangan" class="btn btn-light">Lihat lapangan</a>
        </section>

        <h2 class="h5 mb-3" id="lapangan">Pilih lapangan</h2>

        <div class="row g-3 mb-4">
            @forelse ($courts as $court)
                @php $free = $freeToday[$court->id] ?? null; @endphp

                <div class="col-12 col-md-6 col-lg-4">
                    <a href="{{ route('courts.show', $court) }}" class="court-card card h-100">
                        <div class="card-body d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <h3 class="h5 mb-1">{{ $court->name }}</h3>
                                    @if ($court->floor_type)
                                        <div class="text-muted small">Lantai {{ $court->floor_type }}</div>
                                    @endif
                                </div>
                                <div class="court-price">
                                    Rp{{ number_format($court->price_per_hour, 0, ',', '.') }}<small>/jam</small>
                                </div>
                            </div>

                            @if ($court->description)
                                <p class="small text-muted mb-0">
                                    {{ \Illuminate\Support\Str::limit($court->description, 90) }}</p>
                            @endif

                            <div class="mt-auto pt-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <span class="small text-muted">
                                    Buka {{ substr($court->open_time, 0, 5) }} - {{ substr($court->close_time, 0, 5) }}
                                </span>

                                @if ($free === null)
                                    {{-- info jam kosong tidak tersedia --}}
                                @elseif ($free > 0)
                                    <span class="badge bg-success-subtle text-success-emphasis">{{ $free }} jam
                                        kosong hari ini</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Tidak ada jam kosong
                                        hari ini</span>
                                @endif
                            </div>

                            <span class="btn btn-primary mt-2">Lihat jadwal</span>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border mb-0">Belum ada lapangan yang tersedia.</div>
                </div>
            @endforelse
        </div>

        <section class="mb-4">
            <h2 class="h5 mb-3">Cara memesan</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="step">
                        <span class="step__no">1</span>
                        <div>
                            <strong>Pilih lapangan dan jam</strong>
                            <div class="small text-muted">Lihat jam kosong, lalu ketuk jam yang kamu mau (1 sampai 4 jam
                                berurutan).</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step">
                        <span class="step__no">2</span>
                        <div>
                            <strong>Masuk atau daftar</strong>
                            <div class="small text-muted">Cukup nama, email, dan nomor WhatsApp. Pilihan jammu tidak hilang.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step">
                        <span class="step__no">3</span>
                        <div>
                            <strong>Konfirmasi lewat WhatsApp</strong>
                            <div class="small text-muted">Kirim detail booking ke admin. Pembayaran dilakukan di GOR saat
                                datang.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @php $waNumber = preg_replace('/\D/', '', (string) config('gor.whatsapp')); @endphp

        @if ($waNumber !== '')
            <section class="card">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <strong>Ada pertanyaan?</strong>
                        <div class="small text-muted">Hubungi admin lewat WhatsApp.</div>
                    </div>
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                        class="btn btn-outline-primary">
                        Chat WhatsApp Admin
                    </a>
                </div>
            </section>
        @endif
    </div>
@endsection
