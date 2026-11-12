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
                <h2>Dati raccolti</h2>
                <p class="top-margin-large">Raccogliamo dati anagrafici, contatti, informazioni di iscrizione, preferenze operative e dati necessari alla gestione del pagamento e del biglietto.</p>
            </div>
            <div class="card">
                <h2>Finalità</h2>
                <p class="top-margin-large">I dati sono usati per gestire iscrizioni, inviare conferme, produrre biglietti, fornire assistenza e adempiere a obblighi amministrativi.</p>
            </div>
            <div class="card">
                <h2>Conservazione</h2>
                <p class="top-margin-large">I dati sono conservati per il tempo necessario alla gestione dell’evento e agli obblighi fiscali o legali applicabili.</p>
            </div>
            <div class="card">
                <h2>Diritti</h2>
                <p class="top-margin-large">Per richieste di accesso, rettifica o cancellazione puoi scrivere a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>.</p>
            </div>
            </div>
        </section>
    </main>
@endsection
