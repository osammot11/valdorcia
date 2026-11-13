<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione CSV & Attesa - {{ config('event.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/images/valdorcia-gravel/logo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/controls.css?v=1">
    <link rel="stylesheet" href="/css/admin.css?v=5">
</head>
<body>

    <div class="wrap">
        <div class="card page-head">
            <h1>Caricamento Massivo & Approvazioni</h1>
            <a href="{{ route('admin.dashboard') }}">&larr; Torna alla Dashboard</a>
        </div>

        @if(session('success'))
            <div class="alert alert--ok">{{ session('success') }}</div>
        @endif

        <div class="card" style="margin-bottom: 32px;">
            <h2>Carica Biglietti da CSV</h2>
            <p class="small muted" style="margin-bottom: 16px;">Il file deve avere le colonne in questo ordine esatto: <strong>Nome, Cognome, Email, Percorso, Taglia</strong>. (Senza intestazione o saltando la prima riga).</p>

            <form action="{{ route('admin.upload_csv') }}" method="POST" enctype="multipart/form-data" class="inline-form">
                @csrf
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit" class="btn">Importa File</button>
            </form>
        </div>

        <h2 style="margin-bottom: 16px;">Biglietti in Attesa ({{ $pendingTickets->count() }})</h2>

        @if($pendingTickets->isEmpty())
            <div class="alert alert--warn center">Non ci sono biglietti in attesa di approvazione.</div>
        @else
            <div class="grid grid--cards">
                @foreach($pendingTickets as $ticket)
                    <div class="card pending-card">
                        <span class="badge">PENDING</span>

                        <h3>{{ $ticket->first_name }} {{ $ticket->last_name }}</h3>
                        <p class="small muted email">{{ $ticket->email }}</p>

                        <div class="facts">
                            <p><strong>Percorso:</strong> {{ $ticket->route_choice }}</p>
                            <p><strong>Taglia:</strong> {{ $ticket->tshirt_size }}</p>
                            <p><strong>Gruppo:</strong> {{ $ticket->order->group_code ?? 'N/D' }}</p>
                        </div>

                        <div class="pending-actions">
                            <form action="{{ route('admin.approve_ticket', $ticket->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn--ok btn--small btn--block">Approva e invia email</button>
                            </form>

                            <form action="{{ route('admin.delete_ticket', $ticket->id) }}" method="POST" onsubmit="return confirm('Sicuro di voler eliminare questo biglietto?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger btn--small" title="Elimina">Elimina</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</body>
</html>
