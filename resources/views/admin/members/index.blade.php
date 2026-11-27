@extends('layouts.app')

@section('title', 'Member')

@section('content')
    <div class="container">
        @include('partials.flash')

        <div class="page-head">
            <h1 class="h4">Member</h1>
            <a href="{{ route('admin.members.create') }}" class="btn btn-primary">Tambah Member</a>
        </div>

        @if ($members->isEmpty())
            <div class="card empty-state">Belum ada member.</div>
        @else
            {{-- Ponsel: kartu --}}
            <div class="d-md-none d-grid gap-3">
                @foreach ($members as $member)
                    <article class="booking-card">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <div class="fw-semibold">{{ $member->name }}</div>
                            <span class="badge text-bg-{{ $member->isOngoing() ? 'success' : 'secondary' }}">
                                {{ $member->isOngoing() ? 'Berlangsung' : 'Selesai' }}
                            </span>
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $member->phone) }}" target="_blank"
                            rel="noopener" class="small">{{ $member->phone }}</a>

                        <div class="booking-card__meta mt-1">
                            {{ $member->court->name }}
                            &middot; {{ $member->weekdayLabel() }}
                            &middot; {{ substr($member->start_time, 0, 5) }} ({{ $member->duration_hours }} jam)
                        </div>
                        <div class="booking-card__meta">
                            {{ $member->starts_on->locale('id')->translatedFormat('j M Y') }} -
                            {{ $member->endsOn()->locale('id')->translatedFormat('j M Y') }}
                            &middot; {{ $member->active_sessions }}/{{ $member->sessions }} sesi
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <strong>Rp{{ number_format($member->monthly_fee, 0, ',', '.') }}</strong>
                            @if ($member->isPaid())
                                <span class="badge text-bg-success">Lunas</span>
                            @else
                                <span class="badge text-bg-light border">Belum bayar</span>
                            @endif
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('admin.members.show', $member) }}"
                                class="btn btn-outline-primary btn-sm w-100">Detail</a>
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
                                <th>Member</th>
                                <th>Lapangan</th>
                                <th>Jadwal</th>
                                <th>Periode</th>
                                <th>Sesi</th>
                                <th>Biaya</th>
                                <th>Bayar</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($members as $member)
                                <tr>
                                    <td>
                                        {{ $member->name }}
                                        <div class="small">
                                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $member->phone) }}"
                                                target="_blank" rel="noopener">{{ $member->phone }}</a>
                                        </div>
                                    </td>
                                    <td>{{ $member->court->name }}</td>
                                    <td class="text-nowrap">
                                        {{ $member->weekdayLabel() }}, {{ substr($member->start_time, 0, 5) }}
                                        <div class="small text-muted">{{ $member->duration_hours }} jam</div>
                                    </td>
                                    <td class="text-nowrap small">
                                        {{ $member->starts_on->locale('id')->translatedFormat('j M Y') }} -
                                        {{ $member->endsOn()->locale('id')->translatedFormat('j M Y') }}
                                    </td>
                                    <td>{{ $member->active_sessions }}/{{ $member->sessions }}</td>
                                    <td>Rp{{ number_format($member->monthly_fee, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($member->isPaid())
                                            <span class="badge text-bg-success">Lunas</span>
                                        @else
                                            <span class="badge text-bg-light border">Belum</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $member->isOngoing() ? 'success' : 'secondary' }}">
                                            {{ $member->isOngoing() ? 'Berlangsung' : 'Selesai' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.members.show', $member) }}"
                                            class="btn btn-outline-primary btn-sm">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                {{ $members->links() }}
            </div>
        @endif
    </div>
@endsection
