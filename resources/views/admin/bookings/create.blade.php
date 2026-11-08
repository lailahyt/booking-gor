@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 720px;">
        <a href="{{ route('admin.bookings.index') }}" class="text-decoration-none small">&larr; Kelola Booking</a>
        <h4 class="mt-2 mb-3">Booking Manual</h4>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <p class="text-muted small">
            Untuk pesanan lewat WhatsApp atau pelanggan yang datang langsung. Booking langsung berstatus
            <strong>Dikonfirmasi</strong> dan jamnya dikunci seperti booking biasa.
        </p>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.bookings.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="court_id" class="form-label">Lapangan</label>
                            <select id="court_id" name="court_id"
                                class="form-select @error('court_id') is-invalid @enderror" required>
                                <option value="">Pilih lapangan</option>
                                @foreach ($courts as $court)
                                    <option value="{{ $court->id }}" @selected(old('court_id') == $court->id)>
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
                        <div class="col-md-6 mb-3">
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

                        <div class="col-md-6 mb-3">
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
                            <input id="customer_phone" type="tel" name="customer_phone"
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

                    <button type="submit" class="btn btn-primary">Simpan Booking</button>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endsection
