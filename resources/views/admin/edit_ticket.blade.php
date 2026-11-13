<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Iscritto - {{ config('event.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="apple-touch-icon" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/controls.css?v=2">
    <link rel="stylesheet" href="/css/admin.css?v=8">
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
            <form action="{{ route('admin.update_ticket', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="panel panel--warn">
                    <h3>Tag amministrativi (uso interno)</h3>
                    <p class="small">Usa questi campi per segnare annotazioni, gruppi speciali o compiti (es. "Ritiro pacco anticipato", "Sponsor VIP").</p>
                    <div class="grid grid--2">
                        <input type="text" name="tag_1" value="{{ $ticket->tag_1 }}" placeholder="Tag 1">
                        <input type="text" name="tag_2" value="{{ $ticket->tag_2 }}" placeholder="Tag 2">
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert--error" style="margin-top: 24px;"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <h3 class="section-title" style="margin-top: 32px;">Dati anagrafici</h3>
                <div class="grid grid--2">
                    <div><label class="label--small">Nome</label><input type="text" name="first_name" value="{{ $ticket->first_name }}"></div>
                    <div><label class="label--small">Cognome</label><input type="text" name="last_name" value="{{ $ticket->last_name }}"></div>
                    <div><label class="label--small">Email</label><input type="email" name="email" value="{{ $ticket->email }}"></div>
                    <div><label class="label--small">Telefono</label><input type="text" name="phone" value="{{ $ticket->phone }}"></div>
                    <div><label class="label--small">Codice Fiscale</label><input type="text" name="codice_fiscale" value="{{ $ticket->codice_fiscale }}" class="uppercase"></div>
                    <div>
                        <label class="label--small">Percorso</label>
                        <select name="route_choice">
                            @foreach (config('event.routes') as $value => $route)
                                <option value="{{ $value }}" @selected($ticket->route_choice === $value)>{{ $route['name'] }} — {{ $route['km'] }} km</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h3 class="section-title">Tessera e certificato medico</h3>
                <div class="grid grid--2">
                    <div>
                        <label class="label--small">Tessera</label>
                        <select name="membership">
                            @foreach (config('event.memberships') as $membership)
                                <option value="{{ $membership }}" @selected($ticket->membership === $membership)>{{ $membership }}</option>
                            @endforeach
                            <option value="giornaliera" @selected($ticket->day_card)>Tessera giornaliera</option>
                        </select>
                    </div>
                    <div><label class="label--small">Numero tessera</label><input type="text" name="membership_number" value="{{ $ticket->membership_number }}"></div>
                    <div>
                        <label class="label--small">Certificato attuale</label>
                        @if ($ticket->medical_certificate_path)
                            <a href="{{ route('admin.download_certificate', $ticket->id) }}" class="btn btn--soft btn--small">Scarica certificato</a>
                        @else
                            <p class="small" style="color: var(--danger); font-weight: 700;">Non caricato</p>
                        @endif
                    </div>
                    <div><label class="label--small">Carica o sostituisci certificato</label><input type="file" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png"></div>
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
