{{-- Pesan sementara dari server (session). Panggil dengan @include('partials.flash') di awal isi halaman. --}}
@foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $type)
    @if (session($key))
        <div class="alert alert-{{ $type }} app-flash" role="alert">{{ session($key) }}</div>
    @endif
@endforeach
