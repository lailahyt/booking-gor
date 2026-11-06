@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">Dashboard Admin</div>
            <div class="card-body">
                Selamat datang, {{ Auth::user()->name }} di Aplikasi Web Badminton.
            </div>
        </div>
    </div>
@endsection
