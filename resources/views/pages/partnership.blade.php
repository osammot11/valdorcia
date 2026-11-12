@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Partner</span>
                <h1>Chi pedala con noi.</h1>
                <p class="lead">Le aziende, le istituzioni e le associazioni del territorio che rendono possibile {{ config('event.name') }}.</p>
            </div>
        </section>

        <section>
            <div class="inner grid-3">
                <div class="card">
                    <h2>Territorio</h2>
                    <p class="top-margin-large">Comuni, Pro Loco e realtà della Val d'Orcia che aprono le loro strade e i loro borghi.</p>
                </div>
                <div class="card">
                    <h2>Sponsor</h2>
                    <p class="top-margin-large">Aziende del mondo bici e produttori locali che sostengono l'evento e i ristori.</p>
                </div>
                <div class="card">
                    <h2>Diventa partner</h2>
                    <p class="top-margin-large">Vuoi legare il tuo marchio all'evento? Scrivi a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
