<?php

namespace App\Services;

use App\Jobs\SendTicketEmail;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderPaymentFinalizer
{
    /**
     * Segna l'ordine come pagato e attiva i biglietti. Può arrivare due volte quasi insieme
     * (webhook Stripe e ritorno sulla pagina di conferma): il lock sull'ordine fa sì che
     * codici ed email vengano generati una volta sola.
     */
    public function finalize(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'paid') {
                $locked->update(['status' => 'paid']);
            }

            foreach ($locked->tickets as $ticket) {
                $wasActive = $ticket->status === 'active';

                $ticket->update([
                    'status' => 'active',
                    'unique_ticket_code' => $ticket->unique_ticket_code ?? 'TKT_' . strtoupper(Str::random(8)),
                ]);

                if (! $wasActive) {
                    SendTicketEmail::dispatch($ticket)->afterCommit();
                }
            }
        });

        $order->refresh()->load('tickets');
    }
}
