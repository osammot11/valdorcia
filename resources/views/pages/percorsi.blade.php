@extends('layouts.app')

@php
    $routes = config('event.routes');
    $start = config('event.start_place');
    $sunday = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::before(config('event.sunday_label'), ' '));
@endphp

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Percorsi {{ config('event.edition_year') }}</span>
                <h1>Quattro percorsi, una valle.</h1>
                <p class="lead">Dal giro di una mattina al viaggio di due giorni, sulle strade bianche della Val d'Orcia. Tutti i percorsi sono aperti a gravel, mtb, bici da strada ed e-bike.</p>
            </div>
        </section>

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
                                <p class="route-page__text">{{ $route['text'] }}</p>
                                @isset($route['stages'])
                                    <p class="route-page__text">{{ $route['stages'] }} Nella quota sono compresi {{ $route['includes'] }}.</p>
                                @endisset

                                <div class="route-page__actions">
                                    <a href="/iscrizione?percorso={{ urlencode($value) }}" class="btn">Iscriviti al {{ $route['short_name'] ?? $route['name'] }}</a>
                                    @foreach ($route['gpx'] ?? [] as $label => $url)
                                        <a href="{{ $url }}" class="btn btn-ghost" download>GPX · {{ $label }}</a>
                                    @endforeach
                                </div>
                            </div>

                            <dl class="route-page__facts">
                                <div><dt>Distanza</dt><dd>{{ $route['km'] }} km</dd></div>
                                <div><dt>Dislivello</dt><dd>{{ number_format($route['elevation'], 0, ',', '.') }} m</dd></div>
                                <div><dt>Partenza</dt><dd>{{ isset($route['stages']) ? \Illuminate\Support\Str::ucfirst($route['start']) : \Illuminate\Support\Str::ucfirst($sunday) . ' ' . $route['start'] }}, {{ $start }}</dd></div>
                                <div><dt>Certificato</dt><dd>{{ \Illuminate\Support\Str::ucfirst($route['certificate']) }}</dd></div>
                                <div><dt>Segnaletica</dt><dd>{{ isset($route['stages']) ? 'Non segnalato, traccia GPX' : 'Segnalato' }}</dd></div>
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
                    <span class="kicker">Segnaletica e tracce</span>
                    <h2>Come orientarsi.</h2>
                </div>
                <div>
                    <p>Frugale, Saporito e Goloso sono segnalati con cartelli gialli e frecce marroni. Il Luculliano non ha segnaletica: si segue la traccia GPX, e servono luci anteriori e posteriori.</p>
                    <p>Le tracce GPX qui sopra sono quelle dell'edizione 2026: le tracce {{ config('event.edition_year') }} saranno pubblicate dall'organizzazione e possono cambiare per esigenze organizzative.</p>
                    <p>Orari e ritiro dei pacchi gara sono nel <a class="hyperlink" href="/programma">programma</a>, i requisiti per partecipare nel <a class="hyperlink" href="/regolamento">regolamento</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
