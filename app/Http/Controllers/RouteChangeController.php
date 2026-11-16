<?php

namespace App\Http\Controllers;

use App\Jobs\SendTicketEmail;
use App\Models\Order;
use App\Models\PriceTier;
use App\Models\Ticket;
use App\Services\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Cambio di percorso fatto dall'iscritto dal link della pagina di conferma.
 * Si può passare solo a un percorso con la stessa quota, entro la chiusura delle iscrizioni online.
 */
class RouteChangeController extends Controller
{
    public function show(string $orderCode, string $ticketCode)
    {
        [$order, $ticket] = $this->find($orderCode, $ticketCode);

        return view('checkout.route-change', [
            'order' => $order,
            'ticket' => $ticket,
            'options' => $this->options($order, $ticket),
            'open' => $this->isOpen(),
        ]);
    }

    public function update(Request $request, string $orderCode, string $ticketCode)
    {
        [$order, $ticket] = $this->find($orderCode, $ticketCode);
        abort_unless($this->isOpen(), 403);

        $options = $this->options($order, $ticket);
        $allowed = collect($options)->filter(fn ($o) => $o['available'])->keys()->all();

        $validated = $request->validate([
            'route_choice' => ['required', Rule::in($allowed)],
            'medical_certificate' => [
                Rule::requiredIf(fn () => $options[$request->input('route_choice')]['needs_certificate'] ?? false),
                'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120',
            ],
        ], [
            'route_choice.in' => 'Questo percorso non si può scegliere: forse nel frattempo è andato al completo.',
            'medical_certificate.required' => 'Per questo percorso serve il certificato medico agonistico: caricalo qui.',
            'medical_certificate.mimes' => 'Il certificato medico deve essere un PDF o una foto (JPG, PNG).',
            'medical_certificate.max' => 'Il certificato medico non può superare i 5 MB.',
        ]);

        $previous = $ticket->route_choice;
        $target = $validated['route_choice'];

        try {
            DB::transaction(function () use ($request, $order, $ticket, $target) {
                // Il posto attuale si libera e se ne occupa uno sul nuovo percorso: il totale non cambia
                if (Registration::remainingByRoute()[$target] < 1) {
                    throw new RuntimeException('Il percorso ' . config("event.routes.$target.name") . ' è al completo.');
                }

                $data = ['route_choice' => $target];
                if ($request->hasFile('medical_certificate')) {
                    $data['medical_certificate_path'] = $request->file('medical_certificate')->store('certificates/' . $order->group_code, 'local');
                }
                $ticket->update($data);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['route_choice' => $e->getMessage()]);
        }

        Log::info('Percorso cambiato dall\'iscritto', ['ticket' => $ticket->unique_ticket_code, 'from' => $previous, 'to' => $target]);
        SendTicketEmail::dispatch($ticket, $previous);

        return redirect()
            ->route('checkout.success', ['order_code' => $order->group_code])
            ->with('route_changed', 'Fatto: il biglietto di ' . $ticket->first_name . ' ' . $ticket->last_name . ' ora è per il ' . config("event.routes.$target.name") . '. Ti abbiamo mandato via email il PDF aggiornato.');
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

    private function isOpen(): bool
    {
        return Registration::today()->lte(Carbon::parse(config('event.online_closes_iso'), 'Europe/Rome'));
    }

    /**
     * Stato di ogni altro percorso per questo biglietto.
     *
     * @return array<string, array{available: bool, reason: ?string, needs_certificate: bool}>
     */
    private function options(Order $order, Ticket $ticket): array
    {
        $routes = config('event.routes');
        $tier = PriceTier::where('name', $order->discount_code)->first();
        $currentPrice = $tier?->priceFor($ticket->route_choice);
        $remaining = Registration::remainingByRoute();
        $hasCompetitiveCertificate = $ticket->medical_certificate_path
            && ($routes[$ticket->route_choice]['certificate'] ?? null) === 'agonistico';

        $options = [];
        foreach ($routes as $key => $route) {
            if ($key === $ticket->route_choice) {
                continue;
            }

            $price = $tier?->priceFor($key);
            $reason = match (true) {
                $currentPrice === null || $price === null => 'contact',
                abs($price - $currentPrice) > 0.009 => 'price',
                $remaining[$key] < 1 => 'full',
                default => null,
            };

            $options[$key] = [
                'available' => $reason === null,
                'reason' => $reason,
                'needs_certificate' => $route['certificate'] === 'agonistico' && ! $hasCompetitiveCertificate,
            ];
        }

        return $options;
    }
}
