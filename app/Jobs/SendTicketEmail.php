<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTicketEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticket;

    /** Percorso precedente: se c'è, l'email conferma un cambio di percorso invece dell'iscrizione. */
    public $previousRoute;

    public function __construct(Ticket $ticket, ?string $previousRoute = null)
    {
        $this->ticket = $ticket;
        $this->previousRoute = $previousRoute;
    }

    public function handle()
    {
        $thankYouLink = route("checkout.success", [
            "order_code" => $this->ticket->order->group_code,
        ]);

        $route = config("event.routes.{$this->ticket->route_choice}");
        $name = e($this->ticket->first_name);
        $routeLine = $route ? e($route['name'] . ' — ' . $route['km'] . ' km') : e($this->ticket->route_choice);
        $when = e(config('event.date_label') . ', ' . config('event.location'));
        $lights = $this->ticket->route_choice === 'luculliano' ? ' e luci anteriori e posteriori' : '';

        $isLuculliano = $this->ticket->route_choice === 'luculliano';
        $start = ! config('event.logistics_confirmed')
            ? 'luogo e orari saranno nel programma, ' . e(url('/programma'))
            : ($route
            ? e(($isLuculliano ? config('event.saturday_label') . ' ore 12:00' : config('event.sunday_label') . ' ore ' . $route['start']) . ', ' . config('event.start_place'))
            : '');
        $button = "<p><a href='{$thankYouLink}' style='padding: 10px 20px; background-color: #a9501f; color: white; text-decoration: none; border-radius: 0; display: inline-block; margin-top: 10px;'>Scarica il biglietto</a></p>";
        $fallback = "<p>Se il pulsante non funziona, copia e incolla nel browser questo link:</p><p>{$thankYouLink}</p>";

        // Certificato non ancora caricato: link al portale (poi arriva un promemoria ogni 10 giorni)
        $certificateNote = '';
        if ($this->ticket->missingCertificate() && $this->ticket->unique_ticket_code) {
            $certificateUrl = e($this->ticket->certificateUrl());
            $certificateType = ($route['certificate'] ?? 'agonistico') === 'agonistico' ? 'di idoneità agonistica per il ciclismo' : 'per attività sportiva non agonistica';
            $certificateNote = "<p><strong>Manca il certificato medico</strong> {$certificateType}. Caricalo qui appena ce l'hai: <a href='{$certificateUrl}'>{$certificateUrl}</a>. Finché manca ti mandiamo un promemoria ogni 10 giorni.</p>";
        }

        if ($this->previousRoute) {
            $previous = e(config("event.routes.{$this->previousRoute}.name", $this->previousRoute));
            $subject = 'Percorso modificato - ' . config('event.name');
            $htmlContent = "
                <h2>Ciao {$name}, hai cambiato percorso</h2>
                <p>Il tuo nuovo percorso è <strong>{$routeLine}</strong> (prima era {$previous}).</p>
                <p><strong>Partenza:</strong> {$start}</p>
                <p>Il codice del biglietto resta <strong>" . e($this->ticket->unique_ticket_code) . "</strong>, ma il PDF che avevi è superato: scarica quello aggiornato e porta questo al ritiro del pacco gara.</p>
                {$button}
                <p>Ricorda casco omologato, kit per le forature, pompa e borraccia{$lights}.</p>
                <p>Se il cambio non l'hai fatto tu, rispondi a questa email o scrivi a " . e(config('event.email')) . ".</p>
                {$fallback}
            ";
        } else {
            $subject = 'Iscrizione confermata - ' . config('event.name');
            $htmlContent = "
                <h2>Ciao {$name}, iscrizione confermata!</h2>
                <p>La tua partecipazione a " . e(config("event.name")) . " " . config('event.edition_year') . " è ufficiale.</p>
                <p><strong>Percorso:</strong> {$routeLine}<br><strong>Quando e dove:</strong> {$when}</p>
                <p>Puoi scaricare il tuo biglietto nominativo in PDF quando vuoi. Dalla stessa pagina puoi anche cambiare percorso fino al " . e(config('event.online_closes_label')) . ".</p>
                {$button}
                {$certificateNote}
                <p>Ricorda casco omologato, kit per le forature, pompa e borraccia{$lights}. Il programma completo è su " . e(url('/programma')) . ".</p>
                <p>A presto in Val d'Orcia!</p>
                {$fallback}
            ";
        }

        \App\Services\Brevo::send($this->ticket->email, $this->ticket->first_name . ' ' . $this->ticket->last_name, $subject, $htmlContent);
    }
}
