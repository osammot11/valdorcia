{{--
    Blocco di un partecipante nel modulo di iscrizione.
    $i = indice nel nome dei campi, $t = valori inviati in precedenza (old input), $position = numero mostrato.
--}}
@php
    $v = fn (string $key, $default = '') => $t[$key] ?? $default;
    $fe = function (string $key) use ($errors, $i) {
        $message = $errors->first("tickets.$i.$key");
        return $message ? new \Illuminate\Support\HtmlString('<p class="field-error">' . e($message) . '</p>') : '';
    };
    $country = $v('country', 'Italia');
    $abroad = $country !== 'Italia';
    $selectedRoute = $v('route_choice', $position === 1 ? request('percorso') : '');
    $membership = $v('membership');
@endphp
<div class="ticket-block top-margin-large">
    <h3>Partecipante #{{ $position }}</h3>

    <p class="block-title">Percorso</p>
    <div class="label-box">
        <label>Scelta del percorso</label>
        <select name="tickets[{{ $i }}][route_choice]" class="route-select" required>
            <option value="">Scegli il percorso</option>
            @foreach ($routes as $value => $route)
                @php $price = $displayTier->priceFor($value); $full = $remaining[$value] < 1; @endphp
                <option value="{{ $value }}" data-price="{{ $price }}" data-certificate="{{ $route['certificate'] }}" @disabled($full || $price === null) @selected($selectedRoute === $value && ! $full)>
                    {{ $route['name'] }} — {{ $route['km'] }} km, {{ $route['elevation'] }} m D+ · {{ $full ? 'al completo' : ($price !== null ? $euro($price) : 'non disponibile') }}
                </option>
            @endforeach
        </select>
        {{ $fe('route_choice') }}
    </div>

    <p class="block-title">Dati personali</p>
    <div class="grid-2 gap-20">
        <div class="label-box">
            <label>Nome</label>
            <input type="text" name="tickets[{{ $i }}][first_name]" value="{{ $v('first_name') }}" placeholder="Nome" required autocomplete="given-name">
            {{ $fe('first_name') }}
        </div>

        <div class="label-box">
            <label>Cognome</label>
            <input type="text" name="tickets[{{ $i }}][last_name]" value="{{ $v('last_name') }}" placeholder="Cognome" required autocomplete="family-name">
            {{ $fe('last_name') }}
        </div>

        <div class="label-box">
            <label>Data di nascita</label>
            <input type="date" name="tickets[{{ $i }}][dob]" value="{{ $v('dob') }}" required title="Data di nascita">
            {{ $fe('dob') }}
        </div>

        <div class="label-box">
            <label>Città di nascita</label>
            <input type="text" name="tickets[{{ $i }}][birth_place]" value="{{ $v('birth_place') }}" placeholder="Luogo di nascita" required>
            {{ $fe('birth_place') }}
        </div>

        <div class="label-box">
            <label>Nazionalità (sigla)</label>
            <input type="text" name="tickets[{{ $i }}][nationality]" value="{{ $v('nationality') }}" placeholder="Es. IT" maxlength="2" oninput="this.value = this.value.toUpperCase()" required>
            {{ $fe('nationality') }}
        </div>

        <div class="label-box">
            <label>Codice fiscale (se cittadino italiano)</label>
            <input type="text" name="tickets[{{ $i }}][codice_fiscale]" value="{{ $v('codice_fiscale') }}" placeholder="Codice fiscale" maxlength="16" oninput="this.value = this.value.toUpperCase()">
            {{ $fe('codice_fiscale') }}
        </div>

        <div class="label-box">
            <label>Email</label>
            <input type="email" name="tickets[{{ $i }}][email]" value="{{ $v('email') }}" placeholder="Email" required autocomplete="email">
            {{ $fe('email') }}
        </div>

        <div class="label-box">
            <label>Telefono</label>
            <input type="tel" name="tickets[{{ $i }}][phone]" value="{{ $v('phone') }}" placeholder="Telefono" required autocomplete="tel">
            {{ $fe('phone') }}
        </div>
    </div>

    <div class="grid-3 gap-20 top-margin-mid">
        <div class="label-box">
            <label>Indirizzo</label>
            <input type="text" name="tickets[{{ $i }}][residence_address]" value="{{ $v('residence_address') }}" placeholder="Indirizzo" required>
            {{ $fe('residence_address') }}
        </div>

        <div class="label-box">
            <label>Città</label>
            <input type="text" name="tickets[{{ $i }}][city]" value="{{ $v('city') }}" placeholder="Città" required>
            {{ $fe('city') }}
        </div>

        <div class="label-box">
            <label>CAP</label>
            <input type="text" name="tickets[{{ $i }}][zip_code]" value="{{ $v('zip_code') }}" placeholder="CAP" required>
            {{ $fe('zip_code') }}
        </div>

        <div class="label-box">
            <label>Paese</label>
            <select class="country-select" onchange="toggleCountry(this)">
                <option value="Italia" @selected(! $abroad)>Italia</option>
                <option value="Estero" @selected($abroad)>Estero (Specifica...)</option>
            </select>

            <input type="hidden" name="tickets[{{ $i }}][country]" class="country-hidden" value="{{ $country }}">

            <input type="text" placeholder="Specifica nazione" class="country-text {{ $abroad ? '' : 'hidden' }}" value="{{ $abroad ? $country : '' }}" oninput="updateCountryHidden(this)" @required($abroad)>
            {{ $fe('country') }}
        </div>

        <div class="label-box">
            <label>Regione</label>
            <select name="tickets[{{ $i }}][region]" class="region-select {{ $abroad ? 'hidden' : '' }}" onchange="updateProvinces(this)" data-selected="{{ $abroad ? '' : $v('region') }}" @disabled($abroad) required></select>

            <input type="text" name="tickets[{{ $i }}][region]" value="{{ $abroad ? $v('region') : '' }}" placeholder="Regione/Stato" class="region-text {{ $abroad ? '' : 'hidden' }}" @disabled(! $abroad) required>
            {{ $fe('region') }}
        </div>

        <div class="label-box">
            <label>Provincia</label>
            <select name="tickets[{{ $i }}][province]" class="province-select {{ $abroad ? 'hidden' : '' }}" data-selected="{{ $abroad ? '' : $v('province') }}" @disabled($abroad) required></select>

            <input type="text" name="tickets[{{ $i }}][province]" value="{{ $abroad ? $v('province') : '' }}" placeholder="Provincia/Contea" class="province-text {{ $abroad ? '' : 'hidden' }}" @disabled(! $abroad) required>
            {{ $fe('province') }}
        </div>
    </div>

    <p class="block-title">Tessera e certificato medico</p>
    <div class="grid-2 gap-20">
        <div class="label-box">
            <label>Tessera sportiva per il ciclismo</label>
            <select name="tickets[{{ $i }}][membership]" class="membership-select" required>
                <option value="">Scegli</option>
                @foreach (config('event.memberships') as $option)
                    <option value="{{ $option }}" @selected($membership === $option)>{{ $option }}</option>
                @endforeach
                <option value="giornaliera" @selected($membership === 'giornaliera')>Non sono tesserato: tessera giornaliera (+{{ $euro($dayCardPrice) }})</option>
            </select>
            {{ $fe('membership') }}
        </div>

        <div class="label-box membership-number-box">
            <label>Numero di tessera</label>
            <input type="text" name="tickets[{{ $i }}][membership_number]" value="{{ $v('membership_number') }}" class="membership-number" placeholder="Numero di tessera" required>
            {{ $fe('membership_number') }}
        </div>
    </div>

    <div class="label-box top-margin-mid">
        <label>Certificato medico</label>
        <input type="file" name="tickets[{{ $i }}][medical_certificate]" class="certificate-input" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required>
        <p class="small-note certificate-hint">PDF o foto, massimo 5 MB. Il tipo di certificato dipende dal percorso: scegli prima il percorso.</p>
        {{ $fe('medical_certificate') }}
        <p class="field-error certificate-error" role="alert" hidden></p>
    </div>

    <div class="top-margin-mid">
        <label>
            <input type="hidden" name="tickets[{{ $i }}][celiac]" value="0">
            <input type="checkbox" name="tickets[{{ $i }}][celiac]" value="1" @checked($v('celiac') === '1')> <span>Ai ristori mi servono alimenti senza glutine</span>
        </label>
    </div>
</div>
