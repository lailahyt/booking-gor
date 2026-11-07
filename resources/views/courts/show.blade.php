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

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

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

        <div class="d-flex flex-wrap gap-3 small mb-3">
            <span><span class="badge bg-success-subtle text-success border border-success">&nbsp;</span> Kosong</span>
            <span><span class="badge bg-success border border-success">&nbsp;</span> Dipilih</span>
            <span><span class="badge bg-secondary-subtle text-secondary border">&nbsp;</span> Terisi</span>
            <span><span class="badge bg-light text-muted border">&nbsp;</span> Sudah lewat</span>
        </div>

        <form method="GET" action="{{ route('bookings.create', $court) }}" id="slot-form">
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <input type="hidden" name="start" id="f-start">
            <input type="hidden" name="end" id="f-end">

            <div class="row g-2" id="slot-grid" data-price="{{ $court->price_per_hour }}"
                data-max="{{ \App\Services\BookingService::MAX_HOURS }}">
                @foreach ($slots as $slot)
                    <div class="col-6 col-md-4 col-lg-3">
                        @if ($slot['status'] === 'free')
                            <button type="button" class="slot-btn btn btn-outline-success w-100"
                                data-index="{{ $loop->index }}" data-start="{{ $slot['start'] }}"
                                data-end="{{ $slot['end'] }}">
                                {{ $slot['label'] }}
                                <div class="small">Kosong</div>
                            </button>
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

            <div class="mt-3 d-flex flex-wrap align-items-center gap-3">
                <div id="summary" class="text-muted">
                    Pilih jam yang ingin dipesan (maksimal {{ \App\Services\BookingService::MAX_HOURS }} jam berurutan).
                </div>
                <button type="submit" id="btn-continue" class="btn btn-primary ms-auto" disabled>Lanjut Pesan</button>
            </div>

            @guest
                <div class="alert alert-info small mt-3 mb-0">
                    Untuk memesan, kamu perlu login atau daftar dulu. Setelah itu kamu dikembalikan ke pilihan jam ini.
                </div>
            @endguest
        </form>
    </div>

    <script>
        (function() {
            var grid = document.getElementById('slot-grid');
            if (!grid) return;

            var price = parseInt(grid.dataset.price, 10);
            var maxHours = parseInt(grid.dataset.max, 10);
            var buttons = Array.prototype.slice.call(grid.querySelectorAll('.slot-btn'));
            var fStart = document.getElementById('f-start');
            var fEnd = document.getElementById('f-end');
            var summary = document.getElementById('summary');
            var btn = document.getElementById('btn-continue');
            var defaultText = summary.textContent;

            // daftar nomor urut slot yang dipilih, selalu berurutan
            var selected = [];

            function byIndex(i) {
                return buttons.filter(function(b) {
                    return parseInt(b.dataset.index, 10) === i;
                })[0];
            }

            function render() {
                buttons.forEach(function(b) {
                    var on = selected.indexOf(parseInt(b.dataset.index, 10)) !== -1;
                    b.classList.toggle('btn-success', on);
                    b.classList.toggle('btn-outline-success', !on);
                });

                if (selected.length === 0) {
                    fStart.value = '';
                    fEnd.value = '';
                    btn.disabled = true;
                    summary.textContent = defaultText;
                    return;
                }

                var first = byIndex(selected[0]);
                var last = byIndex(selected[selected.length - 1]);

                fStart.value = first.dataset.start.slice(0, 5);
                fEnd.value = last.dataset.end.slice(0, 5);
                btn.disabled = false;

                summary.textContent = fStart.value + ' - ' + fEnd.value +
                    ' (' + selected.length + ' jam) \u00B7 Rp' +
                    (selected.length * price).toLocaleString('id-ID');
            }

            buttons.forEach(function(b) {
                b.addEventListener('click', function() {
                    var i = parseInt(b.dataset.index, 10);
                    var min = selected[0];
                    var max = selected[selected.length - 1];

                    if (selected.indexOf(i) !== -1) {
                        if (selected.length === 1) {
                            selected = [];
                        } else if (i === max) {
                            selected.pop();
                        } else if (i === min) {
                            selected.shift();
                        } else {
                            selected = [i]; // klik di tengah: mulai pilihan baru
                        }
                    } else if (selected.length > 0 && i === max + 1 && selected.length < maxHours) {
                        selected.push(i);
                    } else if (selected.length > 0 && i === min - 1 && selected.length < maxHours) {
                        selected.unshift(i);
                    } else {
                        selected = [i]; // tidak berurutan atau batas jam tercapai: mulai pilihan baru
                    }

                    render();
                });
            });
        })();
    </script>
@endsection
