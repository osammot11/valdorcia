<?php

// Messaggi di validazione in italiano per le regole usate dal sito e dall'admin.
// Le regole non elencate qui usano i messaggi inglesi di Laravel.
return [
    'accepted' => 'Devi accettare :attribute.',
    'after_or_equal' => ':Attribute deve essere uguale o successiva a :date.',
    'alpha_dash' => ':Attribute può contenere solo lettere, numeri, trattini e trattini bassi.',
    'array' => ':Attribute non è valido.',
    'before' => ':Attribute deve essere precedente a :date.',
    'boolean' => ':Attribute non è valido.',
    'date' => ':Attribute non è una data valida.',
    'email' => ':Attribute non è un indirizzo email valido.',
    'file' => ':Attribute deve essere un file.',
    'in' => ':Attribute non è valido.',
    'integer' => ':Attribute deve essere un numero intero.',
    'max' => [
        'array' => 'Puoi iscrivere al massimo :max partecipanti alla volta.',
        'file' => ':Attribute non può superare :max kilobyte.',
        'numeric' => ':Attribute non può essere maggiore di :max.',
        'string' => ':Attribute non può superare :max caratteri.',
    ],
    'mimes' => ':Attribute deve essere un file di tipo: :values.',
    'min' => [
        'array' => 'Serve almeno :min partecipante.',
        'numeric' => ':Attribute deve essere almeno :min.',
        'string' => ':Attribute deve avere almeno :min caratteri.',
    ],
    'numeric' => ':Attribute deve essere un numero.',
    'required' => 'Il campo :attribute è obbligatorio.',
    'required_if' => 'Il campo :attribute è obbligatorio.',
    'string' => ':Attribute non è valido.',

    'custom' => [
        'tickets.*.dob' => ['before' => 'La data di nascita non è valida.'],
    ],

    'attributes' => [
        'payment_method' => 'metodo di pagamento',
        'association_name' => 'nome associazione',
        'access_code' => 'codice ex iscritti',
        'truthful_declaration' => 'la dichiarazione sui documenti',
        'tickets' => 'partecipanti',
        'tickets.*.first_name' => 'nome',
        'tickets.*.last_name' => 'cognome',
        'tickets.*.codice_fiscale' => 'codice fiscale',
        'tickets.*.route_choice' => 'percorso',
        'tickets.*.dob' => 'data di nascita',
        'tickets.*.birth_place' => 'città di nascita',
        'tickets.*.residence_address' => 'indirizzo',
        'tickets.*.city' => 'città',
        'tickets.*.zip_code' => 'CAP',
        'tickets.*.province' => 'provincia',
        'tickets.*.region' => 'regione',
        'tickets.*.country' => 'paese',
        'tickets.*.nationality' => 'nazionalità',
        'tickets.*.email' => 'email',
        'tickets.*.phone' => 'telefono',
        'tickets.*.membership' => 'tessera sportiva',
        'tickets.*.membership_number' => 'numero di tessera',
        'tickets.*.medical_certificate' => 'certificato medico',
        'tickets.*.celiac' => 'senza glutine',
    ],
];
