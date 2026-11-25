@extends('layouts.app')

@section('title', 'Certificato medico | ' . config('event.name'))

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@php
    $type = $route['certificate'] ?? 'agonistico';
    $missing = $ticket->missingCertificate();
@endphp

@section('content')
    <main class="confirm">
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Certificato medico · {{ $ticket->unique_ticket_code }}</span>
                <h1>{{ $ticket->first_name }} {{ $ticket->last_name }}</h1>
                <p class="lead">
                    Percorso {{ $route['name'] ?? $ticket->route_choice }}: serve il certificato
                    {{ $type === 'agonistico' ? "di idoneità agonistica per il ciclismo (per chi risiede all'estero va bene quello rilasciato nel proprio paese)" : 'per attività sportiva non agonistica' }}.
                </p>
            </div>
        </section>

        <section class="confirm__body">
            <div class="inner">
                @if (session('certificate_saved'))
                    <p class="confirm__alert confirm__alert--ok" role="status">Certificato caricato, grazie: non riceverai altri promemoria. Se serve puoi sostituirlo da questa pagina.</p>
                @elseif ($missing)
                    <p class="confirm__alert" role="status">Il certificato non è ancora stato caricato. Finché manca ti mandiamo un promemoria via email ogni 10 giorni.</p>
                @else
                    <p class="confirm__alert confirm__alert--ok" role="status">Il certificato è già stato caricato. Puoi sostituirlo con uno nuovo, per esempio se è scaduto.</p>
                @endif

                @if (! $open)
                    <p class="confirm__note">Il caricamento online è chiuso. Per il certificato scrivi a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a> indicando il codice biglietto {{ $ticket->unique_ticket_code }}.</p>
                @else
                    <form method="POST" action="{{ route('certificate.update', [$order->group_code, $ticket->unique_ticket_code]) }}" enctype="multipart/form-data">
                        @csrf

                        @if ($errors->any())
                            <div class="confirm__alert" role="alert">
                                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                            </div>
                        @endif

                        <div class="certificate-box">
                            <label for="medical_certificate" class="kicker">{{ $missing ? 'Carica il certificato' : 'Sostituisci il certificato' }}</label>
                            <p id="certificate-hint">PDF o foto, massimo 5 MB.</p>
                            <input type="file" id="medical_certificate" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required aria-describedby="certificate-hint">
                        </div>

                        <label class="certificate-declaration">
                            <input type="checkbox" name="truthful_declaration" value="1" required>
                            <span>Dichiaro, ai sensi del D.P.R. 445/2000, che il certificato è veritiero e corrisponde all'originale in mio possesso.</span>
                        </label>

                        <div class="confirm__actions">
                            <button type="submit" class="btn">{{ $missing ? 'Carica il certificato' : 'Sostituisci il certificato' }}</button>
                            <a href="{{ route('checkout.success', ['order_code' => $order->group_code]) }}" class="hyperlink">Torna ai biglietti</a>
                        </div>
                    </form>
                @endif
            </div>
        </section>
    </main>
@endsection
