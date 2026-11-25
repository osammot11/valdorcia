<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Services\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Portale del certificato medico: l'iscritto lo carica (o lo sostituisce) dopo l'iscrizione,
 * dal link della pagina di conferma e delle email. Aperto fino al giorno dell'evento.
 */
class CertificateController extends Controller
{
    public function show(string $orderCode, string $ticketCode)
    {
        [$order, $ticket] = $this->find($orderCode, $ticketCode);

        return view('checkout.certificate', [
            'order' => $order,
            'ticket' => $ticket,
            'route' => config("event.routes.{$ticket->route_choice}"),
            'open' => static::isOpen(),
        ]);
    }

    public function update(Request $request, string $orderCode, string $ticketCode)
    {
        [$order, $ticket] = $this->find($orderCode, $ticketCode);
        abort_unless(static::isOpen(), 403);

        $request->validate([
            'medical_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'truthful_declaration' => 'accepted',
        ], [
            'medical_certificate.required' => 'Scegli il file del certificato medico.',
            'medical_certificate.mimes' => 'Il certificato medico deve essere un PDF o una foto (JPG, PNG).',
            'medical_certificate.max' => 'Il certificato medico non può superare i 5 MB.',
            'truthful_declaration.accepted' => 'Devi dichiarare che il certificato è veritiero.',
        ]);

        $ticket->update([
            'medical_certificate_path' => $request->file('medical_certificate')->store('certificates/' . $order->group_code, 'local'),
            'certificate_reminded_at' => null,
        ]);

        Log::info('Certificato caricato dal portale', ['ticket' => $ticket->unique_ticket_code]);

        return redirect()
            ->route('certificate.show', [$order->group_code, $ticket->unique_ticket_code])
            ->with('certificate_saved', true);
    }

    /** Si può caricare fino al giorno dell'evento compreso */
    public static function isOpen(): bool
    {
        return Registration::today()->lte(Carbon::parse(config('event.date_iso'), 'Europe/Rome'));
    }

    /** @return array{0: Order, 1: Ticket} */
    private function find(string $orderCode, string $ticketCode): array
    {
        $order = Order::where('group_code', $orderCode)->where('status', 'paid')->firstOrFail();
        $ticket = $order->tickets()
            ->where('unique_ticket_code', $ticketCode)
            ->where('status', 'active')
            ->firstOrFail();

        return [$order, $ticket];
    }
}
