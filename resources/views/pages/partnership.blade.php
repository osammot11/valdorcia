@extends('layouts.app')

@php
    $sponsors = config('sponsors');
@endphp

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Sponsor e partner</span>
                <h1>Chi pedala con noi.</h1>
                <p class="lead">Comuni, enti e aziende che rendono possibile {{ config('event.name') }}: aprono le loro strade, riempiono i ristori e vestono i partecipanti.</p>
            </div>
        </section>

        <section>
            <div class="inner">
                <span class="kicker">Sostenitori dell'edizione {{ $sponsors['edition'] }}</span>
                <h2>Con il sostegno di</h2>
                <ul class="logo-grid logo-grid--supporters">
                    @foreach ($sponsors['supporters'] as $item)
                        <li class="logo-tile">
                            <img src="/images/sponsor/{{ $item['logo'] }}" alt="{{ $item['name'] }}" loading="lazy">
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section>
            <div class="inner">
                <span class="kicker">Partner dell'edizione {{ $sponsors['edition'] }}</span>
                <h2>I nostri partner</h2>
                <ul class="logo-grid">
                    @foreach ($sponsors['partners'] as $item)
                        <li class="logo-tile">
                            @if ($item['url'])
                                <a href="{{ $item['url'] }}" target="_blank" rel="noopener" title="{{ $item['name'] }}">
                                    <img src="/images/sponsor/{{ $item['logo'] }}" alt="{{ $item['name'] }}" loading="lazy">
                                </a>
                            @else
                                <img src="/images/sponsor/{{ $item['logo'] }}" alt="{{ $item['name'] }}" loading="lazy">
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="page-band">
            <div class="inner grid-2">
                <div>
                    <span class="kicker">Diventa partner</span>
                    <h2>Il tuo marchio sulle strade bianche.</h2>
                </div>
                <div>
                    <p>Vuoi sostenere l'edizione {{ config('event.edition_year') }}, portare i tuoi prodotti nei ristori o nel pacco gara? Scrivi a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a> o chiama {{ config('event.phone_contact') }} al <a class="hyperlink" href="tel:{{ str_replace(' ', '', config('event.phone')) }}">{{ config('event.phone') }}</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
