@php
    use Illuminate\Support\Str;

    $route = config('event.routes')[$ticket->route_choice] ?? null;
    $isLuculliano = $ticket->route_choice === 'luculliano';
    $routeName = $route['name'] ?? $ticket->route_choice;
    $startDay = $isLuculliano ? config('event.saturday_label') : config('event.sunday_label');
    $startTime = $route ? ($isLuculliano ? '12:00' : $route['start']) : null;
    $membershipLabel = $ticket->day_card ? 'Giornaliera, inclusa' : trim(($ticket->membership ?? '—') . ' ' . ($ticket->membership_number ?? ''));
    $logo = public_path('images/valdorcia-gravel/logo-valdorcia-gravel.png');
    $euro = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €';
    // Colline della Val d'Orcia con cipressi, nei colori del sito
    $hills = '<svg xmlns="http://www.w3.org/2000/svg" width="595" height="210" viewBox="0 0 595 210">'
        . '<path d="M0 120 C 90 70, 170 80, 250 105 S 420 60, 595 95 L595 210 L0 210 Z" fill="#e8dcc6"/>'
        . '<path d="M0 150 C 110 110, 210 135, 300 140 S 470 100, 595 130 L595 210 L0 210 Z" fill="#d6a95c" fill-opacity="0.55"/>'
        . '<path d="M0 178 C 120 150, 260 170, 360 168 S 520 150, 595 162 L595 210 L0 210 Z" fill="#a9501f" fill-opacity="0.85"/>'
        . collect([[392, 104], [404, 100], [416, 98], [428, 97]])->map(fn ($c) => '<ellipse cx="' . $c[0] . '" cy="' . ($c[1] + 14) . '" rx="4" ry="16" fill="#3d4630"/>')->implode('')
        . '</svg>';
    $hillsSrc = 'data:image/svg+xml;base64,' . base64_encode($hills);
