@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Merchandising</span>
                <h1>La maglia ufficiale.</h1>
                <p class="lead">Completini e accessori di {{ config('event.name') }} sono prodotti dal maglificio Natali, partner tecnico della manifestazione.</p>
            </div>
        </section>

        <section>
            <div class="inner grid-2 merch">
                <figure class="merch__photo">
                    <img src="/images/merch/maglia-natali-2026.jpg" alt="La maglia ufficiale 2026 di {{ config('event.name') }}, prodotta da Natali" width="431" height="698" loading="lazy">
                    <figcaption>La maglia dell'edizione 2026.</figcaption>
                </figure>
                <div>
                    <span class="kicker">Come funziona</span>
                    <h2>Ordini online, ritiri all'evento.</h2>
                    <ol class="steps-list">
                        <li>Scegli maglia e accessori ufficiali sul sito di Natali, a prezzo promozionale fino alla scadenza della promo.</li>
                        <li>Ritiri quello che hai ordinato allo stand Natali durante il weekend della manifestazione ({{ config('event.date_label') }}).</li>
                        <li>Se non puoi venire, scrivici: te lo spediamo a manifestazione conclusa.</li>
                    </ol>
                    <p class="top-gap">Finita la promozione, i completini si possono comprare direttamente allo stand Natali nei due giorni dell'evento.</p>
                    <p class="top-gap">
                        <a class="btn" href="https://www.natalimaglificio.it/categoria-prodotto/val-di-orcia-gravel/" target="_blank" rel="noopener">Vai al negozio Natali</a>
                    </p>
                    <p class="small-print">La scadenza della promozione {{ config('event.edition_year') }} sarà pubblicata qui. Per le spedizioni scrivi a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
