{{-- Tombol aksi admin untuk satu booking. Aturan aksi mana yang tampil ada di Booking::adminActions(). --}}
@php
    $actionLabels = [
        'confirm' => ['Konfirmasi', 'btn-success', null],
        'pay' => ['Tandai Lunas', 'btn-outline-primary', null],
        'complete' => ['Selesai', 'btn-outline-success', null],
        'no_show' => ['Tidak Datang', 'btn-outline-dark', 'Tandai booking ini sebagai tidak datang?'],
        'cancel' => ['Batalkan', 'btn-outline-danger', 'Batalkan booking ini?'],
    ];
@endphp

@foreach ($booking->adminActions() as $action)
    @php [$label, $class, $ask] = $actionLabels[$action]; @endphp

    <form method="POST" action="{{ route('admin.bookings.action', [$booking, $action]) }}" data-once
        @if ($ask) onsubmit="return confirm('{{ $ask }}');" @endif>
        @csrf
        <button type="submit" class="btn btn-sm {{ $class }}"
            data-loading="Memproses...">{{ $label }}</button>
    </form>
@endforeach
