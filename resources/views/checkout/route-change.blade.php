@extends('layouts.app')

@section('title', 'Cambia percorso | ' . config('event.name'))

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@php
    $routes = config('event.routes');
    $current = $routes[$ticket->route_choice] ?? null;
    $reasons = [
        'price' => 'Quota diversa: per passare a questo percorso scrivici',
        'contact' => 'Per questo cambio scrivici',
        'full' => 'Al completo',
    ];
    $anyAvailable = collect($options)->contains('available', true);
@endphp

@section('content')
    <main class="confirm">
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Cambia percorso</span>
                <h1>{{ $ticket->first_name }} {{ $ticket->last_name }}</h1>
                <p class="lead">Ora sei iscritto al <strong>{{ $current['name'] ?? $ticket->route_choice }}</strong>{{ $current ? ' (' . $current['km'] . ' km)' : '' }}. Puoi passare a un altro percorso con la stessa quota fino al {{ config('event.online_closes_label') }}: il codice del biglietto resta lo stesso e ti mandiamo via email il PDF aggiornato.</p>
            </div>
        </section>

        <section class="confirm__body">
            <div class="inner">
                @if (! $open)
                    <p class="confirm__alert">Il cambio di percorso online è chiuso. Per richieste scrivi a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a> indicando il codice biglietto {{ $ticket->unique_ticket_code }}.</p>
                @else
                    <form method="POST" action="{{ route('route-change.update', [$order->group_code, $ticket->unique_ticket_code]) }}" enctype="multipart/form-data" class="route-change">
                        @csrf

                        @if ($errors->any())
                            <div class="confirm__alert" role="alert">
                                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                            </div>
                        @endif

                        <fieldset class="route-options">
                            <legend class="kicker">Nuovo percorso</legend>
                            @foreach ($options as $key => $option)
                                @php $route = $routes[$key]; @endphp
                                <label @class(['route-option', 'is-disabled' => ! $option['available']])>
                                    <input type="radio" name="route_choice" value="{{ $key }}" data-needs-certificate="{{ $option['needs_certificate'] ? 1 : 0 }}" @disabled(! $option['available']) @checked(old('route_choice') === $key) required>
                                    <span class="route-option__km">{{ $route['km'] }}<small>km</small></span>
                                    <span class="route-option__info">
                                        <strong>{{ $route['name'] }}</strong>
                                        <small>{{ $route['level'] }} · certificato {{ $route['certificate'] }}</small>
                                        @if (! $option['available'])
                                            <em>{{ $reasons[$option['reason']] }}</em>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </fieldset>

                        <div class="certificate-box" id="certificate-box" @if (! collect($options)->contains('needs_certificate', true)) hidden @endif>
                            <label for="medical_certificate" class="kicker">Certificato medico agonistico</label>
                            <p>Il percorso scelto richiede il certificato di idoneità agonistica per il ciclismo. PDF o foto, massimo 5 MB.</p>
                            <input type="file" id="medical_certificate" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
                        </div>

                        <div class="confirm__actions">
                            @if ($anyAvailable)
                                <button type="submit" class="btn">Conferma il cambio</button>
                            @endif
                            <a href="{{ route('checkout.success', ['order_code' => $order->group_code]) }}" class="hyperlink">Torna ai biglietti</a>
                        </div>

                        <p class="confirm__note">La quota non cambia e non è rimborsabile. Per passare a un percorso con una quota diversa, come il Luculliano, scrivi a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a> indicando il codice biglietto {{ $ticket->unique_ticket_code }}.</p>
                    </form>
                @endif
            </div>
        </section>
    </main>

    <script>
        // Il campo del certificato compare solo se il percorso scelto lo richiede (il server lo controlla comunque)
        (function () {
            const box = document.getElementById('certificate-box');
            const input = document.getElementById('medical_certificate');
            if (!box) return;
            function sync() {
                const chosen = document.querySelector('input[name=route_choice]:checked');
                const needed = chosen && chosen.dataset.needsCertificate === '1';
                box.hidden = !needed;
                input.required = needed;
            }
            document.querySelectorAll('input[name=route_choice]').forEach(r => r.addEventListener('change', sync));
            sync();
        })();
    </script>
@endsection
