@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <div class="container auth-page">
        @include('partials.flash')

        <div class="card">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Masuk</h1>
                <p class="text-muted mb-4">Masuk untuk memesan lapangan dan melihat booking kamu.</p>

                <form method="POST" action="{{ route('login') }}" data-once>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror" required autocomplete="email"
                            inputmode="email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input id="password" type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" required
                                autocomplete="current-password">
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="#password"
                                aria-pressed="false">Tampilkan</button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" data-loading="Memproses...">Masuk</button>
                </form>
            </div>
        </div>

        <p class="text-center mt-3 mb-0">
            Belum punya akun? <a href="{{ route('register') }}" class="fw-semibold">Daftar</a>
        </p>
    </div>
@endsection
