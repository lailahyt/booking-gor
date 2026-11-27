@extends('layouts.app')

@section('title', 'Kelola Lapangan')

@section('content')
    <div class="container">
        @include('partials.flash')

        <div class="page-head">
            <h1 class="h4">Kelola Lapangan</h1>
            <a href="{{ route('admin.courts.create') }}" class="btn btn-primary">Tambah Lapangan</a>
        </div>

        @if ($courts->isEmpty())
            <div class="card empty-state">Belum ada lapangan.</div>
        @else
            {{-- Ponsel: kartu --}}
            <div class="d-md-none d-grid gap-3">
                @foreach ($courts as $court)
                    <article class="booking-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-w-0">
                                <h2 class="h6 mb-1">{{ $court->name }}</h2>
                                <div class="booking-card__meta">
                                    {{ $court->floor_type ?? 'Lantai -' }}
                                    &middot; Buka {{ substr($court->open_time, 0, 5) }} -
                                    {{ substr($court->close_time, 0, 5) }}
                                </div>
                            </div>
                            <span class="badge text-bg-{{ $court->is_active ? 'success' : 'secondary' }}">
                                {{ $court->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <div class="fw-bold mt-2">
                            3 jam Rp{{ number_format($court->price_3_hours, 0, ',', '.') }}
                            &middot; 4 jam Rp{{ number_format($court->price_4_hours, 0, ',', '.') }}
                        </div>
                        <div class="booking-card__meta">Jam mulai tiap {{ $court->stepMinutes() }} menit</div>

                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('admin.courts.edit', $court) }}"
                                class="btn btn-outline-primary btn-sm flex-fill">Ubah</a>

                            <form method="POST" action="{{ route('admin.courts.toggle', $court) }}" class="flex-fill"
                                data-once
                                @if ($court->is_active) onsubmit="return confirm('Nonaktifkan lapangan ini? Pelanggan tidak bisa memesannya lagi.');" @endif>
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                    {{ $court->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Layar lebar: tabel --}}
            <div class="card d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-admin table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Lantai</th>
                                <th>Harga paket</th>
                                <th>Jam operasional</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($courts as $court)
                                <tr>
                                    <td class="fw-semibold">{{ $court->name }}</td>
                                    <td>{{ $court->floor_type ?? '-' }}</td>
                                    <td>
                                        3 jam Rp{{ number_format($court->price_3_hours, 0, ',', '.') }}<br>
                                        4 jam Rp{{ number_format($court->price_4_hours, 0, ',', '.') }}
                                    </td>
                                    <td>{{ substr($court->open_time, 0, 5) }} - {{ substr($court->close_time, 0, 5) }}
                                        <div class="small text-muted">Jam mulai tiap {{ $court->stepMinutes() }} menit
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $court->is_active ? 'success' : 'secondary' }}">
                                            {{ $court->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.courts.edit', $court) }}"
                                            class="btn btn-sm btn-outline-primary">Ubah</a>

                                        <form method="POST" action="{{ route('admin.courts.toggle', $court) }}"
                                            class="d-inline" data-once
                                            @if ($court->is_active) onsubmit="return confirm('Nonaktifkan lapangan ini? Pelanggan tidak bisa memesannya lagi.');" @endif>
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                {{ $court->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
