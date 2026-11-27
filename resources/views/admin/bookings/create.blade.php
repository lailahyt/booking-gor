@extends('layouts.app')

@section('title', 'Booking Manual')

@section('content')
    <div class="container page-narrow">
        @include('partials.flash')

        <a href="{{ route('admin.bookings.index') }}" class="small text-decoration-none">&larr; Kelola Booking</a>
        <h1 class="h4 mt-2 mb-1">Booking Manual</h1>
        <p class="text-muted small mb-3">
            Untuk pesanan lewat WhatsApp atau pelanggan yang datang langsung. Booking langsung berstatus
            <strong>Dikonfirmasi</strong> dan jamnya dikunci seperti booking biasa.
        </p>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.bookings.store') }}" data-once>
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="court_id" class="form-label">Lapangan</label>
                            <select id="court_id" name="court_id"
                                class="form-select @error('court_id') is-invalid @enderror" required>
                                <option value="">Pilih lapangan</option>
                                @foreach ($courts as $court)
                                    <option value="{{ $court->id }}" data-price-3="{{ $court->price_3_hours }}"
                                        data-price-4="{{ $court->price_4_hours }}" @selected(old('court_id') == $court->id)>
                                        {{ $court->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('court_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Tanggal</label>
                            <input id="date" type="date" name="date" min="{{ $minDate }}"
                                max="{{ $maxDate }}" class="form-control @error('date') is-invalid @enderror"
                                value="{{ old('date', $minDate) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="duration" class="form-label">Paket</label>
                            <select id="duration" name="duration"
                                class="form-select @error('duration') is-invalid @enderror" required>
                                @foreach ($packageHours as $hours)
                                    <option value="{{ $hours }}" @selected((int) old('duration', min($packageHours)) === $hours)>
                                        Paket {{ $hours }} jam
                                    </option>
                                @endforeach
                            </select>
                            @error('duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-6 mb-3">
                            <label for="start" class="form-label">Jam mulai</label>
                            <select id="start" name="start" class="form-select @error('start') is-invalid @enderror"
                                data-options="{{ json_encode($startOptions) }}" data-selected="{{ old('start') }}"
                                required>
                            </select>
                            @error('start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-text mb-3 mt-n2">
                        Daftar jam mengikuti jam buka dan langkah waktu lapangan yang dipilih.
                    </div>

                    <div class="note-box mb-3" id="estimate" hidden>
                        Perkiraan total: <strong id="estimate-total"></strong>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_name" class="form-label">Nama pelanggan</label>
                            <input id="customer_name" type="text" name="customer_name" maxlength="100"
                                class="form-control @error('customer_name') is-invalid @enderror"
                                value="{{ old('customer_name') }}" required>
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="customer_phone" class="form-label">Nomor WhatsApp</label>
                            <input id="customer_phone" type="tel" name="customer_phone" inputmode="tel"
                                class="form-control @error('customer_phone') is-invalid @enderror"
                                value="{{ old('customer_phone') }}" placeholder="081234567890" required>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan (opsional)</label>
                        <input id="notes" type="text" name="notes" maxlength="255"
                            class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes') }}">
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="paid" id="paid" value="1"
                            @checked(old('paid'))>
                        <label class="form-check-label" for="paid">Pembayaran sudah diterima (langsung tandai
                            lunas)</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" data-loading="Menyimpan...">Simpan Booking</button>
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Hanya bantuan tampilan: menukar daftar jam mulai (dibuat server) saat lapangan atau paket berubah,
        // dan menampilkan perkiraan total. Server memeriksa ulang semua aturan dan menghitung harga sebenarnya.
        (function() {
            var court = document.getElementById('court_id');
            var pack = document.getElementById('duration');
            var start = document.getElementById('start');
            var box = document.getElementById('estimate');
            var total = document.getElementById('estimate-total');

            if (!court || !pack || !start || !box || !total) {
                return;
            }

            var options = {};

            try {
                options = JSON.parse(start.getAttribute('data-options') || '{}');
            } catch (e) {
                options = {};
            }

            var wanted = start.getAttribute('data-selected') || '';

            function fillStarts() {
                var list = (options[court.value] || {})[pack.value] || [];
                var keep = start.value || wanted;

                start.innerHTML = '';

                if (list.length === 0) {
                    var empty = document.createElement('option');

                    empty.value = '';
                    empty.textContent = court.value ? 'Tidak ada jam mulai' : 'Pilih lapangan dulu';
                    start.appendChild(empty);

                    return;
                }

                list.forEach(function(item) {
                    var option = document.createElement('option');

                    option.value = item.start;
                    option.textContent = item.start + ' - ' + item.end;

                    if (item.start === keep) {
                        option.selected = true;
                    }

                    start.appendChild(option);
                });
            }

            function updateTotal() {
                var option = court.options[court.selectedIndex];
                var price = option ? parseInt(option.getAttribute('data-price-' + pack.value) || '0', 10) : 0;

                if (!price) {
                    box.hidden = true;

                    return;
                }

                total.textContent = 'Rp' + price.toLocaleString('id-ID');
                box.hidden = false;
            }

            function refresh() {
                fillStarts();
                updateTotal();
            }

            court.addEventListener('change', refresh);
            pack.addEventListener('change', refresh);
            refresh();
        })();
    </script>
@endpush