@endphp
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Biglietto {{ $ticket->unique_ticket_code }}</title>
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #221e18; font-size: 11px; line-height: 1.45; background: #f3ecdf; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .page { padding: 40px 44px 0; }

        /* Testata */
        .top td { vertical-align: middle; }
        .logo { width: 74px; }
        .event-name { font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; }
        .event-edition { margin-top: 3px; font-size: 9px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #a9501f; }
        .event-when { text-align: right; font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: #5e564a; }
        .event-when strong { display: block; font-size: 13px; letter-spacing: .5px; color: #221e18; }

        /* Corpo del biglietto */
        .ticket { margin-top: 26px; background: #fbf8f2; border: 1.5px solid #221e18; }
        .hero { padding: 26px 28px 22px; border-bottom: 1.5px solid #221e18; }
        .kicker { font-size: 9px; font-weight: bold; letter-spacing: 2.5px; text-transform: uppercase; color: #a9501f; }
        .participant { margin-top: 6px; font-size: 30px; font-weight: bold; line-height: 1.05; text-transform: uppercase; }
        .route-row { margin-top: 22px; }
        .km { width: 170px; font-size: 76px; font-weight: bold; line-height: .85; letter-spacing: -2px; }
        .km small { font-size: 18px; letter-spacing: 0; }
        .route-name { font-size: 22px; font-weight: bold; text-transform: uppercase; }
        .route-meta { margin-top: 5px; font-size: 10px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; color: #5e564a; }
        .bar { margin-top: 12px; height: 5px; background: #e8dcc6; }
        .bar div { height: 5px; background: #a9501f; }

        .facts td { width: 33.33%; padding: 14px 18px 14px 28px; border-right: 1px solid #d5c8b0; border-bottom: 1px solid #d5c8b0; }
        .facts td.last { border-right: 0; }
        .facts tr.end td { border-bottom: 0; }
        .label { font-size: 8.5px; font-weight: bold; letter-spacing: 1.8px; text-transform: uppercase; color: #5e564a; }
        .value { margin-top: 4px; font-size: 12.5px; font-weight: bold; }

        /* Tagliando */
        .cut { margin: 26px 0 8px; border-top: 1.5px dashed #221e18; }
        .cut-label { font-size: 8.5px; letter-spacing: 1.8px; text-transform: uppercase; color: #5e564a; }
        .stub { margin-top: 10px; background: #221e18; color: #f3ecdf; }
        .stub td { padding: 18px 24px; vertical-align: middle; }
        .stub .label { color: #d5c8b0; }
        .stub .who { margin-top: 4px; font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .stub .code { text-align: right; }
        .stub .code .value { font-family: 'Courier', monospace; font-size: 22px; letter-spacing: 1.5px; color: #ffffff; }

        /* Note */
        .notes { margin-top: 24px; }
        .notes td { width: 50%; padding-right: 22px; }
        .notes h3 { font-size: 9px; letter-spacing: 1.8px; text-transform: uppercase; margin-bottom: 6px; }
        .notes p, .notes li { color: #5e564a; font-size: 9.5px; }
        .notes ul { margin-left: 12px; }

        .hills { position: absolute; left: 0; bottom: 44px; width: 595px; height: 210px; }
        .footer { position: absolute; left: 0; bottom: 18px; width: 595px; text-align: center; font-size: 8.5px; letter-spacing: .5px; color: #f3ecdf; }
        .stripe { position: absolute; left: 0; bottom: 0; width: 595px; height: 46px; background: #a9501f; }
    </style>
</head>
<body>
    <div class="page">
        <table class="top">
            <tr>
                <td style="width: 86px;">@if (is_file($logo))<img class="logo" src="{{ $logo }}" alt="">@endif</td>
                <td>
                    <div class="event-name">{{ config('event.name') }}</div>
                    <div class="event-edition">{{ config('event.edition_number') }} edizione · {{ config('event.edition_year') }}</div>
                </td>
                <td class="event-when">
                    <strong>{{ config('event.date_label') }}</strong>
                    {{ config('event.location') }} · Val d'Orcia
                </td>
            </tr>
        </table>

        <div class="ticket">
            <div class="hero">
                <div class="kicker">Biglietto nominativo</div>
                <div class="participant">{{ $ticket->first_name }} {{ $ticket->last_name }}</div>

                <table class="route-row">
                    <tr>
                        <td class="km">{{ $route['km'] ?? '—' }}<small> km</small></td>
                        <td style="vertical-align: bottom;">
                            <div class="route-name">{{ $routeName }}</div>
                            @if ($route)
                                <div class="route-meta">{{ number_format($route['elevation'], 0, ',', '.') }} m di dislivello · {{ $route['level'] }}{{ $isLuculliano ? ' · non segnalato' : '' }}</div>
                                <div class="bar"><div style="width: {{ round($route['km'] / 220 * 100) }}%;"></div></div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <table class="facts">
                <tr>
                    <td>
                        <div class="label">Partenza</div>
                        <div class="value">{{ $startDay }}@if ($startTime), ore {{ $startTime }}@endif</div>
                    </td>
                    <td>
                        <div class="label">Luogo di partenza</div>
                        <div class="value">{{ config('event.start_place') }}</div>
                    </td>
                    <td class="last">
                        <div class="label">Ritiro pacco gara</div>
                        <div class="value">Pro Loco, {{ config('event.location') }}</div>
                    </td>
                </tr>
                <tr class="end">
                    <td>
                        <div class="label">Tessera</div>
                        <div class="value">{{ $membershipLabel }}</div>
                    </td>
                    <td>
                        <div class="label">Senza glutine</div>
                        <div class="value">{{ $ticket->celiac ? 'Sì' : 'No' }}</div>
                    </td>
                    <td class="last">
                        <div class="label">Quota pagata</div>
                        <div class="value">{{ $euro($ticket->price_paid) }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="cut"></div>
        <div class="cut-label">Tagliando per il ritiro del pacco gara · mostralo sul telefono o stampato</div>

        <table class="stub">
            <tr>
                <td>
                    <div class="label">Partecipante</div>
                    <div class="who">{{ $ticket->last_name }} {{ $ticket->first_name }}</div>
                    <div class="label" style="margin-top: 6px;">{{ $route['short_name'] ?? $routeName }}{{ $route ? ' · ' . $route['km'] . ' km' : '' }}</div>
                </td>
                <td class="code">
                    <div class="label">Codice biglietto</div>
                    <div class="value">{{ $ticket->unique_ticket_code }}</div>
                </td>
            </tr>
        </table>

        <table class="notes">
            <tr>
                <td>
                    <h3>Da portare in griglia</h3>
                    <ul>
                        <li>casco rigido omologato, sempre allacciato</li>
                        <li>camera d'aria o tubolare di scorta e attrezzi</li>
                        <li>pompa o bomboletta, borraccia</li>
                        @if ($isLuculliano)<li>luci anteriori e posteriori</li>@endif
                        <li>numero da bici ben visibile</li>
                    </ul>
                </td>
                <td>
                    <h3>Buono a sapersi</h3>
                    <p>Il biglietto è personale e non cedibile. Vale come ricevuta del pagamento elettronico della quota indicata; per una fattura scrivi a {{ config('event.email') }} con il codice biglietto nell'oggetto. Orari e programma completo su {{ Str::after(url('/programma'), '://') }}.</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="stripe"></div>
    <img class="hills" src="{{ $hillsSrc }}" alt="">
    <div class="footer">{{ config('event.organizer') }} · {{ config('event.organizer_address') }} · {{ config('event.email') }} · {{ config('event.phone') }}</div>
</body>
</html>
