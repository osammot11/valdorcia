<?php

/*
|--------------------------------------------------------------------------
| Dati dell'evento
|--------------------------------------------------------------------------
|
| Unico punto in cui cambiare nomi, date, luoghi, contatti e percorsi.
| Fonte: sito ufficiale valdorciagravel.it (edizione 2026). Le date 2027
| sono spostate di un anno sulla stessa formula: DA CONFERMARE con
| l'organizzazione. I prezzi stanno nel database (Scaglioni nell'admin).
|
*/

return [
    'name' => "Val d'Orcia Gravel",
    'edition_year' => 2027,
    'edition_number' => 'VI',

    // Formula su due giorni: sabato ritiro pacchi e 1ª tappa del Luculliano, domenica tutti gli altri
    'date_label' => '16-17 ottobre 2027',
    'date_iso' => '2027-10-17',          // giorno delle partenze principali
    'start_date_iso' => '2027-10-16',
    'saturday_label' => 'Sabato 16 ottobre',
    'sunday_label' => 'Domenica 17 ottobre',
    'online_closes_label' => '14 ottobre 2027', // nel 2026: tre giorni prima della domenica
    'online_closes_iso' => '2027-10-14',        // ultimo giorno per iscriversi online e cambiare percorso

    'location' => "Castiglione d'Orcia",
    'start_place' => "Rocca d'Orcia",    // borgo medievale da cui partono Frugale, Saporito e Goloso
    'region' => 'Siena, Toscana',

    'email' => 'bike@bikemontalcino.it',
    'claims_email' => 'info@bikemontalcino.it',
    'phone' => '+39 347 053 5638',
    'phone_contact' => 'Mauro',

    'organizer' => 'ASD Team Orso on Bike',
    'organizer_address' => "Castelnuovo dell'Abate, Montalcino (SI)",
    'organizer_vat' => '01365130523',

    'social' => [
        'Instagram' => 'https://instagram.com/valdorciagravel',
        'Facebook' => 'https://www.facebook.com/valdorciagravel/',
    ],

    // Posti disponibili
    'capacity' => 900,

    // Tessera giornaliera per chi non è tesserato a FCI o a un ente convenzionato
    'day_card_price' => 10.00,
    'memberships' => ['FCI', 'ACSI', 'US ACLI', 'CSI', 'CSAIN', 'AICS', 'LIBERTAS', 'CSEN', 'UISP', 'OPES (con Bike Card)', 'ASI (con Bike Card)'],

    // Iscrizione il sabato in loco (solo Frugale, Saporito, Goloso)
    'onsite_price' => 50.00,

    // La chiave è il valore salvato in database e validato dai controller.
    'routes' => [
        'frugale' => [
            'name' => 'Frugale',
            'km' => 45,                   // il sito ufficiale riporta sia 39 sia 45 km: DA CONFERMARE
            'elevation' => 800,
            'level' => 'Per tutti',
            'certificate' => 'non agonistico',
            'start' => '9:00',
            'limit' => null,
            'gpx' => ['Traccia 2026' => 'https://valdorciagravel.it/wp-content/uploads/2026/10/Frugale_2026.gpx'],
            'text' => "Il percorso breve, adatto a tutti: in una mattinata tocca molti dei luoghi simbolo della Val d'Orcia, tra filari di cipressi e strade bianche, con un ristoro speciale lungo la strada.",
        ],
        'saporito' => [
            'name' => 'Saporito',
            'km' => 73,
            'elevation' => 1550,
            'level' => 'Equilibrato',
            'certificate' => 'agonistico',
            'start' => '8:00',
            'limit' => null,
            'gpx' => ['Traccia 2026' => 'https://valdorciagravel.it/wp-content/uploads/2026/10/saporito_2026.gpx'],
            'text' => "Il più equilibrato: impegnativo il giusto, attraversa i paesaggi più famosi della valle con due ristori dai sapori tipici.",
        ],
        'goloso' => [
            'name' => 'Goloso',
            'km' => 118,
            'elevation' => 2400,
            'level' => 'Allenati',
            'certificate' => 'agonistico',
            'start' => '7:00',
            'limit' => 300,
            'gpx' => ['Traccia 2026' => 'https://valdorciagravel.it/wp-content/uploads/2026/10/Goloso_2026.gpx'],
            'text' => "Una giornata intera in sella per chi è allenato: distanza e dislivello si fanno sentire, ripagati da passaggi come il castello di Contignano e quattro ristori, tra cui quello nel borgo di Castelnuovo dell'Abate.",
        ],
        'luculliano' => [
            'name' => 'Luculliano – Il Viaggio',
            'short_name' => 'Luculliano',
            'km' => 220,
            'elevation' => 4800,
            'level' => 'Due giorni',
            'certificate' => 'agonistico',
            'start' => 'sabato 12:00',
            'limit' => 100,
            'stages' => "Sabato circa 100 km da Rocca d'Orcia a Radicofani, domenica circa 120 km da Radicofani a Castiglione d'Orcia.",
            'includes' => 'cena del sabato a Radicofani, colazione della domenica e trasporto bagagli da e per Radicofani',
            'gpx' => ['1ª tappa 2026' => 'https://valdorciagravel.it/wp-content/uploads/2026/10/Luculliano_tappa_1_20261.gpx', '2ª tappa 2026' => 'https://valdorciagravel.it/wp-content/uploads/2026/10/Luculliano_tappa_2_2026.gpx'],
            'text' => "Un viaggio in bikepacking su due giorni: il primo nel foliage del Monte Amiata, il secondo dentro la Val d'Orcia. Percorso non segnalato, con traccia GPX fornita a tutti; luci anteriori e posteriori obbligatorie.",
        ],
    ],
];
