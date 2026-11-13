@php
    $route = config('event.routes')[$ticket->route_choice] ?? null;
    $routeLabel = $route ? $route['name'] . ' — ' . $route['km'] . ' km, ' . $route['elevation'] . ' m D+' : $ticket->route_choice;
    $startLabel = $route ? ($ticket->route_choice === 'luculliano'
        ? config('event.saturday_label') . ' ore 12:00, ' . config('event.start_place')
        : config('event.sunday_label') . ' ore ' . $route['start'] . ', ' . config('event.start_place')) : null;
    $membershipLabel = $ticket->day_card ? 'Tessera giornaliera (inclusa)' : trim(($ticket->membership ?? '—') . ' ' . ($ticket->membership_number ?? ''));
@endphp
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Biglietto {{ $ticket->unique_ticket_code }}</title>
    <style>
        @page { margin: 36px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #221e18; font-size: 14px; }
        .ticket { border: 2px solid #221e18; }
        .head { background: #a9501f; color: #ffffff; padding: 28px 32px; }
        .head .event { font-size: 30px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .head .meta { margin-top: 6px; font-size: 13px; text-transform: uppercase; letter-spacing: 2px; }
        .body { padding: 28px 32px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 9px 0; border-bottom: 1px solid #d5c8b0; vertical-align: top; }
        td.label { width: 38%; color: #5e564a; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; padding-top: 11px; }
        td.value { font-size: 16px; font-weight: bold; }
        .code { margin-top: 28px; padding: 26px 0; text-align: center; background: #f3ecdf; border: 1px dashed #221e18; }
        .code .label { font-size: 11px; letter-spacing: 3px; text-transform: uppercase; color: #5e564a; }
        .code .value { margin-top: 8px; font-size: 28px; font-weight: bold; letter-spacing: 2px; }
        .note { margin-top: 20px; font-size: 11px; line-height: 1.6; color: #5e564a; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="head">
            <div class="event">{{ config('event.name') }} {{ config('event.edition_year') }}</div>
            <div class="meta">{{ config('event.location') }} · {{ config('event.date_label') }}</div>
        </div>

        <div class="body">
            <table>
                <tr><td class="label">Partecipante</td><td class="value">{{ $ticket->first_name }} {{ $ticket->last_name }}</td></tr>
                <tr><td class="label">Percorso</td><td class="value">{{ $routeLabel }}</td></tr>
                @if ($startLabel)<tr><td class="label">Partenza</td><td class="value">{{ $startLabel }}</td></tr>@endif
                <tr><td class="label">Tessera</td><td class="value">{{ $membershipLabel }}</td></tr>
                <tr><td class="label">Senza glutine</td><td class="value">{{ $ticket->celiac ? 'Sì' : 'No' }}</td></tr>
                <tr><td class="label">Importo pagato</td><td class="value">{{ number_format((float) $ticket->price_paid, 2, ',', '.') }} €</td></tr>
            </table>

            <div class="code">
                <div class="label">Codice biglietto</div>
                <div class="value">{{ $ticket->unique_ticket_code }}</div>
            </div>

            <p class="note">Il biglietto è nominativo e non cedibile: presentalo al ritiro del pacco gara, stampato o in PDF sul telefono. Ricorda casco omologato, kit per le forature, pompa e borraccia{{ $ticket->route_choice === 'luculliano' ? ', luci anteriori e posteriori' : '' }}. Vale anche come ricevuta del pagamento elettronico dell'importo indicato. Per una fattura nominativa scrivi a {{ config('event.email') }} indicando nell'oggetto il codice biglietto.</p>
        </div>
    </div>
</body>
</html>
