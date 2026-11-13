@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Note legali</span>
                <h1>Privacy Policy</h1>
            <p class="lead">Questa informativa descrive in modo sintetico il trattamento dei dati raccolti tramite iscrizioni, pagamenti, comunicazioni e moduli del sito {{ config('event.name') }}.</p>
            </div>
        </section>

        <section>
            <div class="inner grid-2">
            <div class="card">
                <h2>Titolare</h2>
                <p class="top-margin-large">{{ config('event.organizer') }}, {{ config('event.organizer_address') }}, P.IVA e C.F. {{ config('event.organizer_vat') }}. Email <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>.</p>
            </div>
            <div class="card">
                <h2>Dati raccolti</h2>
                <p class="top-margin-large">Dati anagrafici e di contatto, tessera sportiva, certificato medico, eventuali esigenze alimentari e dati necessari alla gestione del pagamento e del biglietto. Certificato ed esigenze alimentari sono dati relativi alla salute (art. 9 GDPR), trattati solo con il tuo consenso e solo per gestire la partecipazione.</p>
            </div>
            <div class="card">
                <h2>Finalità</h2>
                <p class="top-margin-large">I dati sono usati per gestire iscrizioni, inviare conferme, produrre biglietti, fornire assistenza e adempiere a obblighi amministrativi.</p>
            </div>
            <div class="card">
                <h2>Conservazione</h2>
                <p class="top-margin-large">I dati sono conservati per il tempo necessario alla gestione dell’evento e agli obblighi fiscali o legali applicabili. I certificati medici sono accessibili solo all'organizzazione. Il tracciamento durante l'evento è gestito da ENDU tramite l'app Whip Live.</p>
            </div>
            <div class="card">
                <h2>Diritti</h2>
                <p class="top-margin-large">Per richieste di accesso, rettifica o cancellazione puoi scrivere a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>.</p>
            </div>
            </div>
        </section>
    </main>
@endsection
