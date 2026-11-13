@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Chi siamo</span>
                <h1>{{ config('event.organizer') }}.</h1>
                <p class="lead">Dal 1990 a Castelnuovo dell'Abate, nel comune di Montalcino: la prima associazione a credere nel cicloturismo sulle strade bianche della Val d'Orcia.</p>
            </div>
        </section>

        <section>
            <div class="inner intro">
                <div>
                    <span class="kicker">La storia</span>
                    <h2>Dalla mountain bike alle gravel.</h2>
                </div>
                <div class="intro__body">
                    <p>Il Team Orso on Bike nasce nel 1990 dalla passione di Roberto Piccini e Mauro Pieri per il loro territorio e per le mountain bike, allora appena arrivate. Da lì è nata la Granfondo del Brunello, una delle granfondo mtb più partecipate della zona.</p>
                    <p>Nel 2001 la granfondo è entrata in Val d'Orcia ed è diventata la Granfondo del Brunello e della Val d'Orcia. Il team organizza o promuove anche la Coppa Toscana MTB, la Granfondo dei Sapori, il MTB Tour Toscana e il Circuito Umbria Tuscany MTB.</p>
                    <p>Nel 2022 è arrivata {{ config('event.name') }}: centinaia di partecipanti da tutto il mondo già alla prima edizione, su ogni tipo di bici. Nel 2026 l'Amministrazione Provinciale di Siena ha segnalato in modo permanente il tracciato del Luculliano, passando anche da Campiglia d'Orcia: 160 km pedalabili tutto l'anno.</p>
                </div>
            </div>
        </section>

        <section class="page-band">
            <div class="inner grid-2">
                <div>
                    <span class="kicker">Dati dell'associazione</span>
                    <h2>Organizzatore.</h2>
                </div>
                <div>
                    <p><strong>{{ config('event.organizer') }}</strong><br>{{ config('event.organizer_address') }}<br>P.IVA e C.F. {{ config('event.organizer_vat') }}</p>
                    <p><a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a> · <a class="hyperlink" href="tel:{{ str_replace(' ', '', config('event.phone')) }}">{{ config('event.phone') }}</a></p>
                </div>
            </div>
        </section>
    </main>
@endsection
