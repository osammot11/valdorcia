<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\Brevo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Promemoria per il certificato medico non ancora caricato (parte ogni 10 giorni, vedi certificates:remind). */
class SendCertificateReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Ticket $ticket)
    {
    }

    public function handle(): void
    {
        $ticket = $this->ticket->fresh(['order']);

        // Nel frattempo è stato caricato: niente promemoria
        if (! $ticket || ! $ticket->missingCertificate()) {
            return;
        }

        $route = config("event.routes.{$ticket->route_choice}");
        $type = ($route['certificate'] ?? 'agonistico') === 'agonistico'
            ? "il certificato di idoneità agonistica per il ciclismo (per chi risiede all'estero va bene quello rilasciato nel proprio paese)"
            : 'il certificato per attività sportiva non agonistica';
        $url = e($ticket->certificateUrl());
        $name = e($ticket->first_name);
        $event = e(config('event.name') . ' ' . config('event.edition_year'));

        $html = "
            <h2>Ciao {$name}, manca il certificato medico</h2>
            <p>Per partecipare alla {$event} con il percorso <strong>" . e($route['name'] ?? $ticket->route_choice) . "</strong> serve {$type}.</p>
            <p>Non l'abbiamo ancora ricevuto: caricalo da questa pagina, bastano un PDF o una foto.</p>
            <p><a href='{$url}' style='padding: 10px 20px; background-color: #a9501f; color: white; text-decoration: none; display: inline-block;'>Carica il certificato</a></p>
            <p>Se il pulsante non funziona, copia e incolla nel browser questo link:<br>{$url}</p>
            <p>Finché il certificato manca ti mandiamo questo promemoria ogni 10 giorni. Il codice del tuo biglietto è <strong>" . e($ticket->unique_ticket_code) . "</strong>.</p>
            <p>Per domande scrivi a " . e(config('event.email')) . ".</p>
        ";

        Brevo::send($ticket->email, $ticket->first_name . ' ' . $ticket->last_name, 'Promemoria: carica il certificato medico - ' . config('event.name'), $html);
    }
}
