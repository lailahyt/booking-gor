@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">Tambah Lapangan</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.courts.store') }}">
                    @csrf
                    @include('admin.courts._form')
                </form>
            </div>
        </div>
    </div>
@endsection
