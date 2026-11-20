@php
    $siteTitle = $title ?? config('event.name');
    $siteDescription = $description ?? config('event.name') . ': strade bianche, crete senesi e cipressi in sella a una gravel.';
@endphp
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@hasSection('title')@yield('title')@else{{ $siteTitle }}@endif</title>
    <meta name="description" content="{{ $siteDescription }}">
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endisset
    <link rel="icon" type="image/png" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="apple-touch-icon" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <meta name="theme-color" content="#f3ecdf">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $siteTitle }}">
    <meta property="og:description" content="{{ $siteDescription }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:locale" content="it_IT">
    <meta property="og:image" content="{{ url('/images/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ config('event.name') }}, {{ config('event.date_label') }} a {{ config('event.location') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ url('/images/og-image.png') }}">
    <meta name="twitter:title" content="{{ $siteTitle }}">
    <meta name="twitter:description" content="{{ $siteDescription }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/style.css?v=109">
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
