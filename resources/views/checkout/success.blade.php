@extends('layouts.app')

@section('title', 'Iscrizione confermata | ' . config('event.name'))

@push('head')
    {{-- Contiene nomi e codici dei partecipanti: non va indicizzata --}}
    <meta name="robots" content="noindex, nofollow">
@endpush

@php
    $routes = config('event.routes');
    $count = $order->tickets->count();
@endphp

@section('content')
    <main class="confirm">
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Iscrizione confermata</span>
                <h1>Sei dei nostri.</h1>
                <p class="lead">Ci vediamo a {{ config('event.location') }}, {{ config('event.date_label') }}. {{ $count > 1 ? 'Qui sotto trovi i biglietti di tutti i partecipanti.' : 'Qui sotto trovi il tuo biglietto.' }}</p>
            </div>
        </section>

        <section class="confirm__body">
            <div class="inner">
                @if (session('route_changed'))
                    <p class="confirm__alert confirm__alert--ok" role="status">{{ session('route_changed') }}</p>
                @endif

                <dl class="confirm__order">
                    <div>
                        <dt>Codice ordine</dt>
                        <dd class="mono">{{ $order->group_code }}</dd>
                    </div>
                    <div>
                        <dt>{{ $count > 1 ? 'Partecipanti' : 'Partecipante' }}</dt>
                        <dd>{{ $count }}</dd>
                    </div>
                    <div>
                        <dt>Totale pagato</dt>
                        <dd>{{ number_format((float) $order->total_amount, 2, ',', '.') }} €</dd>
                    </div>
                </dl>

                <h2 class="confirm__title">{{ $count > 1 ? 'Biglietti nominativi' : 'Biglietto nominativo' }}</h2>

                <div class="ticket-list">
                    @foreach ($order->tickets as $ticket)
                        @php $route = $routes[$ticket->route_choice] ?? null; @endphp
                        <article class="ticket">
                            <p class="ticket__km">{{ $route['km'] ?? '—' }}<small>km</small></p>
                            <div class="ticket__info">
                                <h3>{{ $ticket->first_name }} {{ $ticket->last_name }}</h3>
                                <p>{{ $route ? $route['name'] : $ticket->route_choice }} · {{ $ticket->day_card ? 'Tessera giornaliera' : ($ticket->membership ?? '') }}</p>
                                <p class="ticket__code mono">{{ $ticket->unique_ticket_code }}</p>
                            </div>
                            <div class="ticket__actions">
                                <a href="{{ route('ticket.download', $ticket->unique_ticket_code) }}" class="btn">Scarica PDF</a>
                                <a href="{{ route('route-change.show', [$order->group_code, $ticket->unique_ticket_code]) }}" class="hyperlink">Cambia percorso</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <p class="confirm__note">{{ $count > 1 ? "Abbiamo mandato un'email a ogni partecipante" : "Ti abbiamo mandato un'email" }} con il link a questa pagina: puoi tornarci per scaricare {{ $count > 1 ? 'i biglietti' : 'il biglietto' }} quando vuoi. Al ritiro del pacco gara basta mostrarlo sul telefono, non serve stamparlo.</p>

                <div class="confirm__actions">
                    <a href="/percorsi" class="btn btn-ghost">Rivedi i percorsi</a>
                    <a href="/" class="hyperlink">Torna alla home</a>
                </div>
            </div>
        </section>
    </main>
@endsection
