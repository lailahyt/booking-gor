@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <div class="container auth-page">
        @include('partials.flash')

        <div class="card">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Buat akun</h1>
                <p class="text-muted mb-4">Cukup beberapa isian, lalu kamu bisa langsung memesan.</p>

                <form method="POST" action="{{ route('register') }}" data-once>
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror" required autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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
                        <label for="phone" class="form-label">Nomor WhatsApp</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror" required autocomplete="tel"
                            inputmode="tel" placeholder="081234567890">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Dipakai admin untuk menghubungimu soal booking.</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input id="password" type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" required
                                autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="#password"
                                aria-pressed="false">Tampilkan</button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @else
                            <div class="form-text">Minimal 8 karakter.</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">Ulangi password</label>
                        <input id="password-confirm" type="password" name="password_confirmation" class="form-control"
                            required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100" data-loading="Memproses...">Daftar</button>
                </form>
            </div>
        </div>

        <p class="text-center mt-3 mb-0">
            Sudah punya akun? <a href="{{ route('login') }}" class="fw-semibold">Masuk</a>
        </p>
    </div>
@endsection
