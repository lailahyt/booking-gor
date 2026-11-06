@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">Ubah Lapangan</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.courts.update', $court) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.courts._form')
                </form>
            </div>
        </div>
    </div>
@endsection
