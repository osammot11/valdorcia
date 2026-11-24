<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - {{ config('event.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="apple-touch-icon" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/controls.css?v=3">
    <link rel="stylesheet" href="/css/admin.css?v=10">
</head>

<body>
    <div class="wrap">
        <div class="card page-head">
            <div>
                <h1>Pannello di Controllo</h1>
                <div class="head-links">
                    <a href="{{ route('admin.blog.index') }}" class="btn btn--soft btn--small">Blog e comunicati</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="link-btn link-btn--danger">Esci</button>
                    </form>
                </div>
            </div>
            <div class="revenue">
                <p class="small muted">Incasso Totale</p>
                <strong>€ {{ number_format($totalRevenue, 2, ',', '.') }}</strong>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert--ok">{{ session('success') }}</div>
        @endif

        <div class="grid grid--2" style="gap: 24px;">
            <div class="card">
                <h3>Posti</h3>
                <ul class="seats">
                    @foreach (config('event.routes') as $value => $route)
                        <li>
                            <span>{{ $route['short_name'] ?? $route['name'] }} <small class="muted">{{ $route['km'] }} km</small></span>
                            <strong>{{ $taken[$value] }}@if ($route['limit']) <small class="muted">/ {{ $route['limit'] }}</small>@endif</strong>
                        </li>
                    @endforeach
                    <li class="seats__total"><span>Totale</span><strong>{{ array_sum($taken) }} <small class="muted">/ {{ config('event.capacity') }}</small></strong></li>
                </ul>
                <p class="small muted" style="margin-top: 10px;">Contano pagati, in approvazione e ordini in pagamento da meno di 30 minuti.</p>
            </div>

            <div class="card">
                <h3>Creazione biglietti manuale</h3>
                <a href="{{ route('admin.pending') }}" class="btn btn--soft btn--block">Gestione CSV & In Attesa</a>
                <a href="{{ route('admin.create_ticket_form') }}" class="btn btn--soft-ok btn--block">Crea Biglietto Singolo</a>
            </div>
        </div>

        <div class="card" style="margin-top: 32px;">
            <h2>Scaglioni di prezzo</h2>
            <p class="small muted" style="margin-bottom: 16px;">
                Oggi vale: <strong>{{ $currentTier ? $currentTier->name : 'nessuno, iscrizioni online chiuse' }}</strong>.
                Il prezzo si applica in base alla data dell'iscrizione. Uno scaglione con un codice vale solo per chi inserisce quel codice (es. ex iscritti); se il prezzo di un percorso è vuoto, in quel periodo il percorso non si può prenotare.
                La tessera giornaliera ({{ number_format(config('event.day_card_price'), 2, ',', '.') }} €) si aggiunge in automatico.
            </p>
            <form action="{{ route('admin.update_price') }}" method="POST">
                @csrf
                <div class="table-scroll">
                    <table class="tiers">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Dal</th>
                                <th>Al</th>
                                <th>Codice</th>
                                @foreach (config('event.routes') as $route)
                                    <th class="right">{{ $route['short_name'] ?? $route['name'] }}</th>
                                @endforeach
                                <th class="center">Elimina</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tiers->concat([null]) as $i => $tier)
                                <tr @class(['tiers__new' => ! $tier, 'tiers__current' => $tier && $currentTier && $tier->is($currentTier)])>
                                    <td>
                                        <input type="hidden" name="tiers[{{ $i }}][id]" value="{{ $tier?->id }}">
                                        <input type="text" name="tiers[{{ $i }}][name]" value="{{ $tier?->name }}" placeholder="{{ $tier ? '' : 'Nuovo scaglione' }}" aria-label="Nome scaglione">
                                    </td>
                                    <td><input type="date" name="tiers[{{ $i }}][starts_on]" value="{{ $tier?->starts_on?->toDateString() }}" aria-label="Dal"></td>
                                    <td><input type="date" name="tiers[{{ $i }}][ends_on]" value="{{ $tier?->ends_on?->toDateString() }}" aria-label="Al"></td>
                                    <td><input type="text" name="tiers[{{ $i }}][access_code]" value="{{ $tier?->access_code }}" placeholder="—" aria-label="Codice" class="uppercase"></td>
                                    @foreach (config('event.routes') as $value => $route)
                                        <td><input type="number" step="0.01" min="0" name="tiers[{{ $i }}][prices][{{ $value }}]" value="{{ $tier?->priceFor($value) !== null ? number_format($tier->priceFor($value), 2, '.', '') : '' }}" aria-label="Prezzo {{ $route['name'] }}" class="price-input"></td>
                                    @endforeach
                                    <td class="center">
                                        @if ($tier)
                                            <input type="hidden" name="tiers[{{ $i }}][delete]" value="0">
                                            <input type="checkbox" name="tiers[{{ $i }}][delete]" value="1" aria-label="Elimina {{ $tier->name }}">
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($errors->any())
                    <div class="alert alert--error" style="margin-top: 16px;"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <button type="submit" class="btn" style="margin-top: 16px;">Salva scaglioni</button>
                <span class="small muted" style="margin-left: 12px;">Per aggiungere uno scaglione compila l'ultima riga.</span>
            </form>
        </div>

        <div class="card" style="margin-top: 32px;">
            @php $filtering = filled(request('search')) || filled(request('date_from')) || filled(request('date_to')); @endphp
            <div class="card-head">
                <h2>Iscritti {{ $filtering ? 'trovati' : '' }} <span class="count">{{ $activeTickets->count() }}</span></h2>
                <a href="{{ route('admin.export_csv', request()->query()) }}" class="btn btn--ok btn--small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Esporta CSV{{ $filtering ? ' (filtrati)' : '' }}
                </a>
            </div>

            <form action="{{ route('admin.dashboard') }}" method="GET" class="filters" role="search">
                <div class="filters__search">
                    <label class="label--small" for="search">Cerca</label>
                    <div class="search-input">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke-width="2"></circle><path stroke-linecap="round" stroke-width="2" d="M20 20l-3.5-3.5"></path></svg>
                        <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Nome, email o codice biglietto">
                    </div>
                </div>
                <div>
                    <label class="label--small" for="date_from">Iscritti dal</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div>
                    <label class="label--small" for="date_to">al</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="filters__actions">
                    <button type="submit" class="btn btn--dark btn--small">Filtra</button>
                    @if ($filtering)
                        <a href="{{ route('admin.dashboard') }}" class="link-reset">Azzera</a>
                    @endif
                </div>
            </form>

            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Codice / Ordine</th>
                            <th>Utente</th>
                            <th>Data</th>
                            <th>Codice fiscale</th>
                            <th>Pagamento</th>
                            <th>Percorso / Tessera</th>
                            <th class="right">Importo</th>
                            <th class="center">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeTickets as $ticket)
                            <tr>
                                <td class="mono">
                                    <div class="code">{{ $ticket->unique_ticket_code }}</div>
                                    <div class="tiny muted">{{ $ticket->order->group_code ?? '-' }}</div>
                                </td>

                                <td>
                                    <div><strong>{{ $ticket->first_name }} {{ $ticket->last_name }}</strong></div>
                                    <div class="tiny muted">{{ $ticket->email }}</div>
                                    @if($ticket->order && $ticket->order->is_sports_association)
                                        <div class="tags"><span class="tag tag--assoc">Associazione: {{ $ticket->order->association_name ?? 'Non specificata' }}</span></div>
                                    @endif

                                    @if($ticket->tag_1 || $ticket->tag_2)
                                        <div class="tags">
                                            @if($ticket->tag_1)<span class="tag">{{ $ticket->tag_1 }}</span>@endif
                                            @if($ticket->tag_2)<span class="tag tag--alt">{{ $ticket->tag_2 }}</span>@endif
                                        </div>
                                    @endif
                                </td>

                                <td>{{ $ticket->created_at->translatedFormat('d F Y') }}</td>
                                <td>{{ $ticket->codice_fiscale }}</td>
                                <td>{{ $ticket->payment_tag }}</td>

                                <td>
                                    <div><strong>{{ config("event.routes.{$ticket->route_choice}.name", $ticket->route_choice) }}</strong></div>
                                    <div class="tiny muted">{{ $ticket->day_card ? 'Tessera giornaliera' : trim(($ticket->membership ?? '') . ' ' . ($ticket->membership_number ?? '')) }}</div>
                                    @if ($ticket->medical_certificate_path)
                                        <a class="tiny" href="{{ route('admin.download_certificate', $ticket->id) }}">Certificato</a>
                                    @else
                                        <span class="tag">Certificato mancante</span>
                                    @endif
                                </td>

                                <td class="right money">€ {{ number_format($ticket->price_paid, 2, ',', '.') }}</td>

                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.edit_ticket', $ticket->id) }}" title="Modifica e Tag"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a9501f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.5 15H7a4 4 0 0 0-4 4v2"/><path d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><circle cx="10" cy="7" r="4"/></svg></a>
                                        <a href="{{ route('admin.download_pdf', $ticket->id) }}" title="Scarica PDF"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#221e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
