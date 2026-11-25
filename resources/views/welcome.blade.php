@php
    $routes = config('event.routes');
    $dayCardPrice = (float) config('event.day_card_price');
    $euro = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €';
    // Prezzi mostrati nel modulo: scaglione pubblico, oppure quello riservato se è l'unico aperto
    $displayTier = $publicTier ?? $reservedTier;
    $codeRequired = ! $publicTier && $reservedTier;
    $shareTitle = 'Iscrizione ' . config('event.name') . ' ' . config('event.edition_year');
    $shareDescription = $displayTier
        ? 'Iscriviti alla ' . config('event.name') . ' ' . config('event.edition_year') . ', ' . config('event.date_label') . ' a ' . config('event.location') . '.'
        : ($nextTier
            ? 'Le iscrizioni online alla ' . config('event.name') . ' ' . config('event.edition_year') . ' aprono il ' . $nextTier->starts_on->translatedFormat('j F Y') . '. Tutte le quote dei percorsi.'
            : 'Iscrizioni online chiuse. ' . config('event.name') . ', ' . config('event.date_label') . ' a ' . config('event.location') . '.');
@endphp
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iscrizione | {{ config('event.name') }} {{ config('event.edition_year') }}</title>
    <meta name="description" content="{{ $shareDescription }}">
    <link rel="canonical" href="{{ route('iscrizione') }}">
    <meta name="theme-color" content="#f3ecdf">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $shareTitle }}">
    <meta property="og:description" content="{{ $shareDescription }}">
    <meta property="og:url" content="{{ route('iscrizione') }}">
    <meta property="og:locale" content="it_IT">
    <meta property="og:image" content="{{ url('/images/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ config('event.name') }}, {{ config('event.date_label') }} a {{ config('event.location') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ url('/images/og-image.png') }}">
    <meta name="twitter:title" content="{{ $shareTitle }}">
    <meta name="twitter:description" content="{{ $shareDescription }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="apple-touch-icon" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="stylesheet" href="/css/controls.css?v=3">
    <link rel="stylesheet" href="/css/checkout.css?v=11">
