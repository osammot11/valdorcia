@php
    $routes = config('event.routes');
    $dayCardPrice = (float) config('event.day_card_price');
    $euro = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €';
    // Prezzi mostrati nel modulo: scaglione pubblico, oppure quello riservato se è l'unico aperto
    $displayTier = $publicTier ?? $reservedTier;
    $codeRequired = ! $publicTier && $reservedTier;
@endphp
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iscrizione | {{ config('event.name') }} {{ config('event.edition_year') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="apple-touch-icon" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="stylesheet" href="/css/controls.css?v=2">
    <link rel="stylesheet" href="/css/checkout.css?v=6">
</head>
<body class="center-items checkout-body">

    <div class="checkout-wrapper stack-large">
        <div class="center-items checkout-logo"><a href="/" aria-label="Torna alla home"><img src="/images/valdorcia-gravel/logo-valdorcia-gravel.png" alt="{{ config('event.name') }}" width="154" height="148"></a></div>
        <div class="center-text stack-mid">
            <p class="checkout-kicker">{{ config('event.location') }} · {{ config('event.date_label') }}</p>
            <h1>Iscrizione {{ config('event.name') }} {{ config('event.edition_year') }}</h1>
            <p class="checkout-intro">Scegli il percorso e inserisci i dati di ogni partecipante. Per ognuno servono tessera sportiva (o la tessera giornaliera) e certificato medico.</p>
        </div>

        @if (! $displayTier)
            {{-- Nessuno scaglione aperto oggi --}}
            <div class="checkout-summary stack-mid center-text">
                <h3 style="justify-content:center">Iscrizioni online chiuse</h3>
                @if ($nextTier)
                    <p>Le iscrizioni online riaprono il <strong>{{ $nextTier->starts_on->translatedFormat('j F Y') }}</strong>.</p>
                @else
                    <p>Le iscrizioni online per questa edizione sono chiuse. Sabato {{ \Illuminate\Support\Str::after(config('event.saturday_label'), 'Sabato ') }} ci si può ancora iscrivere in loco a {{ $euro(config('event.onsite_price')) }}, solo per Frugale, Saporito e Goloso.</p>
                @endif
                <p><a href="/" class="hyperlink">Torna al sito</a></p>
            </div>
        @else
        <form action="{{ route('checkout.store') }}" method="POST" id="registration-form" enctype="multipart/form-data">
            @if($errors->any())
                <div class="bg-primary-hover">
                    <strong>Attenzione!</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @csrf

            {{-- Quote del periodo --}}
            <div class="checkout-summary price-box">
                <p class="checkout-kicker">{{ $displayTier->name }} · fino al {{ $displayTier->ends_on->translatedFormat('j F Y') }}</p>
                <ul class="price-list">
                    @foreach ($routes as $value => $route)
                        <li><span>{{ $route['name'] }} <small>{{ $route['km'] }} km</small></span><strong>{{ $displayTier->priceFor($value) !== null ? $euro($displayTier->priceFor($value)) : '—' }}</strong></li>
                    @endforeach
                </ul>
                <p class="small-note">Chi non è tesserato FCI o con un ente convenzionato aggiunge la tessera giornaliera da {{ $euro($dayCardPrice) }}, che include l'assicurazione. Il Luculliano comprende {{ $routes['luculliano']['includes'] }}.</p>
            </div>

            @if ($reservedTier)
                <div class="box-associazione stack-mid top-margin-large">
                    <div class="label-box">
                        <label for="access_code">Codice ex iscritti{{ $codeRequired ? '' : ' (facoltativo)' }}</label>
                        <input type="text" id="access_code" name="access_code" value="{{ old('access_code') }}" autocomplete="off" @required($codeRequired) oninput="this.value = this.value.toUpperCase()">
                        <p class="field-error" id="access-code-error" role="alert" hidden></p>
                    </div>
                    <p class="small-note">
                        @if ($codeRequired)
                            Fino al {{ $reservedTier->ends_on->translatedFormat('j F') }} l'iscrizione è riservata a chi ha partecipato alle edizioni precedenti, con il codice ricevuto dall'organizzazione.@if ($nextTier) Dal {{ $nextTier->starts_on->translatedFormat('j F') }} le iscrizioni sono aperte a tutti.@endif
                        @else
                            Hai partecipato a un'edizione precedente? Con il tuo codice paghi la quota promo: {{ collect($routes)->map(fn ($r, $k) => ($r['short_name'] ?? $r['name']) . ' ' . $euro($reservedTier->priceFor($k)))->implode(', ') }}.
                        @endif
                    </p>
                </div>
            @endif

            <div class="box-associazione stack-mid top-margin-large">
                <label>
                    <input type="checkbox" name="is_sports_association" value="1" @checked(old('is_sports_association')) onchange="document.getElementById('assoc_name_div').style.display = this.checked ? 'block' : 'none'">
                    <span>Ci iscriviamo come associazione sportiva</span>
                </label>
                <div class="label-box" id="assoc_name_div" style="display:{{ old('is_sports_association') ? 'block' : 'none' }}">
                    <label>Nome associazione</label>
                    <input type="text" name="association_name" value="{{ old('association_name') }}">
                </div>
            </div>

            <div id="tickets-container">
                <div class="ticket-block top-margin-large">
                    <h3>Partecipante #1</h3>

                    <p class="block-title">Percorso</p>
                    <div class="label-box">
                        <label>Scelta del percorso</label>
                        <select name="tickets[0][route_choice]" class="route-select" required>
                            <option value="">Scegli il percorso</option>
                            @foreach ($routes as $value => $route)
                                @php $price = $displayTier->priceFor($value); $full = $remaining[$value] < 1; @endphp
                                <option value="{{ $value }}" data-price="{{ $price }}" data-certificate="{{ $route['certificate'] }}" @disabled($full || $price === null) @selected(request('percorso') === $value && ! $full)>
                                    {{ $route['name'] }} — {{ $route['km'] }} km, {{ $route['elevation'] }} m D+ · {{ $full ? 'al completo' : ($price !== null ? $euro($price) : 'non disponibile') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <p class="block-title">Dati personali</p>
                    <div class="grid-2 gap-20">
                        <div class="label-box">
                            <label>Nome</label>
                            <input type="text" name="tickets[0][first_name]" placeholder="Nome" required autocomplete="given-name">
                        </div>

                        <div class="label-box">
                            <label>Cognome</label>
                            <input type="text" name="tickets[0][last_name]" placeholder="Cognome" required autocomplete="family-name">
                        </div>

                        <div class="label-box">
                            <label>Data di nascita</label>
                            <input type="date" name="tickets[0][dob]" required title="Data di nascita">
                        </div>

                        <div class="label-box">
                            <label>Città di nascita</label>
                            <input type="text" name="tickets[0][birth_place]" placeholder="Luogo di nascita" required>
                        </div>

                        <div class="label-box">
                            <label>Nazionalità (sigla)</label>
                            <input type="text" name="tickets[0][nationality]" placeholder="Es. IT" maxlength="2" oninput="this.value = this.value.toUpperCase()" required>
                        </div>

                        <div class="label-box">
                            <label>Codice fiscale (se cittadino italiano)</label>
                            <input type="text" name="tickets[0][codice_fiscale]" placeholder="Codice fiscale" maxlength="16" oninput="this.value = this.value.toUpperCase()">
                        </div>

                        <div class="label-box">
                            <label>Email</label>
                            <input type="email" name="tickets[0][email]" placeholder="Email" required autocomplete="email">
                        </div>

                        <div class="label-box">
                            <label>Telefono</label>
                            <input type="tel" name="tickets[0][phone]" placeholder="Telefono" required autocomplete="tel">
                        </div>
                    </div>

                    <div class="grid-3 gap-20 top-margin-mid">
                        <div class="label-box">
                            <label>Indirizzo</label>
                            <input type="text" name="tickets[0][residence_address]" placeholder="Indirizzo" required>
                        </div>

                        <div class="label-box">
                            <label>Città</label>
                            <input type="text" name="tickets[0][city]" placeholder="Città" required>
                        </div>

                        <div class="label-box">
                            <label>CAP</label>
                            <input type="text" name="tickets[0][zip_code]" placeholder="CAP" required>
                        </div>

                        <div class="label-box">
                            <label>Paese</label>
                            <select class="country-select" onchange="toggleCountry(this)">
                                <option value="Italia" selected>Italia</option>
                                <option value="Estero">Estero (Specifica...)</option>
                            </select>

                            <input type="hidden" name="tickets[0][country]" class="country-hidden" value="Italia">

                            <input type="text" placeholder="Specifica nazione" class="country-text hidden" oninput="updateCountryHidden(this)">
                        </div>

                        <div class="label-box">
                            <label>Regione</label>
                            <select name="tickets[0][region]" class="region-select" onchange="updateProvinces(this)" required></select>

                            <input type="text" name="tickets[0][region]" placeholder="Regione/Stato" class="region-text hidden" disabled required>
                        </div>

                        <div class="label-box">
                            <label>Provincia</label>
                            <select name="tickets[0][province]" class="province-select" required></select>

                            <input type="text" name="tickets[0][province]" placeholder="Provincia/Contea" class="province-text hidden" disabled required>
                        </div>
                    </div>

                    <p class="block-title">Tessera e certificato medico</p>
                    <div class="grid-2 gap-20">
                        <div class="label-box">
                            <label>Tessera sportiva per il ciclismo</label>
                            <select name="tickets[0][membership]" class="membership-select" required>
                                <option value="">Scegli</option>
                                @foreach (config('event.memberships') as $membership)
                                    <option value="{{ $membership }}">{{ $membership }}</option>
                                @endforeach
                                <option value="giornaliera">Non sono tesserato: tessera giornaliera (+{{ $euro($dayCardPrice) }})</option>
                            </select>
                        </div>

                        <div class="label-box membership-number-box">
                            <label>Numero di tessera</label>
                            <input type="text" name="tickets[0][membership_number]" class="membership-number" placeholder="Numero di tessera" required>
                        </div>
                    </div>

                    <div class="label-box top-margin-mid">
                        <label>Certificato medico</label>
                        <input type="file" name="tickets[0][medical_certificate]" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required>
                        <p class="small-note certificate-hint">PDF o foto, massimo 5 MB. Il tipo di certificato dipende dal percorso: scegli prima il percorso.</p>
                    </div>

                    <div class="top-margin-mid">
                        <label>
                            <input type="hidden" name="tickets[0][celiac]" value="0">
                            <input type="checkbox" name="tickets[0][celiac]" value="1"> <span>Ai ristori mi servono alimenti senza glutine</span>
                        </label>
                    </div>
                </div>
            </div>

            <button class="top-margin-large grey-btn" type="button" onclick="addParticipant()">+ Aggiungi un altro partecipante</button>

            <div class="stack-mid">
              <div class="label-box top-margin-xl">
                  <label>Metodo di pagamento</label>
                  <select name="payment_method">
                      <option value="stripe">Carta</option>
                      <option value="paypal">PayPal</option>
                  </select>
              </div>

              <label>
                  <input type="checkbox" name="truthful_declaration" value="1" required><span>Dichiaro, ai sensi del D.P.R. 445/2000, che i documenti caricati sono veritieri e corrispondono agli originali in mio possesso.</span>
              </label>

              <label>
                  <input type="checkbox" value="1" required><span>Ho letto e accetto il <a href="/regolamento" target="_blank">regolamento</a> e la <a href="/privacy-policy" target="_blank">privacy policy</a>. So che la quota non è rimborsabile né cedibile.</span>
              </label>

              <label>
                  <input type="checkbox" value="1" required><span>Acconsento al trattamento dei dati relativi alla salute (certificato medico ed eventuali esigenze alimentari) per gestire la mia partecipazione, ai sensi dell'art. 9 del Regolamento UE 2016/679 (GDPR).</span>
              </label>

              <button class="btn fullwidth" id="payment-button" type="submit">Procedi al pagamento</button>
            </div>
        </form>
        @endif
    </div>


    <script>
        // --- 1. DATI GEOGRAFICI ---
        const italyLocations = {
            "Abruzzo": ["Chieti", "L'Aquila", "Pescara", "Teramo"],
            "Basilicata": ["Matera", "Potenza"],
            "Calabria": ["Catanzaro", "Cosenza", "Crotone", "Reggio Calabria", "Vibo Valentia"],
            "Campania": ["Avellino", "Benevento", "Caserta", "Napoli", "Salerno"],
            "Emilia-Romagna": ["Bologna", "Ferrara", "Forlì-Cesena", "Modena", "Parma", "Piacenza", "Ravenna", "Reggio Emilia", "Rimini"],
            "Friuli-Venezia Giulia": ["Gorizia", "Pordenone", "Trieste", "Udine"],
            "Lazio": ["Frosinone", "Latina", "Rieti", "Roma", "Viterbo"],
            "Liguria": ["Genova", "Imperia", "La Spezia", "Savona"],
            "Lombardia": ["Bergamo", "Brescia", "Como", "Cremona", "Lecco", "Lodi", "Mantova", "Milano", "Monza e della Brianza", "Pavia", "Sondrio", "Varese"],
            "Marche": ["Ancona", "Ascoli Piceno", "Fermo", "Macerata", "Pesaro e Urbino"],
            "Molise": ["Campobasso", "Isernia"],
            "Piemonte": ["Alessandria", "Asti", "Biella", "Cuneo", "Novara", "Torino", "Verbano-Cusio-Ossola", "Vercelli"],
            "Puglia": ["Bari", "Barletta-Andria-Trani", "Brindisi", "Foggia", "Lecce", "Taranto"],
            "Sardegna": ["Cagliari", "Nuoro", "Oristano", "Sassari", "Sud Sardegna"],
            "Sicilia": ["Agrigento", "Caltanissetta", "Catania", "Enna", "Messina", "Palermo", "Ragusa", "Siracusa", "Trapani"],
            "Toscana": ["Arezzo", "Firenze", "Grosseto", "Livorno", "Lucca", "Massa-Carrara", "Pisa", "Pistoia", "Prato", "Siena"],
            "Trentino-Alto Adige": ["Bolzano", "Trento"],
            "Umbria": ["Perugia", "Terni"],
            "Valle d'Aosta": ["Aosta"],
            "Veneto": ["Belluno", "Padova", "Rovigo", "Treviso", "Venezia", "Verona", "Vicenza"]
        };

        function populateRegions(block) {
            const regionSelect = block.querySelector('.region-select');
            regionSelect.innerHTML = '<option value="">Seleziona Regione</option>';
            Object.keys(italyLocations).sort().forEach(region => {
                const isSelected = region === 'Toscana' ? 'selected' : '';
                regionSelect.innerHTML += `<option value="${region}" ${isSelected}>${region}</option>`;
            });
            updateProvinces(regionSelect, true);
        }

        function updateProvinces(regionSelect, setLocalDefault = false) {
            const block = regionSelect.closest('.ticket-block');
            const provinceSelect = block.querySelector('.province-select');
            const region = regionSelect.value;

            provinceSelect.innerHTML = '<option value="">Seleziona Provincia</option>';
            if (italyLocations[region]) {
                italyLocations[region].sort().forEach(prov => {
                    const isSelected = (setLocalDefault && prov === 'Siena') ? 'selected' : '';
                    provinceSelect.innerHTML += `<option value="${prov}" ${isSelected}>${prov}</option>`;
                });
            }
        }

        function toggleCountry(countrySelect) {
            const block = countrySelect.closest('.ticket-block');
            const isItaly = countrySelect.value === 'Italia';
            const countryHidden = block.querySelector('.country-hidden');
            const countryText = block.querySelector('.country-text');
            const regionSelect = block.querySelector('.region-select');
            const regionText = block.querySelector('.region-text');
            const provinceSelect = block.querySelector('.province-select');
            const provinceText = block.querySelector('.province-text');

            if (isItaly) {
                countryHidden.value = 'Italia';
                countryText.classList.add('hidden');
                countryText.required = false;
                regionText.classList.add('hidden');
                regionText.disabled = true;
                regionSelect.classList.remove('hidden');
                regionSelect.disabled = false;
                provinceText.classList.add('hidden');
                provinceText.disabled = true;
                provinceSelect.classList.remove('hidden');
                provinceSelect.disabled = false;
            } else {
                countryHidden.value = countryText.value;
                countryText.classList.remove('hidden');
                countryText.required = true;
                regionSelect.classList.add('hidden');
                regionSelect.disabled = true;
                regionText.classList.remove('hidden');
                regionText.disabled = false;
                provinceSelect.classList.add('hidden');
                provinceSelect.disabled = true;
                provinceText.classList.remove('hidden');
                provinceText.disabled = false;
            }
        }

        function updateCountryHidden(textInput) {
            const block = textInput.closest('.ticket-block');
            block.querySelector('.country-hidden').value = textInput.value;
        }

        const euro = new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' });
        const dayCardPrice = {{ $dayCardPrice }};
        const certificateHints = {
            'agonistico': 'Per questo percorso serve il certificato di idoneità agonistica per il ciclismo (per chi risiede all\'estero va bene quello rilasciato nel proprio paese). PDF o foto, massimo 5 MB.',
            'non agonistico': 'Per questo percorso basta il certificato per attività sportiva non agonistica. PDF o foto, massimo 5 MB.'
        };

        // Il totale è la somma di percorsi ed eventuali tessere giornaliere (il server lo ricalcola comunque)
        function updateTotal() {
            let total = 0;
            let missing = false;
            document.querySelectorAll('.ticket-block').forEach(block => {
                const option = block.querySelector('.route-select').selectedOptions[0];
                if (option && option.dataset.price) {
                    total += parseFloat(option.dataset.price);
                } else {
                    missing = true;
                }
                if (block.querySelector('.membership-select').value === 'giornaliera') {
                    total += dayCardPrice;
                }
            });

            const btn = document.getElementById('payment-button');
            btn.innerText = missing ? 'Procedi al pagamento' : `Procedi al pagamento - ${euro.format(total)}`;
        }

        function updateBlock(block) {
            // Tipo di certificato richiesto dal percorso scelto
            const option = block.querySelector('.route-select').selectedOptions[0];
            const hint = block.querySelector('.certificate-hint');
            if (option && option.dataset.certificate) {
                hint.textContent = certificateHints[option.dataset.certificate];
            }

            // Numero di tessera solo per chi è tesserato
            const dayCard = block.querySelector('.membership-select').value === 'giornaliera';
            const numberBox = block.querySelector('.membership-number-box');
            const number = block.querySelector('.membership-number');
            numberBox.classList.toggle('hidden', dayCard);
            number.disabled = dayCard;
            number.required = !dayCard;
        }

        document.addEventListener('change', event => {
            const block = event.target.closest('.ticket-block');
            if (block && event.target.matches('.route-select, .membership-select')) {
                updateBlock(block);
                updateTotal();
            }
        });

        let participantCount = 1;

        function addParticipant() {
            const container = document.getElementById('tickets-container');
            const clone = container.querySelector('.ticket-block').cloneNode(true);

            participantCount++;

            clone.querySelectorAll('input, select').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/\[\d+\]/, '[' + participantCount + ']');
                }

                if (input.type !== 'hidden' && input.type !== 'checkbox') {
                    input.value = "";
                }

                if (input.type === 'checkbox') {
                    input.checked = false;
                }
            });

            clone.querySelector('.country-select').value = "Italia";
            clone.querySelector('.country-hidden').value = "Italia";
            toggleCountry(clone.querySelector('.country-select'));
            populateRegions(clone);
            updateBlock(clone);
            clone.querySelector('.certificate-hint').textContent = 'PDF o foto, massimo 5 MB. Il tipo di certificato dipende dal percorso: scegli prima il percorso.';

            container.appendChild(clone);

            reindexParticipants();
            updateTotal();
        }

        function removeParticipant(button) {
            button.closest('.ticket-block').remove();

            reindexParticipants();
            updateTotal();
        }

        function reindexParticipants() {
            const blocks = document.querySelectorAll('.ticket-block');

            blocks.forEach((block, index) => {
                const h3 = block.querySelector('h3');

                if (index === 0) {
                    h3.innerHTML = "Partecipante #1";
                } else {
                    h3.innerHTML = `Partecipante #${index + 1} <button class="remove-btn" type="button" onclick="removeParticipant(this)">Rimuovi</button>`;
                }
            });
        }

        // Il codice ex iscritti si verifica prima dell'invio: se è sbagliato il modulo resta compilato
        const form = document.getElementById('registration-form');
        const codeInput = document.getElementById('access_code');
        if (form && codeInput) {
            let codeChecked = false;
            form.addEventListener('submit', async event => {
                if (codeChecked || codeInput.value.trim() === '') return;
                event.preventDefault();
                const error = document.getElementById('access-code-error');
                try {
                    const response = await fetch('{{ route('iscrizione.codice') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name=_token]').value, 'Accept': 'application/json' },
                        body: JSON.stringify({ code: codeInput.value })
                    });
                    const result = await response.json();
                    if (!result.valid) {
                        error.textContent = response.status === 429
                            ? 'Troppi tentativi: riprova tra un minuto.'
                            : (result.required ? 'Codice non valido: controlla quello ricevuto dall\'organizzazione.' : 'Codice non valido: togli il codice per iscriverti alla quota normale.');
                        error.hidden = false;
                        codeInput.focus();
                        return;
                    }
                } catch (e) {
                    // In caso di problemi di rete decide il server
                }
                error.hidden = true;
                codeChecked = true;
                form.requestSubmit(document.getElementById('payment-button'));
            });
            codeInput.addEventListener('input', () => { codeChecked = false; });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const firstBlock = document.querySelector('.ticket-block');
            if (!firstBlock) return; // iscrizioni chiuse
            populateRegions(firstBlock);
            updateBlock(firstBlock);
            updateTotal();
        });
    </script>
</body>
</html>
