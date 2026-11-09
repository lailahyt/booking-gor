<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#166534">

    <title>
        @hasSection('title')
            @yield('title') &middot;
        @endif{{ config('gor.name', config('app.name')) }}
    </title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

@php
    $authUser = Auth::user();
    $isAdmin = $authUser && $authUser->isAdmin();
@endphp

<body class="app-body {{ $isAdmin ? '' : 'has-bottom-nav' }}">
    <div id="app">
        @include('partials.topbar')

        <main class="app-main">
            @yield('content')
        </main>

        @unless ($isAdmin)
            @include('partials.bottom-nav')
        @endunless
    </div>

    @stack('scripts')
</body>

</html>
