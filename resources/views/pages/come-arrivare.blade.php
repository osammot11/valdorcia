@extends('layouts.app')

@php
    $maps = fn (string $place) => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($place . ', Siena');
    $places = [
        [
            'name' => config('event.location'),
            'role' => 'Segreteria e ritiro pacchi gara',
            'text' => "Qui c'è la segreteria organizzativa: alla Pro Loco si ritira il pacco gara il sabato e la domenica mattina, e si fanno le iscrizioni in loco. È anche l'arrivo della seconda tappa del Luculliano.",
            'query' => 'Pro Loco ' . config('event.location'),
        ],
        [
            'name' => config('event.start_place'),
            'role' => 'Partenza',
            'text' => 'Dal borgo medievale partono Frugale, Saporito e Goloso la domenica e la prima tappa del Luculliano il sabato.',
            'query' => config('event.start_place'),
        ],
        [
            'name' => 'Radicofani',
            'role' => 'Tappa del Luculliano',
            'text' => "Arrivo della prima tappa e ripartenza della seconda. Cena del sabato, colazione e trasporto bagagli sono compresi nella quota; per dormire c'è la Pro Loco di Radicofani.",
            'query' => 'Radicofani',
        ],
    ];
@endphp

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Come arrivare</span>
                <h1>I luoghi della manifestazione.</h1>
                <p class="lead">{{ config('event.name') }} si svolge nel comune di {{ config('event.location') }}, in provincia di Siena, con la tappa del Luculliano a Radicofani.</p>
            </div>
        </section>

        <section>
            <div class="inner places">
                @foreach ($places as $place)
                    <article class="place">
                        <span class="kicker">{{ $place['role'] }}</span>
                        <h2>{{ $place['name'] }}</h2>
                        <p>{{ $place['text'] }}</p>
                        <a class="btn btn-ghost" href="{{ $maps($place['query']) }}" target="_blank" rel="noopener">Apri la mappa</a>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="page-band">
            <div class="inner grid-2">
                <div>
                    <span class="kicker">Informazioni</span>
                    <h2>Parcheggi e pernottamento.</h2>
                </div>
                <div>
                    <p>Per parcheggi e indicazioni stradali scrivi a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a> o chiama {{ config('event.phone_contact') }} al <a class="hyperlink" href="tel:{{ str_replace(' ', '', config('event.phone')) }}">{{ config('event.phone') }}</a>.</p>
                    <p>Per dormire e mangiare a {{ config('event.location') }} trovi i contatti della Pro Loco nel <a class="hyperlink" href="/programma#dormire">programma</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
