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
            'description' => "Val d'Orcia Gravel: quattro percorsi gravel da 39 a 220 km sulle strade bianche intorno a Castiglione d'Orcia.",
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
        'partnership' => [
            'view' => 'pages.partnership',
            'title' => "Partner | Val d'Orcia Gravel",
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
            'description' => "Le regole essenziali per partecipare a Val d'Orcia Gravel.",
        ],
        'termini-condizioni' => [
            'view' => 'pages.regolamento',
            'title' => "Regolamento | Val d'Orcia Gravel",
            'description' => "Le regole essenziali per partecipare a Val d'Orcia Gravel.",
        ],
    ];

    private const HOME_ALIASES = [
        'come-arrivare',
        'gallery',
        'orario',
        'percorsi',
        'ricettivita',
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
