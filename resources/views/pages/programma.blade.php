@extends('layouts.app')

{{-- Programma 2027 in via di definizione (richiesta di Federico, 3/11/2026). Il programma 2026 è nel commit 55ee0bc. --}}

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Programma {{ config('event.edition_year') }}</span>
                <h1>Due giorni in Val d'Orcia.</h1>
                <p class="lead">{{ config('event.location') }} e Radicofani, {{ config('event.date_label') }}.</p>
            </div>
        </section>

        <section>
            <div class="inner grid-2">
                <div>
                    <span class="kicker">In via di definizione</span>
                    <h2>Orari e appuntamenti in arrivo.</h2>
                </div>
                <div>
                    <p>Il programma dell'edizione {{ config('event.edition_year') }} è in via di definizione: ritiro dei pacchi gara, partenze ed eventi collaterali saranno pubblicati qui appena confermati dall'organizzazione.</p>
                    <p>Intanto puoi segnare le date: {{ \Illuminate\Support\Str::lower(config('event.saturday_label')) }} e {{ \Illuminate\Support\Str::lower(config('event.sunday_label')) }} 2027, con partenza da {{ config('event.location') }}. Le iscrizioni online aprono il 10 dicembre 2026: quote e scadenze sono nella pagina <a class="hyperlink" href="/iscrizione">iscrizioni</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
