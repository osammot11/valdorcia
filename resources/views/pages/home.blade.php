@extends('layouts.app')

@php
    $routes = config('event.routes');
    $kmValues = array_column($routes, 'km');
    $maxKm = max($kmValues);
@endphp

@push('head')
    <script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'SportsEvent',
        'name' => config('event.name') . ' ' . config('event.edition_year'),
        'sport' => 'Gravel cycling',
        'startDate' => config('event.date_iso'),
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus' => 'https://schema.org/EventScheduled',
        'url' => url('/'),
        'description' => 'Percorsi gravel da ' . min($kmValues) . ' a ' . $maxKm . " km sulle strade bianche della Val d'Orcia.",
        'location' => [
            '@type' => 'Place',
            'name' => config('event.location'),
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => config('event.location'), 'addressRegion' => 'Toscana', 'addressCountry' => 'IT'],
        ],
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@section('content')
    <div class="hero">
        <div class="hero__text">
            <p class="hero__meta">
                <span>{{ config('event.location') }}</span>
                <span>{{ config('event.date_label') }}</span>
            </p>
            <h1 class="hero__title">Val d'Orcia<span>Gravel</span></h1>
            <p class="lead hero__copy">Strade bianche, crete e filari di cipressi. {{ count($routes) }} percorsi da {{ min($kmValues) }} a {{ $maxKm }} km in uno dei paesaggi più fotografati d'Italia, da pedalare al tuo ritmo.</p>
            <div class="hero__actions">
                <a href="/iscrizione" class="btn">Iscriviti</a>
                <a href="#percorsi" class="btn btn-ghost">Scegli il percorso</a>
            </div>
        </div>

        <svg class="hero__land" viewBox="0 0 1440 380" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
            <circle cx="1130" cy="120" r="54" fill="#c8642d" opacity=".9"/>
            <path d="M0 210 C180 150 330 160 470 196 S760 250 930 200 S1250 120 1440 170 V380 H0Z" fill="#e2c48e"/>
            <path d="M0 250 C160 214 300 220 450 250 S720 300 900 262 S1230 200 1440 236 V380 H0Z" fill="#d6a95c"/>
            <path d="M0 300 C200 262 380 270 560 298 S900 330 1080 296 S1330 262 1440 280 V380 H0Z" fill="#8c8a4f"/>
            <path d="M0 340 C220 318 420 322 620 340 S1000 362 1200 340 S1380 326 1440 332 V380 H0Z" fill="#3d4630"/>
            <path d="M260 380 C330 340 420 318 560 300 S820 262 930 236 S1060 206 1140 196" fill="none" stroke="#f3ecdf" stroke-width="7" stroke-linecap="round" stroke-dasharray="20 14"/>
            <g fill="#2a3122">
                @foreach ([[960, 218], [990, 214], [1020, 209], [1050, 204], [1080, 199], [1110, 194]] as $i => [$x, $y])
                    @php $h = 46 + ($i % 2) * 8; @endphp
                    <path d="M{{ $x }} {{ $y }} C{{ $x - 8 }} {{ $y - $h * .45 }} {{ $x - 5 }} {{ $y - $h * .8 }} {{ $x }} {{ $y - $h }} C{{ $x + 5 }} {{ $y - $h * .8 }} {{ $x + 8 }} {{ $y - $h * .45 }} {{ $x }} {{ $y }}Z"/>
                @endforeach
                <path d="M300 268 C292 250 294 228 300 210 C306 228 308 250 300 268Z"/>
            </g>
        </svg>
    </div>

    <div class="facts" aria-label="L'evento in breve">
        <div><strong>{{ count($routes) }} percorsi</strong><span>{{ implode(' · ', $kmValues) }} km</span></div>
        <div><strong>Strade bianche</strong><span>Sterrato, crete e asfalto</span></div>
        <div><strong>{{ config('event.location') }}</strong><span>Partenza e arrivo</span></div>
        <div><strong>{{ config('event.date_label') }}</strong><span>{{ config('event.region') }}</span></div>
    </div>

    <main>
        <section>
            <div class="inner intro">
                <div>
                    <span class="kicker">La pedalata</span>
                    <h2>La valle si capisce solo sullo sterrato.</h2>
                </div>
                <div class="intro__body">
                    <p>La Val d'Orcia è patrimonio UNESCO dal 2004. La conosci dalle cartoline: colline morbide, poderi isolati, file di cipressi sul crinale. Quello che le cartoline non mostrano sono le strade bianche che le tengono insieme.</p>
                    <p>{{ config('event.name') }} le mette in fila in un'unica giornata, con partenza e arrivo a {{ config('event.location') }}. Scegli la distanza che fa per te: si pedala per il paesaggio, i ristori e la compagnia, non per il cronometro.</p>
                    <p class="counter"><strong>{{ $totalIscritti }}</strong> iscritti finora</p>
                </div>
            </div>
        </section>

        <section id="percorsi" class="routes">
            <div class="inner">
                <div class="routes__head">
                    <div>
                        <span class="kicker">Percorsi</span>
                        <h2>Quattro distanze, una valle.</h2>
                    </div>
                    <p>Dalla mattinata tranquilla all'ultra dall'alba al tramonto. Tutti i tracciati partono e arrivano a {{ config('event.location') }}.</p>
                </div>

                <div class="route-list">
                    @foreach ($routes as $value => $route)
                        <article class="route">
                            <p class="route__km">{{ $route['km'] }}<small>km</small></p>
                            <div class="route__info">
                                <h3>{{ $route['name'] }} <span class="route__level">{{ $route['level'] }}</span></h3>
                                <p>{{ $route['text'] }}</p>
                                <div class="route__bar" style="--len: {{ round($route['km'] / $maxKm, 3) }}" aria-hidden="true"></div>
                            </div>
                            <a href="/iscrizione?percorso={{ urlencode($value) }}" class="btn btn-ghost">Iscriviti alla {{ $route['km'] }}</a>
                        </article>
                    @endforeach
                </div>

                <p class="routes__note">Tracce GPX, dislivelli e punti ristoro verranno pubblicati prima dell'evento.</p>
            </div>
        </section>

        <section id="territorio" class="land">
            <div class="inner">
                <span class="kicker">Il territorio</span>
                <h2>Cosa trovi lungo la strada.</h2>

                <div class="land__grid">
                    <div class="land__item">
                        <span class="num">01</span>
                        <h3>Strade bianche</h3>
                        <p>Sterrati compatti tra campi di grano e poderi, alternati a tratti d'asfalto senza traffico. Fondo scorrevole con l'asciutto, impegnativo dopo la pioggia.</p>
                    </div>
                    <div class="land__item">
                        <span class="num">02</span>
                        <h3>Borghi</h3>
                        <p>Pietra, rocche e pievi romaniche: la valle è punteggiata di paesi arroccati che si vedono da chilometri e si guadagnano in salita.</p>
                    </div>
                    <div class="land__item">
                        <span class="num">03</span>
                        <h3>Sapori</h3>
                        <p>Pecorino, olio, salumi e vino del territorio. I ristori sono parte del viaggio quanto le salite.</p>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="inner">
                <span class="kicker">Come funziona</span>
                <h2>Dall'iscrizione alla partenza.</h2>

                <ol class="steps">
                    <li>
                        <h3>Iscriviti online</h3>
                        <p>Scegli il percorso e paga con carta o PayPal. Puoi iscrivere più persone nello stesso ordine.</p>
                    </li>
                    <li>
                        <h3>Ricevi il biglietto</h3>
                        <p>Ogni partecipante riceve via email il biglietto nominativo in PDF, con il proprio codice.</p>
                    </li>
                    <li>
                        <h3>Presentati alla partenza</h3>
                        <p>Mostra il biglietto, digitale o stampato, al ritiro del pacco gara a {{ config('event.location') }}.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section class="faq">
            <div class="inner faq__wrap">
                <div>
                    <span class="kicker">Domande frequenti</span>
                    <h2>Prima di partire.</h2>
                </div>
                <div class="faq-accordion">
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Quando e dove si svolge?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>A {{ config('event.location') }} ({{ config('event.region') }}), {{ config('event.date_label') }}. La data esatta e il programma della giornata verranno pubblicati qui e comunicati a tutti gli iscritti.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Che bici serve?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>Una gravel è la scelta ideale. Sui percorsi più corti va bene anche una mountain bike o una bici da ciclocross. Consigliamo coperture di almeno 35 mm.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Quale percorso scelgo?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>Se è la tua prima gravel, parti dalla {{ array_values($routes)[0]['km'] }} km. Le distanze lunghe richiedono allenamento e autonomia: sulla {{ $maxKm }} km si pedala per buona parte della giornata.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Posso iscrivere un gruppo o un'associazione?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>Sì. Nel modulo di iscrizione puoi aggiungere più partecipanti nello stesso ordine e indicare il nome dell'associazione sportiva. Ognuno riceverà il proprio biglietto nominativo.</p></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="final-cta">
            <span class="kicker">{{ config('event.location') }} · {{ config('event.date_label') }}</span>
            <h2>Ci vediamo<br>sullo sterrato.</h2>
            <a href="/iscrizione" class="btn btn-light">Iscriviti ora</a>

            <svg viewBox="0 0 1440 140" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 70 C200 30 380 40 560 72 S900 110 1080 70 S1330 40 1440 56 V140 H0Z" fill="#d6a95c" opacity=".55"/>
                <path d="M0 104 C220 80 420 84 620 102 S1000 124 1200 102 S1380 90 1440 96 V140 H0Z" fill="#2a3122"/>
            </svg>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="/accordion.js"></script>
@endpush
