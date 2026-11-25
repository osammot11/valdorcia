<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Invio delle email transazionali con l'API di Brevo (conferme, cambi di percorso, promemoria). */
class Brevo
{
    public static function send(string $email, string $name, string $subject, string $html): void
    {
        $response = Http::withHeaders([
            'api-key' => config('services.brevo.key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => config('services.brevo.sender_name'),
                'email' => config('services.brevo.sender_email'),
            ],
            'to' => [['email' => $email, 'name' => $name]],
            'subject' => $subject,
            'htmlContent' => $html,
        ]);

        // Un errore fa fallire il job: la coda lo riprova
        if ($response->failed()) {
            Log::error('Errore invio Brevo: ' . $response->body());
            throw new \Exception('Chiamata API Brevo fallita.');
        }
    }
}
