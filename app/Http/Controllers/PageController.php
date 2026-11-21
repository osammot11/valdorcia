<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Throwable;

class PageController extends Controller
{
    private const PAGES = [
        '' => [
            'view' => 'pages.home',
            'title' => "Val d'Orcia Gravel | Strade bianche in Val d'Orcia",
            'description' => "Val d'Orcia Gravel: manifestazione cicloturistica sulle strade bianche della Val d'Orcia, patrimonio UNESCO. VI edizione il 16-17 ottobre 2027 a Montalcino, iscrizioni dal 10 dicembre 2026.",
            'bodyClass' => 'home-page',
        ],
        'contattaci' => [
            'view' => 'pages.contattaci',
            'title' => "Contatti | Val d'Orcia Gravel",
            'description' => "Scrivi allo staff di Val d'Orcia Gravel per iscrizioni, percorsi e logistica.",
        ],
        'contatti' => [
            'view' => 'pages.contattaci',
            'title' => "Contatti | Val d'Orcia Gravel",
            'description' => "Scrivi allo staff di Val d'Orcia Gravel per iscrizioni, percorsi e logistica.",
        ],
        'cookie-policy' => [
            'view' => 'pages.cookie-policy',
            'title' => "Cookie Policy | Val d'Orcia Gravel",
            'description' => "Informativa sull'uso dei cookie del sito Val d'Orcia Gravel.",
        ],
        'merchandising' => [
            'view' => 'pages.merchandising',
            'title' => "Merchandising | Val d'Orcia Gravel",
            'description' => "La maglia ufficiale di Val d'Orcia Gravel by Natali: ordina online e ritira allo stand durante la manifestazione.",
        ],
        'partnership' => [
            'view' => 'pages.partnership',
            'title' => "Sponsor e partner | Val d'Orcia Gravel",
            'description' => "Le realtà del territorio che sostengono Val d'Orcia Gravel.",
        ],
        'privacy-policy' => [
            'view' => 'pages.privacy-policy',
            'title' => "Privacy Policy | Val d'Orcia Gravel",
            'description' => "Informativa privacy per iscrizioni e contatti del sito Val d'Orcia Gravel.",
        ],
        'regolamento' => [
            'view' => 'pages.regolamento',
            'title' => "Regolamento | Val d'Orcia Gravel",
            'description' => "Regolamento di Val d'Orcia Gravel 2027, in via di definizione.",
        ],
        'programma' => [
            'view' => 'pages.programma',
            'title' => "Programma | Val d'Orcia Gravel",
            'description' => "Programma di Val d'Orcia Gravel 2027 a Montalcino e Radicofani, in via di definizione.",
        ],
        'chi-siamo' => [
            'view' => 'pages.chi-siamo',
            'title' => "Chi siamo | Val d'Orcia Gravel",
            'description' => "ASD Team Orso on Bike, l'associazione di Montalcino che organizza Val d'Orcia Gravel.",
        ],
        'percorsi' => [
            'view' => 'pages.percorsi',
            'title' => "Percorsi | Val d'Orcia Gravel",
            'description' => "I quattro percorsi di Val d'Orcia Gravel: Frugale, Saporito, Goloso e Luculliano, da 45 a 220 km. Tracce 2027 in via di definizione.",
        ],
        'come-arrivare' => [
            'view' => 'pages.come-arrivare',
            'title' => "Come arrivare | Val d'Orcia Gravel",
            'description' => "I luoghi di Val d'Orcia Gravel 2027: partenza da Montalcino, tappa del Luculliano a Radicofani.",
        ],
        'gallery' => [
            'view' => 'pages.gallery',
            'title' => "Foto | Val d'Orcia Gravel",
            'description' => "Le foto dei partecipanti di Val d'Orcia Gravel: come trovarle con un selfie sul portale Endupix.",
        ],
        'termini-condizioni' => [
            'view' => 'pages.regolamento',
            'title' => "Regolamento | Val d'Orcia Gravel",
            'description' => "Regolamento di Val d'Orcia Gravel 2027, in via di definizione.",
        ],
    ];

    private const HOME_ALIASES = [
        'faq',
        'styleguide',
    ];

    public function show(?string $slug = ''): Response
    {
        $slug = trim((string) $slug, '/');
        $slug = Str::replaceEnd('.php', '', $slug);

        if (in_array($slug, self::HOME_ALIASES, true)) {
            $slug = '';
        }

        abort_unless(array_key_exists($slug, self::PAGES), 404);

        $page = self::PAGES[$slug];
        $data = [
            'title' => $page['title'],
            'description' => $page['description'],
            'canonical' => url($slug === '' ? '/' : '/' . $slug),
            'bodyClass' => $page['bodyClass'] ?? '',
        ];

        if ($page['view'] === 'pages.home') {
            try {
                $data['totalIscritti'] = Ticket::whereNotNull('unique_ticket_code')->count();
            } catch (Throwable $e) {
                $data['totalIscritti'] = 0;
            }
        }

        return response()->view($page['view'], $data);
    }
}
