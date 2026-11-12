<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Iscritto - {{ config('event.name') }}</title>
    <link rel="stylesheet" href="/admin/admin.css?v=1">
</head>
<body>

    <div class="wrap wrap--narrow">
        <div class="card page-head">
            <div>
                <h1>Modifica Iscritto</h1>
                <p class="sub">Biglietto: <span class="code">{{ $ticket->unique_ticket_code }}</span></p>
            </div>
            <a href="{{ route('admin.dashboard') }}">&larr; Torna alla Dashboard</a>
        </div>

        <div class="card card--roomy">
            <form action="{{ route('admin.update_ticket', $ticket->id) }}" method="POST">
                @csrf

                <div class="panel panel--warn">
                    <h3>🏷️ Tag Amministrativi (Uso interno)</h3>
                    <p class="small">Usa questi campi per segnare annotazioni, gruppi speciali o compiti (es. "Ritiro pacco anticipato", "Sponsor VIP").</p>
                    <div class="grid grid--2">
                        <input type="text" name="tag_1" value="{{ $ticket->tag_1 }}" placeholder="Tag 1">
                        <input type="text" name="tag_2" value="{{ $ticket->tag_2 }}" placeholder="Tag 2">
                    </div>
                </div>

                <h3 class="section-title" style="margin-top: 32px;">Dati Anagrafici e Gara</h3>
                <div class="grid grid--2">
                    <div><label class="label--small">Nome</label><input type="text" name="first_name" value="{{ $ticket->first_name }}"></div>
                    <div><label class="label--small">Cognome</label><input type="text" name="last_name" value="{{ $ticket->last_name }}"></div>
                    <div><label class="label--small">Email</label><input type="email" name="email" value="{{ $ticket->email }}"></div>
                    <div><label class="label--small">Telefono</label><input type="text" name="phone" value="{{ $ticket->phone }}"></div>
                    <div><label class="label--small">Codice Fiscale</label><input type="text" name="codice_fiscale" value="{{ $ticket->codice_fiscale }}" class="uppercase"></div>
                    <div><label class="label--small">Taglia Maglia</label><input type="text" name="tshirt_size" value="{{ $ticket->tshirt_size }}"></div>
                </div>

                <h3 class="section-title">Residenza</h3>
                <div class="grid grid--3">
                    <input type="text" name="residence_address" value="{{ $ticket->residence_address }}" placeholder="Indirizzo">
                    <input type="text" name="city" value="{{ $ticket->city }}" placeholder="Città">
                    <input type="text" name="zip_code" value="{{ $ticket->zip_code }}" placeholder="CAP">
                </div>

                <button type="submit" class="btn btn--block btn--big">Salva Modifiche</button>
            </form>
        </div>
    </div>

</body>
</html>
