<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea biglietto manuale - {{ config('event.name') }}</title>
    <link rel="stylesheet" href="/css/admin.css?v=3">
</head>
<body>

    <div class="wrap wrap--narrow">
        <div class="card page-head">
            <h1>Crea Biglietto Manuale</h1>
            <a href="{{ route('admin.dashboard') }}">&larr; Torna alla Dashboard</a>
        </div>

        @if($errors->any())
            <div class="alert alert--error">
                <strong>Attenzione!</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card--roomy">
            <p class="intro">Compila tutti i dati dell'iscritto. Per motivi assicurativi è obbligatorio inserire correttamente i dati anagrafici e il codice fiscale. In fondo potrai specificare quanto ha pagato (es. contanti).</p>

            <form action="{{ route('admin.store_ticket') }}" method="POST">
                @csrf

                <h3 class="section-title">Dati Anagrafici e Contatti</h3>
                <div class="grid grid--2">
                    <input type="text" name="first_name" placeholder="Nome" value="{{ old('first_name') }}" required>
                    <input type="text" name="last_name" placeholder="Cognome" value="{{ old('last_name') }}" required>

                    <input type="date" name="dob" value="{{ old('dob') }}" required title="Data di nascita">
                    <input type="text" name="birth_place" placeholder="Luogo di Nascita" value="{{ old('birth_place') }}" required>

                    <input type="text" name="nationality" placeholder="Nazionalità (es. IT)" value="{{ old('nationality', 'IT') }}" maxlength="2" required>
                    <input type="text" name="codice_fiscale" placeholder="Codice Fiscale (Obbligatorio per IT)" value="{{ old('codice_fiscale') }}" class="uppercase">

                    <input type="email" name="email" placeholder="Email (a cui inviare il PDF)" value="{{ old('email') }}" required>
                    <input type="text" name="phone" placeholder="Telefono" value="{{ old('phone') }}" required>
                </div>

                <h3 class="section-title">Residenza</h3>
                <div class="grid grid--3">
                    <input type="text" name="residence_address" placeholder="Indirizzo (Via/Piazza)" value="{{ old('residence_address') }}" required>
                    <input type="text" name="city" placeholder="Città" value="{{ old('city') }}" required>
                    <input type="text" name="zip_code" placeholder="CAP" value="{{ old('zip_code') }}" required>
                    <input type="text" name="province" placeholder="Provincia" value="{{ old('province') }}" required>
                    <input type="text" name="region" placeholder="Regione" value="{{ old('region') }}" required>
                    <input type="text" name="country" placeholder="Nazione" value="{{ old('country', 'Italia') }}" required>
                </div>

                <h3 class="section-title">Opzioni Gara</h3>
                <div class="grid grid--2">
                    <select name="route_choice" required>
                        <option value="">-- Scelta Percorso --</option>
                        @foreach (config('event.routes') as $value => $route)
                            <option value="{{ $value }}" @selected(old('route_choice') == $value)>{{ $route['km'] }} km — {{ $route['name'] }}</option>
                        @endforeach
                    </select>

                    <select name="tshirt_size" required>
                        <option value="">-- Taglia Maglia --</option>
                        @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)
                            <option value="{{ $size }}" {{ old('tshirt_size') == $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="checks">
                    <label>
                        <input type="hidden" name="shuttle_needed" value="0">
                        <input type="checkbox" name="shuttle_needed" value="1" {{ old('shuttle_needed') ? 'checked' : '' }}>
                        Necessità di Navetta
                    </label>

                    <label>
                        <input type="hidden" name="celiac" value="0">
                        <input type="checkbox" name="celiac" value="1" {{ old('celiac') ? 'checked' : '' }}>
                        Pasto per Celiaci
                    </label>
                </div>

                <h3 class="section-title">Dettagli Pagamento</h3>
                <div class="panel">
                    <label class="label" for="price_paid">Importo Pagato (€)</label>
                    <input type="number" step="0.01" id="price_paid" name="price_paid" value="{{ old('price_paid', '0.00') }}" required class="field--short">
                    <p class="small muted" style="margin-top: 8px;">Inserisci "0" per un ospite VIP omaggio, oppure la cifra esatta pagata in contanti/bonifico. Verrà sommata all'incasso totale della dashboard.</p>
                </div>

                <button type="submit" class="btn btn--ok btn--block btn--big">Salva Iscritto e Invia Biglietto</button>
            </form>
        </div>
    </div>

</body>
</html>
