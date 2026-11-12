@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Contatti</span>
                <h1>Scrivici.</h1>
                <p class="lead">Per iscrizioni, percorsi, logistica o richieste di fattura rispondiamo via email.</p>
                <a href="mailto:{{ config('event.email') }}" class="btn top-margin-xl mobile-fullwidth">{{ config('event.email') }}</a>
            </div>
        </section>

        <section>
            <div class="inner grid-2">
                <div class="card">
                    <h2>Email</h2>
                    <p class="top-margin-large"><a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>@if(config('event.phone'))<br>{{ config('event.phone') }}@endif</p>
                    <p class="top-margin-mid">Per le richieste sui biglietti indica il codice che trovi nel PDF.</p>
                </div>
                <div class="card">
                    <h2>Dove</h2>
                    <p class="top-margin-large">Partenza e arrivo a {{ config('event.location') }} ({{ config('event.region') }}). Il punto esatto del ritrovo verrà comunicato agli iscritti.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
