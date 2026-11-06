@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Kelola Lapangan</h4>
            <a href="{{ route('admin.courts.create') }}" class="btn btn-primary">Tambah Lapangan</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Lantai</th>
                            <th>Harga/jam</th>
                            <th>Jam operasional</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courts as $court)
                            <tr>
                                <td>{{ $court->name }}</td>
                                <td>{{ $court->floor_type ?? '-' }}</td>
                                <td>Rp{{ number_format($court->price_per_hour, 0, ',', '.') }}</td>
                                <td>{{ substr($court->open_time, 0, 5) }} - {{ substr($court->close_time, 0, 5) }}</td>
                                <td>
                                    @if ($court->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.courts.edit', $court) }}"
                                        class="btn btn-sm btn-outline-primary">Ubah</a>
                                    <form method="POST" action="{{ route('admin.courts.toggle', $court) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            {{ $court->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada lapangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
