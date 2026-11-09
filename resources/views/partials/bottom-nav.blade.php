@php
    $user = Auth::user();
    $onCourts = request()->routeIs('courts.*', 'bookings.create', 'bookings.store');
    $onMine = request()->routeIs('bookings.index', 'bookings.show', 'bookings.cancel');
@endphp

<nav class="bottom-nav" aria-label="Menu utama">
    <a href="{{ route('courts.index') }}" class="bottom-nav__item {{ $onCourts ? 'is-active' : '' }}"
        @if ($onCourts) aria-current="page" @endif>
        <span class="bottom-nav__icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="5" width="18" height="14" rx="2" />
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="3" y1="12" x2="21" y2="12" />
            </svg>
        </span>
        <span>Lapangan</span>
    </a>

    @auth
        <a href="{{ route('bookings.index') }}" class="bottom-nav__item {{ $onMine ? 'is-active' : '' }}"
            @if ($onMine) aria-current="page" @endif>
            <span class="bottom-nav__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </span>
            <span>Booking Saya</span>
        </a>

        <div class="dropup">
            <button type="button" class="bottom-nav__item" data-bs-toggle="dropdown" aria-expanded="false"
                aria-label="Menu akun">
                <span class="bottom-nav__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </span>
                <span>Akun</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <span class="dropdown-item-text">
                        <strong class="d-block">{{ $user->name }}</strong>
                        <small class="text-muted">{{ $user->email }}</small>
                    </span>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">Keluar</button>
                    </form>
                </li>
            </ul>
        </div>
    @else
        <a href="{{ route('login') }}" class="bottom-nav__item {{ request()->routeIs('login') ? 'is-active' : '' }}">
            <span class="bottom-nav__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                    <polyline points="10 17 15 12 10 7" />
                    <line x1="15" y1="12" x2="3" y2="12" />
                </svg>
            </span>
            <span>Masuk</span>
        </a>

        <a href="{{ route('register') }}" class="bottom-nav__item {{ request()->routeIs('register') ? 'is-active' : '' }}">
            <span class="bottom-nav__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="8.5" cy="7" r="4" />
                    <line x1="20" y1="8" x2="20" y2="14" />
                    <line x1="23" y1="11" x2="17" y2="11" />
                </svg>
            </span>
            <span>Daftar</span>
        </a>
    @endauth
</nav>
