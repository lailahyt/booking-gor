@php
    $active = old() ? (bool) old('is_active') : $court->is_active ?? true;
@endphp

<div class="mb-3">
    <label for="name" class="form-label">Nama lapangan</label>
    <input id="name" type="text" name="name" maxlength="100"
        class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $court->name ?? '') }}"
        placeholder="Lapangan 1" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="floor_type" class="form-label">Jenis lantai</label>
    <input id="floor_type" type="text" name="floor_type" maxlength="50"
        class="form-control @error('floor_type') is-invalid @enderror"
        value="{{ old('floor_type', $court->floor_type ?? '') }}" placeholder="Karpet, kayu, atau semen">
    @error('floor_type')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Deskripsi</label>
    <textarea id="description" name="description" rows="3" maxlength="1000"
        class="form-control @error('description') is-invalid @enderror">{{ old('description', $court->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-6 mb-3">
        <label for="price_3_hours" class="form-label">Harga paket 3 jam (Rp)</label>
        <input id="price_3_hours" type="number" name="price_3_hours" min="1000" step="1000" inputmode="numeric"
            class="form-control @error('price_3_hours') is-invalid @enderror"
            value="{{ old('price_3_hours', $court->price_3_hours ?? 110000) }}" required>
        @error('price_3_hours')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-6 mb-3">
        <label for="price_4_hours" class="form-label">Harga paket 4 jam (Rp)</label>
        <input id="price_4_hours" type="number" name="price_4_hours" min="1000" step="1000" inputmode="numeric"
            class="form-control @error('price_4_hours') is-invalid @enderror"
            value="{{ old('price_4_hours', $court->price_4_hours ?? 140000) }}" required>
        @error('price_4_hours')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-6 mb-3">
        <label for="open_time" class="form-label">Jam buka</label>
        <input id="open_time" type="time" name="open_time" step="1800"
            class="form-control @error('open_time') is-invalid @enderror"
            value="{{ old('open_time', isset($court) ? substr($court->open_time, 0, 5) : '07:00') }}" required>
        @error('open_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-6 mb-3">
        <label for="close_time" class="form-label">Jam tutup</label>
        <input id="close_time" type="time" name="close_time" step="1800"
            class="form-control @error('close_time') is-invalid @enderror"
            value="{{ old('close_time', isset($court) ? substr($court->close_time, 0, 5) : '23:30') }}" required>
        @error('close_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="form-text mb-3 mt-n2">Jam buka dan tutup boleh pas atau setengah jam, misalnya 07:00 dan 23:30.</div>

<div class="mb-3">
    <label for="slot_step_minutes" class="form-label">Langkah jam mulai</label>
    @php $step = (int) old('slot_step_minutes', $court->slot_step_minutes ?? 30); @endphp
    <select id="slot_step_minutes" name="slot_step_minutes"
        class="form-select @error('slot_step_minutes') is-invalid @enderror" required>
        <option value="30" @selected($step === 30)>Tiap 30 menit (07:00, 07:30, 08:00, ...)</option>
        <option value="60" @selected($step === 60)>Tiap 60 menit (07:00, 08:00, 09:00, ...)</option>
    </select>
    @error('slot_step_minutes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Menentukan jam mulai yang bisa dipilih pelanggan, dihitung dari jam buka.</div>
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
        @checked($active)>
    <label class="form-check-label" for="is_active">Lapangan aktif (bisa dipesan pelanggan)</label>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary" data-loading="Menyimpan...">Simpan</button>
    <a href="{{ route('admin.courts.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>
