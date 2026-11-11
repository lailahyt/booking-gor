@extends('layouts.app')

@section('title', 'Ubah Lapangan')

@section('content')
    <div class="container page-narrow">
        @include('partials.flash')

        <a href="{{ route('admin.courts.index') }}" class="small text-decoration-none">&larr; Kelola Lapangan</a>
        <h1 class="h4 mt-2 mb-3">Ubah {{ $court->name }}</h1>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.courts.update', $court) }}" data-once>
                    @csrf
                    @method('PUT')
                    @include('admin.courts._form')
                </form>
            </div>
        </div>
    </div>
@endsection
