<?php

/*
|--------------------------------------------------------------------------
| Dati dell'evento
|--------------------------------------------------------------------------
|
| Unico punto in cui cambiare nome, date, luogo, contatti e percorsi.
| I valori marcati "DA CONFERMARE" sono segnaposto: distanze e località
| vengono dall'edizione 2026, la data 2027 non è ancora ufficiale.
|
*/

return [
    'name' => "Val d'Orcia Gravel",
    'edition_year' => 2027,
    'date_label' => 'Ottobre 2027', // DA CONFERMARE
    'date_iso' => null,             // es. '2027-10-17T08:00:00+02:00' quando ufficiale
    'location' => "Castiglione d'Orcia",
    'region' => 'Siena, Toscana',
    'email' => 'info@valdorciagravel.it', // DA CONFERMARE
    'phone' => null,
    'organizer' => "Val d'Orcia Gravel", // DA CONFERMARE: ragione sociale / ASD

    // La chiave è il valore salvato in database e validato dai controller.
    'routes' => [
        'Gravel 39K' => [
            'km' => 39,
            'name' => 'Corta',
            'level' => 'Per tutti',
            'text' => "Il giro più breve tra le crete e i borghi intorno a Castiglione d'Orcia. Adatto a chi si avvicina al gravel.",
        ],
        'Gravel 73K' => [
            'km' => 73,
            'name' => 'Media',
            'level' => 'Allenati',
            'text' => 'Una mattinata piena di strade bianche, salite brevi e panorami sulla valle.',
        ],
        'Gravel 118K' => [
            'km' => 118,
            'name' => 'Lunga',
            'level' => 'Esperti',
            'text' => "Una giornata in sella che attraversa la Val d'Orcia da un capo all'altro.",
        ],
        'Gravel 220K' => [
            'km' => 220,
            'name' => 'Ultra',
            'level' => 'Endurance',
            'text' => 'Per chi vuole vedere tutta la valle in un giorno, dall’alba al tramonto.',
        ],
    ],
];
