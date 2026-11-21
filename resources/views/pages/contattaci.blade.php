@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Contatti</span>
                <h1>Scrivici.</h1>
                <p class="lead">Per domande su iscrizioni, percorsi e logistica lo staff ti risponde via email o telefono.</p>
                <a href="mailto:{{ config('event.email') }}" class="btn top-margin-xl mobile-fullwidth">{{ config('event.email') }}</a>
            </div>
        </section>

        <section>
            <div class="inner grid-3">
                <div class="card">
                    <h2>Organizzazione</h2>
                    <p class="top-margin-large"><a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a><br>
                    {{ config('event.phone_contact') }}: <a class="hyperlink" href="tel:{{ str_replace(' ', '', config('event.phone')) }}">{{ config('event.phone') }}</a></p>
                    <p class="top-margin-mid">Per le richieste sui biglietti indica il codice che trovi nel PDF. Reclami: <a class="hyperlink" href="mailto:{{ config('event.claims_email') }}">{{ config('event.claims_email') }}</a>.</p>
                </div>
                <div class="card">
                    <h2>Dormire e mangiare</h2>
                    <p class="top-margin-large">Pro Loco di Castiglione d'Orcia: <a class="hyperlink" href="mailto:prolococastiglionedorcia@gmail.com">prolococastiglionedorcia@gmail.com</a></p>
                    <p class="top-margin-mid">Pro Loco di Radicofani (Luculliano): <a class="hyperlink" href="mailto:prolocoradicofani@gmail.com">prolocoradicofani@gmail.com</a></p>
                </div>
                <div class="card">
                    <h2>Social</h2>
                    <p class="top-margin-large">
                        @foreach (config('event.social') as $name => $url)
                            <a class="hyperlink" href="{{ $url }}" rel="noopener" target="_blank">{{ $name }}</a>@if (! $loop->last)<br>@endif
                        @endforeach
                    </p>
                    <p class="top-margin-mid">{{ config('event.organizer') }}<br>{{ config('event.organizer_address') }}</p>
                </div>
            </div>
        </section>
    </main>
@endsection
