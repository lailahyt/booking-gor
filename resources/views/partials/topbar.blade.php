@php
    $user = Auth::user();
    $isAdmin = $user && $user->isAdmin();
    $gorName = config('gor.name', config('app.name'));
@endphp

<header class="app-topbar">
    <nav class="navbar navbar-expand-md">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2"
                href="{{ $isAdmin ? route('admin.dashboard') : route('courts.index') }}">
                <span class="brand-mark" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="19" r="2.6" />
                        <path d="M9.6 17 6 5h12l-3.6 12" />
                        <path d="M8.1 10.5h7.8" />
                    </svg>
                </span>
                <span class="brand-name">{{ $gorName }}</span>
            </a>

            @if ($isAdmin)
                {{-- Admin: menu atas, jadi tombol buka-tutup di layar kecil --}}
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Buka menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="adminNav">
                    <ul class="navbar-nav ms-md-4 me-auto gap-md-1 pt-2 pt-md-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.courts.*') ? 'active' : '' }}"
                                href="{{ route('admin.courts.index') }}">Lapangan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}"
                                href="{{ route('admin.bookings.index') }}">Booking</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                                href="{{ route('admin.reports.index') }}">Laporan</a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-3 py-2 py-md-0">
                        <span class="navbar-text small">{{ $user->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Keluar</button>
                        </form>
                    </div>
                </div>
            @else
                {{-- Pelanggan dan tamu: di layar kecil navigasinya ada di menu bawah --}}
                <div class="d-none d-md-flex align-items-center ms-auto gap-1">
                    <a class="nav-link {{ request()->routeIs('courts.*', 'bookings.create', 'bookings.store') ? 'active' : '' }}"
                        href="{{ route('courts.index') }}">Lapangan</a>

                    @auth
                        <a class="nav-link {{ request()->routeIs('bookings.index', 'bookings.show') ? 'active' : '' }}"
                            href="{{ route('bookings.index') }}">Booking Saya</a>
                        <span class="navbar-text small ms-2">{{ $user->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="ms-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Keluar</button>
                        </form>
                    @else
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}"
                            href="{{ route('login') }}">Masuk</a>
                        <a class="btn btn-primary btn-sm ms-2" href="{{ route('register') }}">Daftar</a>
                    @endauth
                </div>
            @endif
        </div>
    </nav>
</header>
