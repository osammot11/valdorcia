<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTicketEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
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

        $htmlContent = "
            <h2>Ciao {$name}, iscrizione confermata!</h2>
            <p>La tua partecipazione a " . e(config("event.name")) . " " . config('event.edition_year') . " è ufficiale.</p>
            <p><strong>Percorso:</strong> {$routeLine}<br><strong>Quando e dove:</strong> {$when}</p>
            <p>Puoi scaricare il tuo biglietto nominativo in PDF quando vuoi:</p>
            <p><a href='{$thankYouLink}' style='padding: 10px 20px; background-color: #a9501f; color: white; text-decoration: none; border-radius: 0; display: inline-block; margin-top: 10px;'>Scarica il tuo biglietto</a></p>
            <p>Ricorda casco omologato, kit per le forature, pompa e borraccia{$lights}. Il programma completo è su " . e(url('/programma')) . ".</p>
            <p>A presto in Val d'Orcia!</p>

            <p>Se il pulsante non funziona, copia e incolla nel browser questo link:</p>

            <p>{$thankYouLink}</p>
        ";

        // Chiamata API diretta e leggerissima a Brevo
        $response = Http::withHeaders([
            "api-key" => env("BREVO_API_KEY"),
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ])->post("https://api.brevo.com/v3/smtp/email", [
            "sender" => [
                "name" => env("BREVO_SENDER_NAME", "Tommaso Giovannoni"),
                "email" => env(
                    "BREVO_SENDER_EMAIL",
                    "info@tommasogiovannoni.com",
                ),
            ],
            "to" => [
                [
                    "email" => $this->ticket->email,
                    "name" =>
                        $this->ticket->first_name .
                        " " .
                        $this->ticket->last_name,
                ],
            ],
            "subject" => "Iscrizione confermata - " . config("event.name"),
            "htmlContent" => $htmlContent,
        ]);

        // Controllo errori
        if ($response->failed()) {
            Log::error("Errore invio Brevo: " . $response->body());
            throw new \Exception("Chiamata API Brevo fallita.");
        }
    }
}
