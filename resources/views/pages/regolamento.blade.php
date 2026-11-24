@extends('layouts.app')

{{-- Regolamento 2027 in via di definizione (richiesta di Federico, 3/11/2026). Il regolamento 2026 completo è nel commit 55ee0bc. --}}

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Regolamento {{ config('event.edition_year') }}</span>
                <h1>Le regole della strada.</h1>
                <p class="lead">Il regolamento dell'edizione {{ config('event.edition_year') }} è in via di definizione e sarà pubblicato qui.</p>
            </div>
        </section>

        @include('partials.page-photo', ['photo' => 'sosta', 'alt' => 'Una partecipante in sosta accanto alla sua gravel, appoggiata a un muro in pietra, edizione 2026'])

        <section>
            <div class="inner grid-2">
                <div>
                    <span class="kicker">Informazioni</span>
                    <h2>Hai una domanda?</h2>
                </div>
                <div>
                    <p>{{ config('event.name') }} è una manifestazione cicloturistica internazionale, non competitiva, in Val d'Orcia. L'edizione {{ config('event.edition_year') }} si svolge a {{ config('event.location') }} il {{ config('event.date_label') }}.</p>
                    <p>Per informazioni scrivi a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a> o chiama {{ config('event.phone_contact') }} al <a class="hyperlink" href="tel:{{ str_replace(' ', '', config('event.phone')) }}">{{ config('event.phone') }}</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
