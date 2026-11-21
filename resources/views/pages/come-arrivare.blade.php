@extends('layouts.app')

@php
    $maps = fn (string $place) => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($place . ', Siena');
    $places = [
        [
            'name' => config('event.location'),
            'role' => 'Partenza',
            'text' => "La VI edizione parte da Montalcino. Luogo esatto di partenza, ritiro dei pacchi gara e iscrizioni in loco sono in via di definizione.",
            'query' => config('event.location'),
        ],
        [
            'name' => 'Radicofani',
            'role' => 'Tappa del Luculliano',
            'text' => "Il viaggio di due giorni fa tappa a Radicofani: cena del sabato, colazione e trasporto bagagli sono compresi nella quota.",
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
                <p class="lead">{{ config('event.name') }} parte da {{ config('event.location') }}, in provincia di Siena, con la tappa del Luculliano a Radicofani.</p>
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
                    <p>Indicazioni su parcheggi, pernottamento e cene convenzionate saranno pubblicate con il <a class="hyperlink" href="/programma">programma</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
