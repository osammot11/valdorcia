@extends('layouts.app')

@php
    $routes = config('event.routes');
    $euro = fn ($v) => number_format((float) $v, 0, ',', '.') . ' €';
@endphp

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Regolamento {{ config('event.edition_year') }}</span>
                <h1>Le regole della strada.</h1>
                <p class="lead">{{ config('event.name') }} è una manifestazione cicloturistica internazionale, non competitiva, in Val d'Orcia (provincia di Siena). Si svolge a {{ config('event.location') }} il {{ config('event.date_label') }}. Iscrivendoti accetti tutti i punti di questo regolamento.</p>
            </div>
        </section>

        <section>
            <div class="inner rules">
                <article class="rule">
                    <span class="rule__num">00</span>
                    <div>
                        <h2>Percorsi</h2>
                        <ul>
                            @foreach ($routes as $route)
                                <li><strong>{{ $route['name'] }}</strong>: circa {{ $route['km'] }} km e {{ number_format($route['elevation'], 0, ',', '.') }} m di dislivello{{ isset($route['stages']) ? '. ' . $route['stages'] : '.' }}</li>
                            @endforeach
                        </ul>
                        <p>I percorsi sono aperti a qualsiasi bicicletta: strada, gravel, mtb ed e-bike.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">01</span>
                    <div>
                        <h2>Requisiti per partecipare</h2>
                        <h3>Tessera sportiva</h3>
                        <p>Possono partecipare i tesserati FCI e gli enti convenzionati ({{ implode(', ', array_slice(config('event.memberships'), 1, 8)) }}); i tesserati OPES e ASI devono attivare la Bike Card. Chi non ha una tessera per il ciclismo, italiano o straniero, si iscrive pagando {{ $euro(config('event.day_card_price')) }} per la tessera giornaliera, che include la copertura assicurativa per il giorno dell'evento.</p>
                        <h3>Certificato medico</h3>
                        <p>Il certificato medico in corso di validità è obbligatorio per tutti, anche per chi ha la tessera, e si carica in fase di iscrizione.</p>
                        <ul>
                            <li><strong>Saporito, Goloso e Luculliano</strong>: certificato di idoneità alla pratica agonistica del ciclismo (non valgono quelli per altri sport come triathlon o atletica). Per i residenti in Italia non tesserati è richiesto il certificato per attività di particolare ed elevato impegno cardiovascolare; per i residenti all'estero un certificato per gare ciclistiche agonistiche rilasciato anche nel proprio paese.</li>
                            <li><strong>Frugale</strong>: certificato di idoneità alla pratica sportiva non agonistica, anche rilasciato all'estero per chi non risiede in Italia.</li>
                        </ul>
                        <p>Ogni partecipante dichiara, ai sensi del D.P.R. 445/2000, che i documenti caricati sono veritieri e corrispondono agli originali.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">02</span>
                    <div>
                        <h2>Iscrizioni e quote</h2>
                        <p>Ci si iscrive online da questo sito. I posti sono {{ config('event.capacity') }} in totale, di cui al massimo {{ $routes['goloso']['limit'] }} sul Goloso e {{ $routes['luculliano']['limit'] }} sul Luculliano. Le iscrizioni online chiudono il {{ config('event.online_closes_label') }}.</p>
                        @if ($tiers->isNotEmpty())
                            <div class="table-wrap">
                                <table class="fee-table">
                                    <thead>
                                        <tr><th>Periodo</th><th>Frugale, Saporito, Goloso</th><th>Luculliano</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tiers as $tier)
                                            <tr>
                                                <td>{{ $tier->name }}<small>{{ $tier->starts_on->translatedFormat('j M Y') }} – {{ $tier->ends_on->translatedFormat('j M Y') }}</small></td>
                                                <td>{{ $tier->priceFor('goloso') !== null ? $euro($tier->priceFor('goloso')) : '—' }}</td>
                                                <td>{{ $tier->priceFor('luculliano') !== null ? $euro($tier->priceFor('luculliano')) : '—' }}</td>
                                            </tr>
                                        @endforeach
                                        <tr><td>In loco<small>{{ config('event.saturday_label') }}</small></td><td>{{ $euro(config('event.onsite_price')) }}</td><td>non disponibile</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="small-print">La quota promo è riservata a chi ha partecipato alle edizioni precedenti.</p>
                        @endif
                        <p>La quota comprende: pacco gara con omaggi e ricordi della manifestazione, numero da bici, road map con percorsi e ristori, assistenza meccanica e sanitaria (con ambulanze) lungo il percorso, ristori in numero proporzionato alla distanza, rinfresco e ricordo all'arrivo, docce e lavaggio bici. Per il Luculliano anche {{ $routes['luculliano']['includes'] }}.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">03</span>
                    <div>
                        <h2>Segreteria e partenze</h2>
                        <p>La segreteria organizzativa è a {{ config('event.location') }}. Le partenze di Frugale, Saporito e Goloso sono da {{ config('event.start_place') }} la domenica; il Luculliano parte il sabato da {{ config('event.start_place') }} e la domenica da Radicofani. Orari e luoghi precisi sono nel <a class="hyperlink" href="/programma">programma</a> e possono subire variazioni.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">04</span>
                    <div>
                        <h2>Tracciamento live</h2>
                        <p>Per la sicurezza tutti i partecipanti devono usare per l'intera manifestazione l'app Whip Live di ENDU, che ne segue la posizione in tempo reale. Chi la disattiva volontariamente può essere escluso.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">05</span>
                    <div>
                        <h2>Disdetta</h2>
                        <p>La quota non è rimborsabile né cedibile ad altri. Solo in caso di infortunio, documentato da certificato medico, l'iscrizione può essere spostata all'edizione successiva: la richiesta va fatta entro il 1° ottobre. Altri motivi (impegni, altre gare, influenza) non sono accettati.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">06</span>
                    <div>
                        <h2>Equipaggiamento obbligatorio</h2>
                        <p>All'ingresso in griglia ogni partecipante deve avere con sé:</p>
                        <ul>
                            <li>attrezzi per smontare copertone o tubolare;</li>
                            <li>una camera d'aria (o un tubolare) di scorta;</li>
                            <li>una pompa o una bomboletta;</li>
                            <li>una borraccia;</li>
                            <li>solo per il Luculliano, luci anteriori e posteriori.</li>
                        </ul>
                        <p>Per tutta la manifestazione sono obbligatori il casco rigido omologato, sempre allacciato, e il numero ben visibile.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">07</span>
                    <div>
                        <h2>In strada</h2>
                        <p>Sul percorso si alternano strade bianche e asfalto a basso traffico, che restano aperte alla circolazione: si rispetta il codice della strada e si procede a velocità controllata. L'assistenza con mezzi privati al seguito è vietata.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">08</span>
                    <div>
                        <h2>Reclami e variazioni</h2>
                        <p>Eventuali reclami vanno inviati, individualmente, a <a class="hyperlink" href="mailto:{{ config('event.claims_email') }}">{{ config('event.claims_email') }}</a> entro 10 giorni dall'evento. L'organizzazione può modificare il regolamento dandone notizia su questo sito, che è il canale di comunicazione ufficiale, e declina ogni responsabilità per incidenti o danni prima, durante e dopo la manifestazione.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">09</span>
                    <div>
                        <h2>Annullamento o spostamento</h2>
                        <p>Se la manifestazione non si svolge per cause di forza maggiore la quota non viene rimborsata, ma resta valida per l'edizione successiva con un contributo di segreteria di 10 €. Per gravi motivi l'organizzazione può spostare luogo o data entro lo stesso anno: in quel caso l'iscrizione vale per la nuova data, senza costi aggiuntivi.</p>
                    </div>
                </article>

                <article class="rule">
                    <span class="rule__num">10</span>
                    <div>
                        <h2>Norme generali</h2>
                        <p>Per tutto ciò che non è previsto qui valgono le norme attuative della Federazione Ciclistica Italiana in vigore. Per informazioni: {{ config('event.phone_contact') }}, <a class="hyperlink" href="tel:{{ str_replace(' ', '', config('event.phone')) }}">{{ config('event.phone') }}</a>.</p>
                    </div>
                </article>
            </div>
        </section>
    </main>
@endsection
