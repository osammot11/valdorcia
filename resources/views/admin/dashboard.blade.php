<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - {{ config('event.name') }}</title>
    <link rel="stylesheet" href="/admin/admin.css?v=1">
</head>

<body>
    <div class="wrap">
        <div class="card page-head">
            <div>
                <h1>Pannello di Controllo</h1>
                <form action="{{ route('logout') }}" method="POST" style="margin-top: 6px;">
                    @csrf
                    <button type="submit" class="link-btn link-btn--danger">Esci (Logout)</button>
                </form>
            </div>
            <div class="revenue">
                <p class="small muted">Incasso Totale</p>
                <strong>€ {{ number_format($totalRevenue, 2, ',', '.') }}</strong>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert--ok">{{ session('success') }}</div>
        @endif

        <div class="grid grid--3" style="gap: 24px;">
            <div class="card">
                <h3>Prezzo Biglietto Default</h3>
                <form action="{{ route('admin.update_price') }}" method="POST">
                    @csrf
                    <div class="inline-form" style="flex-wrap: nowrap; align-items: center;">
                        <span class="muted">€</span>
                        <input type="number" step="0.01" name="ticket_price" value="{{ $currentPrice }}" style="width: 100%;">
                    </div>
                    <button type="submit" class="btn btn--block" style="margin-top: 16px;">Aggiorna Prezzo</button>
                </form>
            </div>

            <div class="card">
                <h3>Creazione biglietti manuale</h3>
                <a href="{{ route('admin.pending') }}" class="btn btn--soft btn--block">Gestione CSV & In Attesa</a>
                <a href="{{ route('admin.create_ticket_form') }}" class="btn btn--soft-ok btn--block">Crea Biglietto Singolo</a>
            </div>

            <div class="card toolbar">
                <form action="{{ route('admin.dashboard') }}" method="GET" class="inline-form">
                    <div>
                        <label class="label--small" for="search">Cerca (Nome, Email, Codice)</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Es. Mario o TK_...">
                    </div>
                    <div>
                        <label class="label--small" for="date_from">Da Data</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div>
                        <label class="label--small" for="date_to">A Data</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <button type="submit" class="btn btn--dark btn--small">Filtra</button>
                    <a href="{{ route('admin.dashboard') }}" class="small muted">Resetta</a>
                </form>
                <a href="{{ route('admin.export_csv', request()->query()) }}" class="btn btn--ok btn--small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Esporta CSV
                </a>
            </div>
        </div>

        <div class="card" style="margin-top: 32px;">
            <h2>Iscritti Trovati ({{ $activeTickets->count() }})</h2>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Codice / Ordine</th>
                            <th>Utente</th>
                            <th>Data</th>
                            <th>Codice fiscale</th>
                            <th>Pagamento</th>
                            <th class="center">Taglia/Percorso</th>
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
                                        <div class="tags"><span class="tag tag--assoc">🏃‍♂️ Assoc: {{ $ticket->order->association_name ?? 'Non specificata' }}</span></div>
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

                                <td class="center">
                                    <div>{{ $ticket->tshirt_size }}</div>
                                    <div class="tiny muted">{{ $ticket->route_choice }}</div>
                                </td>

                                <td class="right money">€ {{ number_format($ticket->price_paid, 2, ',', '.') }}</td>

                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.edit_ticket', $ticket->id) }}" title="Modifica e Tag"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2f5d8a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.5 15H7a4 4 0 0 0-4 4v2"/><path d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><circle cx="10" cy="7" r="4"/></svg></a>
                                        <a href="{{ route('admin.download_pdf', $ticket->id) }}" title="Scarica PDF"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#1f2328" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg></a>
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