</head>
<body class="center-items checkout-body">

    <div class="checkout-wrapper stack-large">
        <div class="center-items checkout-logo"><a href="/" aria-label="Torna alla home"><img src="/images/valdorcia-gravel/logo-valdorcia-gravel.png" alt="{{ config('event.name') }}" width="154" height="148"></a></div>
        <div class="center-text stack-mid">
            <p class="checkout-kicker">{{ config('event.location') }} · {{ config('event.date_label') }}</p>
            <h1>Iscrizione {{ config('event.name') }} {{ config('event.edition_year') }}</h1>
            @if ($displayTier)
                <p class="checkout-intro">Scegli il percorso e inserisci i dati di ogni partecipante. Per ognuno servono tessera sportiva (o la tessera giornaliera) e certificato medico.</p>
            @endif
        </div>

        @if (! $displayTier)
            {{-- Nessuno scaglione aperto oggi --}}
            <div class="checkout-summary closed-box stack-mid">
                @if ($nextTier)
                    @php
                        $opening = $nextTier->starts_on->translatedFormat('j F');
                        $chosen = $selectedRoute ? $routes[$selectedRoute] : null;
                        $chosenPrice = $selectedRoute ? $nextTier->priceFor($selectedRoute) : null;
                    @endphp
                    <h2 class="closed-title">Le iscrizioni online per la {{ config('event.name') }} {{ config('event.edition_year') }} riapriranno il {{ $nextTier->starts_on->translatedFormat('j F Y') }}!</h2>

                    @if ($chosen)
                        <p class="closed-route"><span>{{ $chosen['name'] }} · {{ $chosen['km'] }} km</span>@if ($chosenPrice !== null)<strong>{{ $euro($chosenPrice) }} dal {{ $opening }}</strong>@endif</p>
                    @endif

                    <p><strong>La {{ config('event.edition_number') }} edizione partirà da {{ config('event.location') }}!</strong> La {{ config('event.name') }} fa parte del circuito <a class="hyperlink" href="https://gravelroadstuscany.it/" target="_blank" rel="noopener">Gravel Roads Tuscany</a> e del circuito MUT.</p>

                    <div class="closed-actions">
                        <a class="btn" href="{{ route('iscrizione.ics') }}" download>Aggiungi al calendario</a>
                        <a class="btn btn-ghost" href="/percorsi{{ $selectedRoute ? '#' . $selectedRoute : '' }}">Guarda i percorsi</a>
                    </div>
                @else
                    <h2 class="closed-title">Iscrizioni online chiuse</h2>
                    <p>Le iscrizioni online per questa edizione sono chiuse. Sabato {{ \Illuminate\Support\Str::after(config('event.saturday_label'), 'Sabato ') }} ci si può ancora iscrivere in loco a {{ $euro(config('event.onsite_price')) }}, solo per Frugale, Saporito e Goloso.</p>
                @endif
                <p class="closed-back"><a href="/" class="hyperlink">Torna al sito</a></p>
            </div>

            @if ($tiers->isNotEmpty())
                <div class="checkout-summary price-box">
                    <p class="checkout-kicker">Quote {{ config('event.edition_year') }}</p>
                    @include('checkout._tiers')
                    <p class="small-note">Chi non è tesserato FCI o con un ente convenzionato aggiunge la tessera giornaliera da {{ $euro($dayCardPrice) }}, che include l'assicurazione. Il Luculliano comprende {{ $routes['luculliano']['includes'] }}.</p>
                </div>
            @endif
        @else
        <form action="{{ route('checkout.store') }}" method="POST" id="registration-form" enctype="multipart/form-data">
            @if($errors->any())
                @php
                    // Gli errori dei singoli partecipanti sono mostrati sotto ogni campo
                    $participantErrors = collect($errors->keys())->filter(fn ($k) => preg_match('/^tickets\.\d+\./', $k));
                    $generalErrors = collect($errors->keys())->diff($participantErrors)->flatMap(fn ($k) => $errors->get($k));
                @endphp
                <div class="form-alert" role="alert">
                    <strong>Attenzione!</strong>
                    <ul>
                        @foreach($generalErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        @if ($participantErrors->isNotEmpty())
                            <li>Controlla i campi segnati in rosso nei dati {{ $participantErrors->map(fn ($k) => explode('.', $k)[1])->unique()->count() > 1 ? 'dei partecipanti' : 'del partecipante' }}.</li>
                        @endif
                    </ul>
                    @if (old('tickets'))
                        <p>I dati inseriti sono stati conservati, ma per sicurezza il browser non tiene i file: ricarica il certificato medico di ogni partecipante.</p>
                    @endif
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
                <details class="tier-details">
                    <summary>Tutte le quote {{ config('event.edition_year') }}</summary>
                    @include('checkout._tiers')
                </details>
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
                    <label for="association_name">Nome associazione</label>
                    <input type="text" id="association_name" name="association_name" value="{{ old('association_name') }}">
                </div>
            </div>

            <div id="tickets-container">
                @foreach (old('tickets') ?: [0 => []] as $i => $t)
                    @include('checkout._participant', ['i' => $i, 't' => $t, 'position' => $loop->iteration])
                @endforeach
            </div>

            <button class="top-margin-large grey-btn" type="button" onclick="addParticipant()">+ Aggiungi un altro partecipante</button>

            <div class="stack-mid">
              <div class="label-box top-margin-xl">
                  <label for="payment_method">Metodo di pagamento</label>
                  <select id="payment_method" name="payment_method">
                      <option value="stripe" @selected(old('payment_method') !== 'paypal')>Carta</option>
                      <option value="paypal" @selected(old('payment_method') === 'paypal')>PayPal</option>
                  </select>
              </div>

              <label>
                  <input type="checkbox" name="truthful_declaration" value="1" required @checked(old('truthful_declaration'))><span>Dichiaro, ai sensi del D.P.R. 445/2000, che i documenti che carico, ora o in seguito, sono veritieri e corrispondono agli originali in mio possesso.</span>
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
            // Dopo un errore riprende la regione inviata, altrimenti Toscana / Siena
            const selected = regionSelect.dataset.selected || 'Toscana';
            Object.keys(italyLocations).sort().forEach(region => {
                const isSelected = region === selected ? 'selected' : '';
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
                    const wanted = provinceSelect.dataset.selected || 'Siena';
                    const isSelected = (setLocalDefault && prov === wanted) ? 'selected' : '';
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
            'agonistico': 'Per questo percorso serve il certificato di idoneità agonistica per il ciclismo (per chi risiede all\'estero va bene quello rilasciato nel proprio paese). PDF o foto, massimo 5 MB. Se non l\'hai ancora, lo carichi dopo dal link che ricevi via email.',
            'non agonistico': 'Per questo percorso basta il certificato per attività sportiva non agonistica. PDF o foto, massimo 5 MB. Se non l\'hai ancora, lo carichi dopo dal link che ricevi via email.'
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

        // Indice per i nuovi blocchi: dopo un errore i blocchi esistenti possono avere indici sparsi
        let participantCount = {{ collect(array_keys(old('tickets') ?: [0 => []]))->max() }};

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

            clone.querySelectorAll('.field-error').forEach(el => { if (el.classList.contains('certificate-error')) { el.hidden = true; } else { el.remove(); } });

            // Id del nuovo partecipante (t0-email -> t3-email) per etichette, suggerimenti ed errori
            const prefix = id => id.replace(/^t\d+-/, 't' + participantCount + '-');
            clone.querySelectorAll('[id]').forEach(el => { el.id = prefix(el.id); });
            clone.querySelectorAll('label[for]').forEach(el => { el.htmlFor = prefix(el.htmlFor); });
            clone.querySelectorAll('[aria-invalid]').forEach(el => el.removeAttribute('aria-invalid'));
            clone.querySelectorAll('[aria-describedby]').forEach(el => {
                // Gli errori del server sono stati tolti: restano solo suggerimento ed errore del certificato
                const ids = el.getAttribute('aria-describedby').split(' ').map(prefix).filter(id => clone.querySelector('#' + CSS.escape(id)));
                ids.length ? el.setAttribute('aria-describedby', ids.join(' ')) : el.removeAttribute('aria-describedby');
            });
            clone.querySelectorAll('[data-selected]').forEach(el => { el.dataset.selected = ''; });
            clone.querySelector('.country-select').value = "Italia";
            clone.querySelector('.country-hidden').value = "Italia";
            toggleCountry(clone.querySelector('.country-select'));
            populateRegions(clone);
            updateBlock(clone);
            clone.querySelector('.certificate-hint').textContent = "PDF o foto, massimo 5 MB. Il tipo di certificato dipende dal percorso: scegli prima il percorso. Se non l'hai ancora, iscriviti lo stesso: lo carichi dopo dal link che ricevi via email.";

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
                const h3 = block.querySelector('.participant-title');

                if (index === 0) {
                    h3.innerHTML = "Partecipante #1";
                } else {
                    h3.innerHTML = `Partecipante #${index + 1} <button class="remove-btn" type="button" onclick="removeParticipant(this)" aria-label="Rimuovi il partecipante ${index + 1}">Rimuovi</button>`;
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

        // Controllo del certificato prima dell'invio, così un file sbagliato non fa ricaricare la pagina
        const maxCertificateBytes = 5 * 1024 * 1024;
        document.addEventListener('change', event => {
            if (!event.target.matches('.certificate-input')) return;
            const file = event.target.files[0];
            const error = event.target.closest('.label-box').querySelector('.certificate-error');
            let message = '';
            if (file && !/\.(pdf|jpe?g|png)$/i.test(file.name)) {
                message = 'Il certificato deve essere un PDF o una foto (JPG, PNG).';
            } else if (file && file.size > maxCertificateBytes) {
                message = 'Il file supera i 5 MB: comprimilo o fotografa di nuovo il certificato.';
            }
            event.target.setCustomValidity(message);
            error.textContent = message;
            error.hidden = message === '';
        });

        document.addEventListener("DOMContentLoaded", function() {
            const blocks = document.querySelectorAll('.ticket-block');
            if (!blocks.length) return; // iscrizioni chiuse
            blocks.forEach(block => {
                populateRegions(block);
                updateBlock(block);
            });
            reindexParticipants();
            updateTotal();
        });
    </script>
</body>
</html>
