@extends('layouts.app')

@php
    $routes = config('event.routes');
@endphp

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Programma {{ config('event.edition_year') }}</span>
                <h1>Due giorni in Val d'Orcia.</h1>
                <p class="lead">{{ config('event.location') }} e Radicofani, {{ config('event.date_label') }}. Gli orari seguono la formula dell'ultima edizione e verranno confermati dall'organizzazione.</p>
            </div>
        </section>

        <section>
            <div class="inner program">
                <article class="program__day">
                    <header>
                        <span class="kicker">Giorno 1</span>
                        <h2>{{ config('event.saturday_label') }}</h2>
                    </header>
                    <ol class="timeline">
                        <li>
                            <time>10:00 – 12:00<br>14:30 – 19:30</time>
                            <div>
                                <h3>Ritiro pacchi gara</h3>
                                <p>Alla Pro Loco di {{ config('event.location') }}. Si possono fare anche nuove iscrizioni in loco a {{ number_format(config('event.onsite_price'), 0) }} €, tranne che per il Luculliano. Chi fa il Luculliano può ritirare il pacco anche il venerdì dalle 17:00 alle 19:30.</p>
                            </div>
                        </li>
                        <li>
                            <time>12:00</time>
                            <div>
                                <h3>Partenza del Luculliano, 1ª tappa</h3>
                                <p>Dal borgo medievale di {{ config('event.start_place') }} a Radicofani, circa 100 km e 2.400 m di dislivello. Percorso non segnalato, con traccia GPX; luci obbligatorie. Il servizio bagagli da e per Radicofani è incluso.</p>
                            </div>
                        </li>
                        <li>
                            <time>15:30</time>
                            <div>
                                <h3>Visita alle Sorgenti del Vivo</h3>
                                <p>Escursione guidata a cura del Parco Vivo, a quota 1003 metri. Ritrovo alle 15:00 alla Pro Loco di {{ config('event.location') }}; prenotazione al {{ config('event.phone') }}.</p>
                            </div>
                        </li>
                        <li>
                            <time>Sera</time>
                            <div>
                                <h3>Cena convenzionata</h3>
                                <p>Per il Luculliano a Radicofani, con la Pro Loco locale (inclusa nella quota). Per gli altri percorsi a {{ config('event.location') }}, su prenotazione alla Pro Loco.</p>
                            </div>
                        </li>
                    </ol>
                </article>

                <article class="program__day">
                    <header>
                        <span class="kicker">Giorno 2</span>
                        <h2>{{ config('event.sunday_label') }}</h2>
                    </header>
                    <ol class="timeline">
                        <li>
                            <time>6:15 – 8:50</time>
                            <div>
                                <h3>Ritiro pacchi gara</h3>
                                <p>Alla Pro Loco di {{ config('event.location') }}, per chi non l'ha ritirato il sabato (escluso Luculliano).</p>
                            </div>
                        </li>
                        <li>
                            <time>7:00</time>
                            <div>
                                <h3>Partenza Goloso · Luculliano 2ª tappa</h3>
                                <p>Il Goloso ({{ $routes['goloso']['km'] }} km) parte da {{ config('event.start_place') }}. Il Luculliano riparte da Radicofani per circa 120 km e 2.400 m di dislivello, con arrivo a {{ config('event.location') }}; colazione inclusa.</p>
                            </div>
                        </li>
                        <li>
                            <time>8:00</time>
                            <div>
                                <h3>Partenza Saporito</h3>
                                <p>{{ $routes['saporito']['km'] }} km da {{ config('event.start_place') }}.</p>
                            </div>
                        </li>
                        <li>
                            <time>9:00</time>
                            <div>
                                <h3>Partenza Frugale</h3>
                                <p>{{ $routes['frugale']['km'] }} km da {{ config('event.start_place') }}.</p>
                            </div>
                        </li>
                        <li>
                            <time>9:30</time>
                            <div>
                                <h3>Trekking alle Rocche</h3>
                                <p>Escursione guidata a piedi dopo la partenza del Frugale, con visita alle Rocche di {{ config('event.location') }}. Non serve prenotare.</p>
                            </div>
                        </li>
                    </ol>
                </article>
            </div>
        </section>

        <section class="page-band">
            <div class="inner grid-2">
                <div>
                    <span class="kicker">Dormire e mangiare</span>
                    <h2>Pernottamenti e cene.</h2>
                </div>
                <div>
                    <p>Per pernottare e prenotare la cena convenzionata a {{ config('event.location') }}: Pro Loco, <a class="hyperlink" href="mailto:prolococastiglionedorcia@gmail.com">prolococastiglionedorcia@gmail.com</a>.</p>
                    <p>Per il Luculliano a Radicofani: Pro Loco, <a class="hyperlink" href="mailto:prolocoradicofani@gmail.com">prolocoradicofani@gmail.com</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
