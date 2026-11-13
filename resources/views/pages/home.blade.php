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
        'startDate' => config('event.start_date_iso'),
        'endDate' => config('event.date_iso'),
        'organizer' => ['@type' => 'SportsOrganization', 'name' => config('event.organizer'), 'email' => config('event.email'), 'telephone' => config('event.phone')],
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus' => 'https://schema.org/EventScheduled',
        'url' => url('/'),
        'description' => "Manifestazione cicloturistica internazionale sulle strade bianche della Val d'Orcia: quattro percorsi da " . min($kmValues) . ' a ' . $maxKm . ' km.',
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
            <p class="lead hero__copy">Pedala sulle strade bianche più belle del mondo, patrimonio UNESCO. {{ count($routes) }} percorsi da {{ min($kmValues) }} a {{ $maxKm }} km, per ogni tipo di bici e ogni livello di esperienza.</p>
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
        <div><strong>Ogni bici</strong><span>Gravel, strada, mtb, e-bike</span></div>
        <div><strong>{{ config('event.location') }}</strong><span>Partenze da {{ config('event.start_place') }}</span></div>
        <div><strong>{{ config('event.date_label') }}</strong><span>{{ config('event.edition_number') }} edizione</span></div>
    </div>

    <main>
        <section>
            <div class="inner intro">
                <div>
                    <span class="kicker">La manifestazione</span>
                    <h2>Cicloturismo, non cronometro.</h2>
                </div>
                <div class="intro__body">
                    <p>{{ config('event.name') }} è una manifestazione cicloturistica internazionale nata nel 2022 e organizzata da {{ config('event.organizer') }}, l'associazione di Castelnuovo dell'Abate che dal 1990 porta le bici fuori strada in Val d'Orcia. Nel 2025 i partecipanti sono stati 693.</p>
                    <p>Non ci sono tratti cronometrati né chip: si pedala per il paesaggio, i ristori con i prodotti del territorio e la compagnia. Le strade restano aperte al traffico, quindi vale il codice della strada.</p>
                    <p>La {{ config('event.edition_number') }} edizione si svolge su due giorni a {{ config('event.location') }}: il sabato il ritiro dei pacchi gara e la partenza del Luculliano, la domenica tutti gli altri percorsi.</p>
                    @if ($totalIscritti > 0)
                        <p class="counter"><strong>{{ $totalIscritti }}</strong> iscritti finora</p>
                    @endif
                </div>
            </div>
        </section>

        <section id="percorsi" class="routes">
            <div class="inner">
                <div class="routes__head">
                    <div>
                        <span class="kicker">Percorsi</span>
                        <h2>Quattro percorsi, una valle.</h2>
                    </div>
                    <p>Tracciati disegnati per le gravel ma percorribili anche con mtb ed e-bike. Frugale, Saporito e Goloso partono la domenica da {{ config('event.start_place') }}; il Luculliano è un viaggio di due giorni.</p>
                </div>

                <div class="route-list">
                    @foreach ($routes as $value => $route)
                        <article class="route">
                            <p class="route__km">{{ $route['km'] }}<small>km</small></p>
                            <div class="route__info">
                                <h3>{{ $route['name'] }} <span class="route__level">{{ $route['level'] }}</span></h3>
                                <p class="route__stats">{{ number_format($route['elevation'], 0, ',', '.') }} m di dislivello · partenza {{ $route['start'] }}@if ($route['limit']) · max {{ $route['limit'] }} partecipanti @endif</p>
                                <p>{{ $route['text'] }}</p>
                                <div class="route__bar" style="--len: {{ round($route['km'] / $maxKm, 3) }}" aria-hidden="true"></div>
                            </div>
                            <a href="/iscrizione?percorso={{ urlencode($value) }}" class="btn btn-ghost">Iscriviti al {{ $route['short_name'] ?? $route['name'] }}</a>
                        </article>
                    @endforeach
                </div>

                <p class="routes__note">Frugale, Saporito e Goloso sono segnalati con cartelli gialli e frecce marroni; il Luculliano non è segnalato e si segue la traccia GPX. Le tracce possono cambiare per esigenze organizzative.</p>
            </div>
        </section>

        <section id="territorio" class="land">
            <div class="inner">
                <span class="kicker">Cosa comprende l'iscrizione</span>
                <h2>Pensate tu a pedalare.</h2>

                <div class="land__grid">
                    <div class="land__item">
                        <span class="num">01</span>
                        <h3>Punti ristoro</h3>
                        <p>Prodotti della tradizione senese e toscana lungo il percorso, più un rinfresco all'arrivo. Saporito ha due ristori, Goloso quattro.</p>
                    </div>
                    <div class="land__item">
                        <span class="num">02</span>
                        <h3>Assistenza</h3>
                        <p>Assistenza meccanica lungo il tracciato e assistenza sanitaria con ambulanze. Tutti i partecipanti sono seguiti in tempo reale con l'app Whip Live di ENDU.</p>
                    </div>
                    <div class="land__item">
                        <span class="num">03</span>
                        <h3>Pacco gara</h3>
                        <p>Numero da bici, road map con percorsi e ristori, prodotti del territorio e gadget tecnici, più un ricordo consegnato all'arrivo.</p>
                    </div>
                    <div class="land__item">
                        <span class="num">04</span>
                        <h3>Docce e lavaggio bici</h3>
                        <p>All'arrivo trovi docce e postazioni per lavare la bicicletta prima di ripartire.</p>
                    </div>
                    <div class="land__item">
                        <span class="num">05</span>
                        <h3>Green zone</h3>
                        <p>Zone dedicate ai rifiuti lungo il percorso: l'obiettivo è una manifestazione sempre più a basso impatto sulla valle.</p>
                    </div>
                    <div class="land__item">
                        <span class="num">06</span>
                        <h3>Luculliano</h3>
                        <p>Per chi fa il viaggio di due giorni la quota include anche {{ $routes['luculliano']['includes'] }}.</p>
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
                        <p>Scegli il percorso, indica la tua tessera sportiva (o aggiungi la tessera giornaliera da 10 €) e carica il certificato medico. Paghi con carta o PayPal.</p>
                    </li>
                    <li>
                        <h3>Ricevi il biglietto</h3>
                        <p>Ogni partecipante riceve via email il biglietto nominativo in PDF, con il proprio codice.</p>
                    </li>
                    <li>
                        <h3>Ritira il pacco gara</h3>
                        <p>Alla Pro Loco di {{ config('event.location') }}, il sabato o la domenica mattina prima della partenza: gli orari sono nel <a class="hyperlink" href="/programma">programma</a>.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section class="faq" id="faq">
            <div class="inner faq__wrap">
                <div>
                    <span class="kicker">Domande frequenti</span>
                    <h2>Prima di partire.</h2>
                </div>
                <div class="faq-accordion">
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Serve il certificato medico?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>Sì, per tutti, anche per chi ha la tessera. Per Saporito, Goloso e Luculliano serve il certificato di idoneità agonistica per il ciclismo; per il Frugale basta quello non agonistico. Lo carichi direttamente nel modulo di iscrizione.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Serve una tessera sportiva?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>No. Se non sei tesserato FCI o con un ente convenzionato, nel modulo aggiungi la tessera giornaliera da 10 €, che include la copertura assicurativa per la manifestazione.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Ci sono tratti cronometrati o il chip?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>No: tutti i percorsi sono cicloturistici, senza cronometraggio. Il tracciamento con l'app Whip Live di ENDU serve solo per la sicurezza ed è obbligatorio.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Con che bici posso partecipare?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>Con qualsiasi bici: gravel, strada, mountain bike ed e-bike.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>I percorsi sono segnalati?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>Frugale, Saporito e Goloso sì, con cartelli gialli e frecce marroni e personale ai bivi. Il Luculliano non è segnalato: si segue la traccia GPX fornita a tutti gli iscritti.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Le strade sono chiuse al traffico?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>No. Essendo una manifestazione cicloturistica le strade restano aperte: si rispetta il codice della strada e si tiene sempre la destra.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Cosa devo portare?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>Casco rigido omologato (obbligatorio e sempre allacciato), attrezzi per smontare il copertone, camera d'aria di scorta, pompa o bomboletta e borraccia. Per il Luculliano anche luci anteriori e posteriori.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Posso avere un rimborso?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>La quota non è rimborsabile né cedibile. In caso di infortunio, con un certificato medico inviato entro il 1° ottobre, puoi spostare l'iscrizione all'edizione successiva.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Posso iscrivere un gruppo o un'associazione?</span><span class="faq-icon">+</span></button>
                        <div class="faq-answer"><p>Sì. Nello stesso ordine puoi aggiungere più partecipanti e indicare il nome dell'associazione. Ognuno riceve il proprio biglietto nominativo.</p></div>
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
