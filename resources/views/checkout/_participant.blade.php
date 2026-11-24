{{--
    Blocco di un partecipante nel modulo di iscrizione.
    $i = indice nel nome dei campi, $t = valori inviati in precedenza (old input), $position = numero mostrato.
--}}
@php
    $v = fn (string $key, $default = '') => $t[$key] ?? $default;
    // Id dei campi di questo partecipante (t0-first_name, ...): collegano etichette, suggerimenti ed errori.
    // addParticipant() in welcome.blade.php li riscrive quando clona il blocco.
    $id = fn (string $key) => "t{$i}-{$key}";
    $fe = function (string $key) use ($errors, $i, $id) {
        $message = $errors->first("tickets.$i.$key");
        return $message ? new \Illuminate\Support\HtmlString('<p class="field-error" id="' . $id($key) . '-error">' . e($message) . '</p>') : '';
    };
    // aria-describedby (suggerimento ed errore del server) e aria-invalid per il campo
    $describe = function (string $key, ?string $hint = null) use ($errors, $i, $id) {
        $ids = array_filter([$hint, $errors->has("tickets.$i.$key") ? $id($key) . '-error' : null]);
        $attributes = $ids ? ' aria-describedby="' . implode(' ', $ids) . '"' : '';
        $attributes .= $errors->has("tickets.$i.$key") ? ' aria-invalid="true"' : '';
        return new \Illuminate\Support\HtmlString($attributes);
    };
    $country = $v('country', 'Italia');
    $abroad = $country !== 'Italia';
    $selectedRoute = $v('route_choice', $position === 1 ? request('percorso') : '');
    $membership = $v('membership');
