@extends('layouts.app')

@php
    $routes = config('event.routes');
@endphp

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Percorsi {{ config('event.edition_year') }}</span>
                <h1>Quattro percorsi, una valle.</h1>
                <p class="lead">Dal giro di una mattina al viaggio di due giorni, sulle strade bianche della Val d'Orcia. Per l'edizione {{ config('event.edition_year') }}, che parte da {{ config('event.location') }}, i percorsi saranno tutti rinnovati: tracce e descrizioni sono in via di definizione.</p>
            </div>
        </section>

        @include('partials.page-photo', ['photo' => 'crete', 'alt' => 'Due ciclisti in salita su una strada bianca tra le crete, edizione 2026'])

        <section>
            <div class="inner route-pages">
                @foreach ($routes as $value => $route)
                    <article class="route-page" id="{{ $value }}">
                        <header class="route-page__head">
                            <p class="route__km">{{ $route['km'] }}<small>km</small></p>
                            <div>
                                <h2>{{ $route['name'] }}</h2>
                                <span class="route__level">{{ $route['level'] }}</span>
                            </div>
                        </header>

                        <div class="route-page__body">
                            <div>
                                <p class="route-page__text">Percorso in via di definizione.@isset($route['stages']) Nella quota sono compresi {{ $route['includes'] }}.@endisset</p>

                                <div class="route-page__actions">
                                    <a href="/iscrizione?percorso={{ urlencode($value) }}" class="btn">{{ $registrationOpen ? 'Iscriviti al' : 'Quote del' }} {{ $route['short_name'] ?? $route['name'] }}</a>
                                </div>
                            </div>

                            <dl class="route-page__facts">
                                <div><dt>Distanza</dt><dd>{{ $route['km'] }} km</dd></div>
                                <div><dt>Certificato</dt><dd>{{ \Illuminate\Support\Str::ucfirst($route['certificate']) }}</dd></div>
                                <div><dt>Posti</dt><dd>{{ $route['limit'] ? 'Massimo ' . $route['limit'] : 'Nel limite dei ' . config('event.capacity') . ' totali' }}</dd></div>
                            </dl>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="page-band">
            <div class="inner grid-2">
                <div>
                    <span class="kicker">Tracce {{ config('event.edition_year') }}</span>
                    <h2>In arrivo.</h2>
                </div>
                <div>
                    <p>Tracce GPX, dislivelli, orari di partenza e ristori dei nuovi percorsi saranno pubblicati qui appena definiti dall'organizzazione.</p>
                    <p>Intanto le iscrizioni aprono il 10 dicembre 2026: le quote sono nella pagina <a class="hyperlink" href="/iscrizione">iscrizioni</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
