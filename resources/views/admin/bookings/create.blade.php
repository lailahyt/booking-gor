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
                                    <option value="{{ $court->id }}" data-price="{{ $court->price_per_hour }}"
                                        @selected(old('court_id') == $court->id)>
                                        {{ $court->name }} (Rp{{ number_format($court->price_per_hour, 0, ',', '.') }}/jam)
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
                            <label for="start_hour" class="form-label">Jam mulai</label>
                            <select id="start_hour" name="start_hour"
                                class="form-select @error('start_hour') is-invalid @enderror" required>
                                @foreach (range(0, 23) as $hour)
                                    <option value="{{ $hour }}" @selected((int) old('start_hour', 19) === $hour)>
                                        {{ sprintf('%02d:00', $hour) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('start_hour')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-6 mb-3">
                            <label for="duration" class="form-label">Durasi</label>
                            <select id="duration" name="duration"
                                class="form-select @error('duration') is-invalid @enderror" required>
                                @foreach (range(1, $maxHours) as $hours)
                                    <option value="{{ $hours }}" @selected((int) old('duration', 1) === $hours)>{{ $hours }}
                                        jam</option>
                                @endforeach
                            </select>
                            @error('duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
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
        // Perkiraan total: harga lapangan x durasi. Hanya bantuan tampilan, server yang menghitung harga sebenarnya.
        (function() {
            var court = document.getElementById('court_id');
            var duration = document.getElementById('duration');
            var box = document.getElementById('estimate');
            var total = document.getElementById('estimate-total');

            if (!court || !duration || !box || !total) {
                return;
            }

            function update() {
                var option = court.options[court.selectedIndex];
                var price = option ? parseInt(option.getAttribute('data-price') || '0', 10) : 0;
                var hours = parseInt(duration.value || '0', 10);

                if (!price || !hours) {
                    box.hidden = true;

                    return;
                }

                total.textContent = 'Rp' + (price * hours).toLocaleString('id-ID');
                box.hidden = false;
            }

            court.addEventListener('change', update);
            duration.addEventListener('change', update);
            update();
        })();
    </script>
@endpush
