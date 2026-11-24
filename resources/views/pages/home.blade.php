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
    <main>
    <div class="hero">
        <div class="hero__text">
            <p class="hero__meta">
                <span>{{ config('event.location') }}</span>
                <span>{{ config('event.date_label') }}</span>
            </p>
            <h1 class="hero__title">Val d'Orcia <span>Gravel</span></h1>
            <p class="lead hero__copy">Pedala sulle strade bianche più belle del mondo, patrimonio UNESCO. La {{ config('event.edition_number') }} edizione parte da {{ config('event.location') }}, con {{ count($routes) }} percorsi tutti rinnovati.</p>
            <p class="hero__note">Iscrizioni online dal 10 dicembre 2026 a prezzo agevolato: 25 € (70 € per il Luculliano).</p>
            <div class="hero__actions">
                <a href="/iscrizione" class="btn">Iscrizioni e quote</a>
                <a href="#edizione-2026" class="btn btn-ghost">Com'è andata nel 2026</a>
            </div>
        </div>

        <figure class="hero__photo">
            <img src="/images/home/arrivo.jpg" srcset="/images/home/arrivo-800.jpg 800w, /images/home/arrivo.jpg 1534w" sizes="(max-width: 960px) 100vw, 50vw" width="1534" height="1025" alt="Un partecipante festeggia sotto lo striscione d'arrivo della Val d'Orcia Gravel 2026" fetchpriority="high">
        </figure>
    </div>

    <div class="facts" aria-label="L'evento in breve">
        <div><strong>{{ count($routes) }} percorsi</strong><span>{{ implode(' · ', $kmValues) }} km</span></div>
        <div><strong>Ogni bici</strong><span>Gravel, strada, mtb, e-bike</span></div>
        <div><strong>{{ config('event.location') }}</strong><span>Nuova sede</span></div>
        <div><strong>{{ config('event.date_label') }}</strong><span>{{ config('event.edition_number') }} edizione</span></div>
    </div>

        <section id="edizione-2026" class="thanks">
            <div class="inner">
                <span class="kicker">Edizione 2026</span>
                <h2>Grazie a tutti i partecipanti.</h2>
                <p class="lead">In 740 avete pedalato tra le strade bianche più belle del mondo!</p>

                <div class="photo-strip">
                    <img src="/images/home/crete-800.jpg" srcset="/images/home/crete-800.jpg 800w, /images/home/crete.jpg 1600w" sizes="(max-width: 768px) 100vw, 40vw" width="1600" height="1066" alt="Due ciclisti in salita su una strada bianca tra le crete" loading="lazy">
                    <img src="/images/home/notturna.jpg" width="764" height="1024" alt="Ciclisti di notte tra le vie lastricate di un borgo, in bianco e nero" loading="lazy">
                    <img src="/images/home/sosta-800.jpg" srcset="/images/home/sosta-800.jpg 800w, /images/home/sosta.jpg 1600w" sizes="(max-width: 768px) 100vw, 30vw" width="1600" height="1066" alt="Una partecipante in sosta accanto alla sua gravel, appoggiata a un muro in pietra" loading="lazy">
                    <img src="/images/home/timbro-800.jpg" srcset="/images/home/timbro-800.jpg 800w, /images/home/timbro.jpg 1600w" sizes="(max-width: 768px) 100vw, 30vw" width="1600" height="1066" alt="Due bambine timbrano la road map di un ciclista al punto timbro" loading="lazy">
                </div>

                <a class="btn btn-ghost top-gap" href="https://www.youtube.com/watch?v=fvuZWxppnUs" target="_blank" rel="noopener">Guarda gli highlights 2026 su YouTube</a>
            </div>
        </section>

        <section class="news">
            <div class="inner intro">
                <div>
                    <span class="kicker">Edizione {{ config('event.edition_year') }}</span>
                    <h2>Si riparte da {{ config('event.location') }}.</h2>
                </div>
                <div class="intro__body">
                    <p>La nuova sede dell'edizione {{ config('event.edition_year') }} sarà {{ config('event.location') }}, il {{ config('event.date_label') }}. Tutti i percorsi saranno rinnovati e, dopo il grande successo, torna il Luculliano: un viaggio di due giorni in pieno spirito gravel e bikepacking, ampliato a {{ $routes['luculliano']['limit'] }} iscritti.</p>
                    <p>Le iscrizioni online aprono il 10 dicembre 2026 a prezzo agevolato: 25 € per Frugale, Saporito e Goloso, 70 € per il Luculliano. Tutte le quote sono nella pagina <a class="hyperlink" href="/iscrizione">iscrizioni</a>.</p>
                    <p>La {{ config('event.name') }} fa parte del circuito <a class="hyperlink" href="https://gravelroadstuscany.it/" target="_blank" rel="noopener">Gravel Roads Tuscany</a> e del circuito MUT.</p>
                </div>
            </div>
        </section>

        <section>
            <div class="inner intro">
                <div>
                    <span class="kicker">La manifestazione</span>
                    <h2>Cicloturismo, non cronometro.</h2>
                </div>
                <div class="intro__body">
                    <p>{{ config('event.name') }} è una manifestazione cicloturistica internazionale nata nel 2022 e organizzata da {{ config('event.organizer') }}, l'associazione di Castelnuovo dell'Abate che dal 1990 porta le bici fuori strada in Val d'Orcia.</p>
                    <p>Non ci sono tratti cronometrati né chip: si pedala per il paesaggio, i ristori con i prodotti del territorio e la compagnia. Le strade restano aperte al traffico, quindi vale il codice della strada.</p>
                    <p>La {{ config('event.edition_number') }} edizione si svolge il {{ config('event.date_label') }} a {{ config('event.location') }}: il <a class="hyperlink" href="/programma">programma</a> è in via di definizione.</p>
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
                    <p>Tracciati disegnati per le gravel ma percorribili anche con mtb ed e-bike. Per l'edizione {{ config('event.edition_year') }} i percorsi saranno tutti rinnovati: tracce e descrizioni sono in via di definizione.</p>
                </div>

                <div class="route-list">
                    @foreach ($routes as $value => $route)
                        <article class="route">
                            <p class="route__km">{{ $route['km'] }}<small>km</small></p>
                            <div class="route__info">
                                <h3>{{ $route['name'] }} <span class="route__level">{{ $route['level'] }}</span></h3>
                                @if ($route['limit'])
                                    <p class="route__stats">Max {{ $route['limit'] }} partecipanti</p>
                                @endif
                                <div class="route__bar" style="--len: {{ round($route['km'] / $maxKm, 3) }}" aria-hidden="true"></div>
                            </div>
                            <a href="/iscrizione?percorso={{ urlencode($value) }}" class="btn btn-ghost">{{ $registrationOpen ? 'Iscriviti al' : 'Quote del' }} {{ $route['short_name'] ?? $route['name'] }}</a>
                        </article>
                    @endforeach
                </div>

            </div>
        </section>

        <section id="territorio" class="land">
            <div class="inner">
                <span class="kicker">Cosa comprende l'iscrizione</span>
                <h2>Tu pensa a pedalare.</h2>

                <div class="land__grid">
                    <div class="land__item">
                        <span class="num">01</span>
                        <h3>Punti ristoro</h3>
                        <p>Prodotti della tradizione senese e toscana lungo il percorso, più un rinfresco all'arrivo.</p>
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
                        <p>A {{ config('event.location') }}, prima della partenza: luogo e orari saranno nel <a class="hyperlink" href="/programma">programma</a>, in via di definizione.</p>
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
                        <button class="faq-question" aria-expanded="false"><span>Serve il certificato medico?</span><span class="faq-icon" aria-hidden="true">+</span></button>
                        <div class="faq-answer"><p>Sì, per tutti, anche per chi ha la tessera. Per Saporito, Goloso e Luculliano serve il certificato di idoneità agonistica per il ciclismo; per il Frugale basta quello non agonistico. Lo carichi direttamente nel modulo di iscrizione.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Serve una tessera sportiva?</span><span class="faq-icon" aria-hidden="true">+</span></button>
                        <div class="faq-answer"><p>No. Se non sei tesserato FCI o con un ente convenzionato, nel modulo aggiungi la tessera giornaliera da 10 €, che include la copertura assicurativa per la manifestazione.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Ci sono tratti cronometrati o il chip?</span><span class="faq-icon" aria-hidden="true">+</span></button>
                        <div class="faq-answer"><p>No: tutti i percorsi sono cicloturistici, senza cronometraggio. Il tracciamento con l'app Whip Live di ENDU serve solo per la sicurezza ed è obbligatorio.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Con che bici posso partecipare?</span><span class="faq-icon" aria-hidden="true">+</span></button>
                        <div class="faq-answer"><p>Con qualsiasi bici: gravel, strada, mountain bike ed e-bike.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>I percorsi sono segnalati?</span><span class="faq-icon" aria-hidden="true">+</span></button>
                        <div class="faq-answer"><p>Frugale, Saporito e Goloso sì, con cartelli gialli e frecce marroni e personale ai bivi. Il Luculliano non è segnalato: si segue la traccia GPX fornita a tutti gli iscritti.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Le strade sono chiuse al traffico?</span><span class="faq-icon" aria-hidden="true">+</span></button>
                        <div class="faq-answer"><p>No. Essendo una manifestazione cicloturistica le strade restano aperte: si rispetta il codice della strada e si tiene sempre la destra.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Cosa devo portare?</span><span class="faq-icon" aria-hidden="true">+</span></button>
                        <div class="faq-answer"><p>Casco rigido omologato (obbligatorio e sempre allacciato), attrezzi per smontare il copertone, camera d'aria di scorta, pompa o bomboletta e borraccia. Per il Luculliano anche luci anteriori e posteriori.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Posso avere un rimborso?</span><span class="faq-icon" aria-hidden="true">+</span></button>
                        <div class="faq-answer"><p>La quota non è rimborsabile né cedibile. In caso di infortunio, con un certificato medico inviato entro il 1° ottobre, puoi spostare l'iscrizione all'edizione successiva.</p></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false"><span>Posso iscrivere un gruppo o un'associazione?</span><span class="faq-icon" aria-hidden="true">+</span></button>
                        <div class="faq-answer"><p>Sì. Nello stesso ordine puoi aggiungere più partecipanti e indicare il nome dell'associazione. Ognuno riceve il proprio biglietto nominativo.</p></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="final-cta">
            <span class="kicker">{{ config('event.location') }} · {{ config('event.date_label') }}</span>
            <h2>Ci vediamo <br>sullo sterrato.</h2>
            <a href="/iscrizione" class="btn btn-light">{{ $registrationOpen ? 'Iscriviti ora' : 'Quote e apertura iscrizioni' }}</a>

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
