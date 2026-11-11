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

<div class="mb-3">
    <label for="price_per_hour" class="form-label">Harga per jam (Rp)</label>
    <input id="price_per_hour" type="number" name="price_per_hour" min="1000" step="1000" inputmode="numeric"
        class="form-control @error('price_per_hour') is-invalid @enderror"
        value="{{ old('price_per_hour', $court->price_per_hour ?? 30000) }}" required>
    @error('price_per_hour')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-6 mb-3">
        <label for="open_time" class="form-label">Jam buka</label>
        <input id="open_time" type="time" name="open_time" step="3600"
            class="form-control @error('open_time') is-invalid @enderror"
            value="{{ old('open_time', isset($court) ? substr($court->open_time, 0, 5) : '08:00') }}" required>
        @error('open_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-6 mb-3">
        <label for="close_time" class="form-label">Jam tutup</label>
        <input id="close_time" type="time" name="close_time" step="3600"
            class="form-control @error('close_time') is-invalid @enderror"
            value="{{ old('close_time', isset($court) ? substr($court->close_time, 0, 5) : '23:00') }}" required>
        @error('close_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="form-text mb-3 mt-n2">Jam buka dan tutup harus jam bulat, misalnya 08:00 dan 23:00.</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
        @checked($active)>
    <label class="form-check-label" for="is_active">Lapangan aktif (bisa dipesan pelanggan)</label>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary" data-loading="Menyimpan...">Simpan</button>
    <a href="{{ route('admin.courts.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>
