@php
    $siteTitle = $title ?? config('event.name');
    $siteDescription = $description ?? config('event.name') . ': strade bianche, crete senesi e cipressi in sella a una gravel.';
@endphp
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $siteTitle }}</title>
    <meta name="description" content="{{ $siteDescription }}">
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endisset
    <link rel="icon" type="image/svg+xml" href="/images/valdorcia-gravel/logo.svg">
    <meta name="theme-color" content="#f3ecdf">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $siteTitle }}">
    <meta property="og:description" content="{{ $siteDescription }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:locale" content="it_IT">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $siteTitle }}">
    <meta name="twitter:description" content="{{ $siteDescription }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/style.css?v=100">
    @stack('head')
</head>

<body class="{{ $bodyClass ?? '' }}">
    <header class="site-header">
        @include('partials.navbar')
    </header>

    @yield('content')

    @include('partials.footer')
    @include('partials.cookie-banner')

    @stack('scripts')
</body>
</html>
