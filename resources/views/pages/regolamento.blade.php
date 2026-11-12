@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Regolamento</span>
                <h1>Le regole della strada.</h1>
                <p class="lead">Iscrivendoti accetti il regolamento di {{ config('event.name') }}. Qui trovi i punti essenziali; il testo completo verrà pubblicato prima dell'apertura delle iscrizioni.</p>
            </div>
        </section>

        <section>
            <div class="inner grid-2">
                <div class="card">
                    <h2>Partecipazione</h2>
                    <p class="top-margin-large">Ogni partecipante deve avere un biglietto nominativo, digitale o stampato, da mostrare allo staff al ritiro del pacco gara.</p>
                </div>
                <div class="card">
                    <h2>Percorsi</h2>
                    <p class="top-margin-large">Puoi scegliere tra {{ implode(', ', array_map(fn ($r) => $r['km'] . ' km', config('event.routes'))) }}. Partenza e arrivo a {{ config('event.location') }}.</p>
                </div>
                <div class="card">
                    <h2>Sicurezza</h2>
                    <p class="top-margin-large">Il casco è obbligatorio. Le strade restano aperte al traffico: rispetta il Codice della Strada e le indicazioni dello staff lungo il percorso.</p>
                </div>
                <div class="card">
                    <h2>Biglietti</h2>
                    <p class="top-margin-large">Il biglietto è personale. Per cambi di nominativo, rimborsi o fatture scrivi a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
