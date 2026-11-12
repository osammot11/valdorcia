<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma iscrizione - {{ config('event.name') }}</title>
    <link rel="stylesheet" href="/admin/admin.css?v=1">
</head>
<body class="success">

    <div class="wrap wrap--slim card">
        <div class="success-head">
            <h1>Iscrizione Completata!</h1>
            <p>Ci vediamo a {{ config('event.location') }}. Grazie per la tua iscrizione a {{ config('event.name') }}.</p>
        </div>

        <div class="success-body">
            <div class="group-code">
                <p class="kicker">Codice Gruppo</p>
                <h2>{{ $order->group_code }}</h2>
            </div>

            <h3>I tuoi Biglietti Nominativi:</h3>
            <div class="ticket-list">
                @foreach($order->tickets as $ticket)
                    <div class="ticket-row">
                        <div>
                            <p class="name">{{ $ticket->first_name }} {{ $ticket->last_name }}</p>
                            <p class="small muted">{{ $ticket->route_choice }}</p>
                            <p class="tiny code">{{ $ticket->unique_ticket_code }}</p>
                        </div>

                        <a href="{{ route('ticket.download', $ticket->unique_ticket_code) }}" class="btn btn--ok btn--small">PDF</a>
                    </div>
                @endforeach
            </div>

            <div class="note">
                <p><strong>Nota:</strong> Abbiamo inviato un'email a ciascun partecipante con il link per tornare su questa pagina e scaricare il proprio biglietto in qualsiasi momento.</p>
            </div>

            <p class="center" style="margin-top: 32px;"><a href="/">&larr; Torna alla Home</a></p>
        </div>
    </div>

</body>
</html>
