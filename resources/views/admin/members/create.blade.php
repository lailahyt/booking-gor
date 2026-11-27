@extends('layouts.app')

@section('title', 'Tambah Member')

@section('content')
    @php
        $val = fn(string $key, $default = '') => old($key, $prefill[$key] ?? $default);
    @endphp

    <div class="container page-narrow">
        @include('partials.flash')

        <a href="{{ route('admin.members.index') }}" class="small text-decoration-none">&larr; Member</a>
        <h1 class="h4 mt-2 mb-1">{{ $previous ? 'Perpanjang Member' : 'Tambah Member' }}</h1>
        <p class="text-muted small mb-3">
            Sistem membuat <strong>{{ $sessions }} booking</strong> sekaligus, satu per minggu di hari dan jam yang
            sama, langsung berstatus <strong>Dikonfirmasi</strong> sehingga jamnya terkunci. Kalau ada satu tanggal yang
            bentrok, tidak ada yang dibuat.
        </p>

        @if ($previous)
            <div class="note-box mb-3">
                Form diisi dari periode sebelumnya ({{ $previous->name }}). Ubah hari atau jam kalau member ingin
                ganti jadwal.
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.members.store') }}" data-once>
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama member</label>
                            <input id="name" type="text" name="name" maxlength="100"
                                class="form-control @error('name') is-invalid @enderror" value="{{ $val('name') }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Nomor WhatsApp</label>
                            <input id="phone" type="tel" name="phone" inputmode="tel"
                                class="form-control @error('phone') is-invalid @enderror" value="{{ $val('phone') }}"
                                placeholder="081234567890" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="court_id" class="form-label">Lapangan</label>
                            <select id="court_id" name="court_id"
                                class="form-select @error('court_id') is-invalid @enderror" required>
                                <option value="">Pilih lapangan</option>
                                @foreach ($courts as $court)
                                    <option value="{{ $court->id }}" @selected($val('court_id') == $court->id)>
                                        {{ $court->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('court_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="starts_on" class="form-label">Tanggal sesi pertama</label>
                            <input id="starts_on" type="date" name="starts_on" min="{{ $minDate }}"
                                max="{{ $maxDate }}" class="form-control @error('starts_on') is-invalid @enderror"
                                value="{{ $val('starts_on', $minDate) }}" required>
                            @error('starts_on')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" id="weekday-hint"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="duration" class="form-label">Paket</label>
                            <select id="duration" name="duration"
                                class="form-select @error('duration') is-invalid @enderror" required>
                                @foreach ($packageHours as $hours)
                                    <option value="{{ $hours }}" @selected((int) $val('duration', min($packageHours)) === $hours)>
                                        {{ $hours }} jam per main
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
                                data-options="{{ json_encode($startOptions) }}" data-selected="{{ $val('start') }}"
                                required>
                            </select>
                            @error('start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="monthly_fee" class="form-label">Biaya periode (Rp)</label>
                        <input id="monthly_fee" type="number" name="monthly_fee" inputmode="numeric" min="1000"
                            step="1" class="form-control @error('monthly_fee') is-invalid @enderror"
                            value="{{ $val('monthly_fee', $defaultFee) }}" required>
                        @error('monthly_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Dibagi rata ke {{ $sessions }} sesi. Sisa pembagian masuk ke sesi pertama.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan (opsional)</label>
                        <input id="notes" type="text" name="notes" maxlength="255"
                            class="form-control @error('notes') is-invalid @enderror" value="{{ $val('notes') }}">
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="paid" id="paid" value="1"
                            @checked(old('paid'))>
                        <label class="form-check-label" for="paid">Pembayaran sudah diterima (tandai seluruh periode
                            lunas)</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" data-loading="Menyimpan...">Simpan Member</button>
                        <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Hanya bantuan tampilan: menukar daftar jam mulai (dibuat server) saat lapangan atau paket berubah,
        // dan menampilkan nama hari. Server memeriksa ulang semua aturan.
        (function() {
            var court = document.getElementById('court_id');
            var pack = document.getElementById('duration');
            var start = document.getElementById('start');
            var dateInput = document.getElementById('starts_on');
            var hint = document.getElementById('weekday-hint');

            if (!court || !pack || !start) {
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

            function showWeekday() {
                if (!dateInput || !hint) {
                    return;
                }

                var parts = (dateInput.value || '').split('-');

                if (parts.length !== 3) {
                    hint.textContent = '';

                    return;
                }

                var day = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));

                hint.textContent = 'Member main tiap ' +
                    day.toLocaleDateString('id-ID', {
                        weekday: 'long'
                    }) + '.';
            }

            court.addEventListener('change', fillStarts);
            pack.addEventListener('change', fillStarts);

            if (dateInput) {
                dateInput.addEventListener('change', showWeekday);
            }

            fillStarts();
            showWeekday();
        })();
    </script>
@endpush