@endphp
<div class="ticket-block top-margin-large">
    <h2 class="participant-title">Partecipante #{{ $position }}</h2>

    <p class="block-title">Percorso</p>
    <div class="label-box">
        <label for="{{ $id('route_choice') }}">Scelta del percorso</label>
        <select id="{{ $id('route_choice') }}" name="tickets[{{ $i }}][route_choice]"{{ $describe('route_choice') }} class="route-select" required>
            <option value="">Scegli il percorso</option>
            @foreach ($routes as $value => $route)
                @php $price = $displayTier->priceFor($value); $full = $remaining[$value] < 1; @endphp
                <option value="{{ $value }}" data-price="{{ $price }}" data-certificate="{{ $route['certificate'] }}" @disabled($full || $price === null) @selected($selectedRoute === $value && ! $full)>
                    {{ $route['name'] }} — {{ $route['km'] }} km · {{ $full ? 'al completo' : ($price !== null ? $euro($price) : 'non disponibile') }}
                </option>
            @endforeach
        </select>
        {{ $fe('route_choice') }}
    </div>

    <p class="block-title">Dati personali</p>
    <div class="grid-2 gap-20">
        <div class="label-box">
            <label for="{{ $id('first_name') }}">Nome</label>
            <input type="text" id="{{ $id('first_name') }}" name="tickets[{{ $i }}][first_name]"{{ $describe('first_name') }} value="{{ $v('first_name') }}" placeholder="Nome" required autocomplete="given-name">
            {{ $fe('first_name') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('last_name') }}">Cognome</label>
            <input type="text" id="{{ $id('last_name') }}" name="tickets[{{ $i }}][last_name]"{{ $describe('last_name') }} value="{{ $v('last_name') }}" placeholder="Cognome" required autocomplete="family-name">
            {{ $fe('last_name') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('dob') }}">Data di nascita</label>
            <input type="date" id="{{ $id('dob') }}" name="tickets[{{ $i }}][dob]"{{ $describe('dob') }} value="{{ $v('dob') }}" required autocomplete="bday">
            {{ $fe('dob') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('birth_place') }}">Città di nascita</label>
            <input type="text" id="{{ $id('birth_place') }}" name="tickets[{{ $i }}][birth_place]"{{ $describe('birth_place') }} value="{{ $v('birth_place') }}" placeholder="Luogo di nascita" required>
            {{ $fe('birth_place') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('nationality') }}">Nazionalità (sigla)</label>
            <input type="text" id="{{ $id('nationality') }}" name="tickets[{{ $i }}][nationality]"{{ $describe('nationality') }} value="{{ $v('nationality') }}" placeholder="Es. IT" maxlength="2" oninput="this.value = this.value.toUpperCase()" required>
            {{ $fe('nationality') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('codice_fiscale') }}">Codice fiscale (se cittadino italiano)</label>
            <input type="text" id="{{ $id('codice_fiscale') }}" name="tickets[{{ $i }}][codice_fiscale]"{{ $describe('codice_fiscale') }} value="{{ $v('codice_fiscale') }}" placeholder="Codice fiscale" maxlength="16" oninput="this.value = this.value.toUpperCase()">
            {{ $fe('codice_fiscale') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('email') }}">Email</label>
            <input type="email" id="{{ $id('email') }}" name="tickets[{{ $i }}][email]"{{ $describe('email') }} value="{{ $v('email') }}" placeholder="Email" required autocomplete="email">
            {{ $fe('email') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('phone') }}">Telefono</label>
            <input type="tel" id="{{ $id('phone') }}" name="tickets[{{ $i }}][phone]"{{ $describe('phone') }} value="{{ $v('phone') }}" placeholder="Telefono" required autocomplete="tel">
            {{ $fe('phone') }}
        </div>
    </div>

    <div class="grid-3 gap-20 top-margin-mid">
        <div class="label-box">
            <label for="{{ $id('residence_address') }}">Indirizzo</label>
            <input type="text" id="{{ $id('residence_address') }}" name="tickets[{{ $i }}][residence_address]"{{ $describe('residence_address') }} value="{{ $v('residence_address') }}" placeholder="Indirizzo" required autocomplete="street-address">
            {{ $fe('residence_address') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('city') }}">Città</label>
            <input type="text" id="{{ $id('city') }}" name="tickets[{{ $i }}][city]"{{ $describe('city') }} value="{{ $v('city') }}" placeholder="Città" required autocomplete="address-level2">
            {{ $fe('city') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('zip_code') }}">CAP</label>
            <input type="text" id="{{ $id('zip_code') }}" name="tickets[{{ $i }}][zip_code]"{{ $describe('zip_code') }} value="{{ $v('zip_code') }}" placeholder="CAP" required autocomplete="postal-code" inputmode="numeric">
            {{ $fe('zip_code') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('country') }}">Paese di residenza</label>
            <select id="{{ $id('country') }}" class="country-select" onchange="toggleCountry(this)"{{ $describe('country') }}>
                <option value="Italia" @selected(! $abroad)>Italia</option>
                <option value="Estero" @selected($abroad)>Estero (Specifica...)</option>
            </select>

            <input type="hidden" name="tickets[{{ $i }}][country]" class="country-hidden" value="{{ $country }}">

            <input type="text" placeholder="Specifica nazione" aria-label="Nazione di residenza" autocomplete="country-name" class="country-text {{ $abroad ? '' : 'hidden' }}" value="{{ $abroad ? $country : '' }}" oninput="updateCountryHidden(this)" @required($abroad)>
            {{ $fe('country') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('region') }}">Regione</label>
            <select id="{{ $id('region') }}" name="tickets[{{ $i }}][region]"{{ $describe('region') }} autocomplete="address-level1" class="region-select {{ $abroad ? 'hidden' : '' }}" onchange="updateProvinces(this)" data-selected="{{ $abroad ? '' : $v('region') }}" @disabled($abroad) required></select>

            <input type="text" name="tickets[{{ $i }}][region]" value="{{ $abroad ? $v('region') : '' }}" placeholder="Regione/Stato" aria-label="Regione o stato di residenza" autocomplete="address-level1"{{ $describe('region') }} class="region-text {{ $abroad ? '' : 'hidden' }}" @disabled(! $abroad) required>
            {{ $fe('region') }}
        </div>

        <div class="label-box">
            <label for="{{ $id('province') }}">Provincia</label>
            <select id="{{ $id('province') }}" name="tickets[{{ $i }}][province]"{{ $describe('province') }} class="province-select {{ $abroad ? 'hidden' : '' }}" data-selected="{{ $abroad ? '' : $v('province') }}" @disabled($abroad) required></select>

            <input type="text" name="tickets[{{ $i }}][province]" value="{{ $abroad ? $v('province') : '' }}" placeholder="Provincia/Contea" aria-label="Provincia o contea di residenza"{{ $describe('province') }} class="province-text {{ $abroad ? '' : 'hidden' }}" @disabled(! $abroad) required>
            {{ $fe('province') }}
        </div>
    </div>

    <p class="block-title">Tessera e certificato medico</p>
    <div class="grid-2 gap-20">
        <div class="label-box">
            <label for="{{ $id('membership') }}">Tessera sportiva per il ciclismo</label>
            <select id="{{ $id('membership') }}" name="tickets[{{ $i }}][membership]"{{ $describe('membership') }} class="membership-select" required>
                <option value="">Scegli</option>
                {{-- La risposta più comune per prima --}}
                <option value="giornaliera" @selected($membership === 'giornaliera')>Non sono tesserato: tessera giornaliera (+{{ $euro($dayCardPrice) }})</option>
                <optgroup label="Sono tesserato con">
                    @foreach (config('event.memberships') as $option)
                        <option value="{{ $option }}" @selected($membership === $option)>{{ $option }}</option>
                    @endforeach
                </optgroup>
            </select>
            {{ $fe('membership') }}
        </div>

        <div class="label-box membership-number-box">
            <label for="{{ $id('membership_number') }}">Numero di tessera</label>
            <input type="text" id="{{ $id('membership_number') }}" name="tickets[{{ $i }}][membership_number]"{{ $describe('membership_number') }} value="{{ $v('membership_number') }}" class="membership-number" placeholder="Numero di tessera" required>
            {{ $fe('membership_number') }}
        </div>
    </div>

    <div class="label-box top-margin-mid">
        <label for="{{ $id('medical_certificate') }}">Certificato medico</label>
        <input type="file" id="{{ $id('medical_certificate') }}" name="tickets[{{ $i }}][medical_certificate]"{{ $describe('medical_certificate', $id('certificate-hint') . ' ' . $id('certificate-error')) }} class="certificate-input" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required>
        <p class="small-note certificate-hint" id="{{ $id('certificate-hint') }}">PDF o foto, massimo 5 MB. Il tipo di certificato dipende dal percorso: scegli prima il percorso.</p>
        {{ $fe('medical_certificate') }}
        <p class="field-error certificate-error" id="{{ $id('certificate-error') }}" role="alert" hidden></p>
    </div>

    <div class="top-margin-mid">
        <label>
            <input type="hidden" name="tickets[{{ $i }}][celiac]" value="0">
            <input type="checkbox" name="tickets[{{ $i }}][celiac]" value="1" @checked($v('celiac') === '1')> <span>Ai ristori mi servono alimenti senza glutine</span>
        </label>
    </div>
</div>
