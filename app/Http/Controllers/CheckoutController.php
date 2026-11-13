<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Services\OrderPaymentFinalizer;
use App\Services\Registration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $routes = config('event.routes');
        $memberships = config('event.memberships');

        // 1. Validazione
        $validatedData = $request->validate([
            'payment_method' => 'required|in:stripe,paypal',
            'is_sports_association' => 'boolean',
            'association_name' => 'required_if:is_sports_association,1|nullable|string|max:255',
            'access_code' => 'nullable|string|max:50',
            'truthful_declaration' => 'accepted',
            'tickets' => 'required|array|min:1|max:20',
            'tickets.*.first_name' => 'required|string|max:255',
            'tickets.*.last_name' => 'required|string|max:255',
            'tickets.*.codice_fiscale' => 'nullable|string|max:16',
            'tickets.*.route_choice' => ['required', Rule::in(array_keys($routes))],
            'tickets.*.dob' => 'required|date|before:today',
            'tickets.*.birth_place' => 'required|string|max:255',
            'tickets.*.residence_address' => 'required|string|max:255',
            'tickets.*.city' => 'required|string|max:255',
            'tickets.*.zip_code' => 'required|string|max:20',
            'tickets.*.province' => 'required|string|max:255',
            'tickets.*.region' => 'required|string|max:255',
            'tickets.*.country' => 'required|string|max:255',
            'tickets.*.nationality' => 'required|string|max:2',
            'tickets.*.email' => 'required|email',
            'tickets.*.phone' => 'required|string|max:20',
            'tickets.*.membership' => ['required', Rule::in(array_merge($memberships, ['giornaliera']))],
            'tickets.*.membership_number' => 'exclude_if:tickets.*.membership,giornaliera|required|string|max:50',
            'tickets.*.medical_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tickets.*.celiac' => 'required|boolean',
        ], [
            'truthful_declaration.accepted' => 'Devi dichiarare che i documenti caricati sono veritieri.',
            'tickets.*.membership_number.required' => 'Indica il numero di tessera, oppure scegli la tessera giornaliera.',
            'tickets.*.medical_certificate.required' => 'Carica il certificato medico di ogni partecipante.',
            'tickets.*.medical_certificate.mimes' => 'Il certificato medico deve essere un PDF o una foto (JPG, PNG).',
            'tickets.*.medical_certificate.max' => 'Il certificato medico non può superare i 5 MB.',
        ]);

        // 2. Scaglione di prezzo valido oggi (quello riservato solo con il codice giusto)
        $tier = Registration::tierFor($validatedData['access_code'] ?? null);

        if (! $tier) {
            $message = Registration::reservedTier()
                ? 'In questo periodo le iscrizioni sono riservate a chi ha partecipato alle edizioni precedenti: controlla il codice che hai ricevuto.'
                : 'Le iscrizioni online in questo momento sono chiuse.';

            return back()->withInput()->withErrors(['access_code' => $message]);
        }

        $dayCardPrice = (float) config('event.day_card_price');

        foreach ($validatedData['tickets'] as $i => $ticket) {
            $routePrice = $tier->priceFor($ticket['route_choice']);
            if ($routePrice === null) {
                return back()->withInput()->withErrors(['tickets' => 'Il percorso ' . $routes[$ticket['route_choice']]['name'] . ' non è disponibile in questo periodo.']);
            }

            $dayCard = $ticket['membership'] === 'giornaliera';
            $validatedData['tickets'][$i]['day_card'] = $dayCard;
            $validatedData['tickets'][$i]['membership'] = $dayCard ? null : $ticket['membership'];
            $validatedData['tickets'][$i]['membership_number'] = $dayCard ? null : $ticket['membership_number'];
            $validatedData['tickets'][$i]['price_paid'] = $routePrice + ($dayCard ? $dayCardPrice : 0);
        }
        $totalAmount = array_sum(array_column($validatedData['tickets'], 'price_paid'));

        if ($validatedData['payment_method'] === 'stripe' && (blank(config('services.stripe.secret')) || blank(config('services.stripe.key')))) {
            return back()
                ->withInput()
                ->withErrors(['payment_method' => 'Stripe non è configurato: controlla STRIPE_SECRET e STRIPE_KEY nel file .env oppure la configurazione Laravel in cache.']);
        }

        // 3. Creazione ordine e biglietti, controllando i posti rimasti dentro la transazione
        try {
            $order = DB::transaction(function () use ($request, $validatedData, $tier, $totalAmount, $routes) {
                $remaining = Registration::remainingByRoute();
                $requested = array_count_values(array_column($validatedData['tickets'], 'route_choice'));
                $totalLeft = (int) config('event.capacity') - array_sum(Registration::takenByRoute());

                foreach ($requested as $route => $count) {
                    if ($count > $remaining[$route]) {
                        throw new RuntimeException($remaining[$route] === 0
                            ? "Il percorso {$routes[$route]['name']} è al completo."
                            : "Sul percorso {$routes[$route]['name']} restano solo {$remaining[$route]} posti.");
                    }
                }
                if (count($validatedData['tickets']) > $totalLeft) {
                    throw new RuntimeException("Restano solo {$totalLeft} posti in totale.");
                }

                $order = Order::create([
                    'group_code' => 'WFU_' . strtoupper(\Illuminate\Support\Str::random(10)),
                    'total_amount' => $totalAmount,
                    'payment_method' => $validatedData['payment_method'],
                    'is_sports_association' => $request->boolean('is_sports_association'),
                    'association_name' => $validatedData['association_name'] ?? null,
                    'discount_code' => $tier->name,
                    'status' => 'pending',
                ]);

                foreach ($validatedData['tickets'] as $i => $data) {
                    $certificate = $request->file("tickets.$i.medical_certificate");
                    unset($data['medical_certificate']);

                    $order->tickets()->create(array_merge($data, [
                        'unique_ticket_code' => null,
                        'medical_certificate_path' => $certificate->store('certificates/' . $order->group_code, 'local'),
                        'payment_tag' => ucfirst($validatedData['payment_method']),
                        'status' => 'pending',
                    ]));
                }

                return $order;
            });
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['tickets' => $e->getMessage()]);
        }

        // 5. Redirect Pagamento
        if ($totalAmount == 0) {
            return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
        }

        try {
            return ($validatedData['payment_method'] === 'stripe')
                ? $this->processStripePayment($order)
                : $this->processPaypalPayment($order);
        } catch (Throwable $e) {
            $order->update(['status' => 'payment_error']);
            Log::error('Errore avvio pagamento', [
                'order_id' => $order->id,
                'group_code' => $order->group_code,
                'payment_method' => $order->payment_method,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['payment_method' => 'Non siamo riusciti ad avviare il pagamento. Controlla la configurazione del gateway e riprova.']);
        }
    }

    private function processStripePayment(Order $order)
    {
        $this->createOrRetrieveStripePaymentIntent($order);

        return redirect()->route('checkout.payment', ['order_code' => $order->group_code]);
    }

    public function payment($order_code)
    {
        $order = Order::with('tickets')->where('group_code', $order_code)->firstOrFail();

        if ($order->payment_method !== 'stripe') {
            return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Metodo di pagamento non valido per questo ordine.');
        }

        if ($order->status === 'paid') {
            return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
        }

        try {
            $paymentIntent = $this->createOrRetrieveStripePaymentIntent($order);

            if ($paymentIntent->status === 'succeeded') {
                app(OrderPaymentFinalizer::class)->finalize($order);

                return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
            }
        } catch (Throwable $e) {
            $order->update(['status' => 'payment_error']);
            Log::error('Errore creazione Payment Intent Stripe', [
                'order_id' => $order->id,
                'group_code' => $order->group_code,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('iscrizione')
                ->withErrors(['payment_method' => 'Non siamo riusciti ad avviare il pagamento con carta. Riprova tra poco.']);
        }

        return view('checkout.payment', [
            'order' => $order,
            'clientSecret' => $paymentIntent->client_secret,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    private function createOrRetrieveStripePaymentIntent(Order $order)
    {
        $stripeSecret = config('services.stripe.secret');

        if (blank($stripeSecret) || blank(config('services.stripe.key'))) {
            throw new RuntimeException('Stripe keys non configurate.');
        }

        \Stripe\Stripe::setApiKey($stripeSecret);

        if ($order->stripe_payment_intent_id) {
            try {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($order->stripe_payment_intent_id);

                if ($paymentIntent->status !== 'canceled') {
                    return $paymentIntent;
                }
            } catch (Throwable $e) {
                Log::warning('Payment Intent Stripe non recuperabile, ne creo uno nuovo', [
                    'order_id' => $order->id,
                    'group_code' => $order->group_code,
                    'stripe_payment_intent_id' => $order->stripe_payment_intent_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $order->loadMissing('tickets');
        $firstTicket = $order->tickets->first();

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => (int) round($order->total_amount * 100),
            'currency' => 'eur',
            'payment_method_types' => ['card'],
            'description' => 'Iscrizione ' . config('event.name') . ' - Ordine ' . $order->group_code,
            'receipt_email' => $firstTicket?->email,
            'metadata' => [
                'order_id' => (string) $order->id,
                'group_code' => $order->group_code,
            ],
        ]);

        $order->update(['stripe_payment_intent_id' => $paymentIntent->id]);

        return $paymentIntent;
    }

    private function processPaypalPayment(Order $order)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('checkout.success', ['order_code' => $order->group_code]),
                "cancel_url" => route('checkout.cancel', ['order' => $order->group_code]),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => $order->total_amount
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    $order->update(['paypal_order_id' => $response['id']]); 
                    return redirect()->away($links['href']);
                }
            }
        }

        return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Qualcosa è andato storto con PayPal.');
    }

    public function success(Request $request, $order_code)
    {
        $order = Order::with('tickets')->where('group_code', $order_code)->firstOrFail();

        // 1. Se l'ordine risulta già pagato, mostriamo solo la vista.
        if ($order->status === 'paid') {
            app(OrderPaymentFinalizer::class)->finalize($order);
            return view('checkout.success', compact('order'));
        }

        // --- 2. VERIFICA E CATTURA PAGAMENTO PAYPAL ---
        if ($order->payment_method === 'paypal' && $request->has('token')) {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            // QUESTA È LA RIGA MAGICA CHE PRELEVA EFFETTIVAMENTE I SOLDI
            $response = $provider->capturePaymentOrder($request->query('token'));
            
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                app(OrderPaymentFinalizer::class)->finalize($order);
            } else {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Il pagamento PayPal non è stato autorizzato o è stato annullato.');
            }
        }
        
        // --- 3. VERIFICA PAGAMENTO STRIPE ---
        elseif ($order->payment_method === 'stripe' && $order->stripe_payment_intent_id) {
            $stripeSecret = config('services.stripe.secret');

            if (blank($stripeSecret)) {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Stripe non è configurato correttamente.');
            }

            \Stripe\Stripe::setApiKey($stripeSecret);
            $paymentIntent = \Stripe\PaymentIntent::retrieve($order->stripe_payment_intent_id);
            
            if ($paymentIntent->status === 'succeeded') {
                app(OrderPaymentFinalizer::class)->finalize($order);
            } else {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Il pagamento Stripe non è stato completato.');
            }
        }
        elseif ($order->payment_method === 'stripe' && $order->stripe_session_id) {
            $stripeSecret = config('services.stripe.secret');

            if (blank($stripeSecret)) {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Stripe non è configurato correttamente.');
            }

            \Stripe\Stripe::setApiKey($stripeSecret);
            $session = \Stripe\Checkout\Session::retrieve($order->stripe_session_id);

            if ($session->payment_status == 'paid') {
                app(OrderPaymentFinalizer::class)->finalize($order);
            } else {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Il pagamento Stripe non è stato completato.');
            }
        }
        
        // --- 4. VERIFICA ORDINI A ZERO EURO (Promo Gruppi) ---
        elseif ($order->total_amount == 0) {
            app(OrderPaymentFinalizer::class)->finalize($order);
        }

        // --- 5. AZIONI POST-PAGAMENTO VERIFICATO ---
        // Se l'ordine ha passato i controlli ed è "paid", attiviamo i biglietti
        if ($order->status === 'paid') {
            app(OrderPaymentFinalizer::class)->finalize($order);
            return view('checkout.success', compact('order'));
        }

        // Fallback in caso di tentativi anomali
        return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Errore nella convalida del pagamento.');
    }

    public function downloadTicket($unique_code)
    {
        $ticket = Ticket::with('order')->where('unique_ticket_code', $unique_code)->firstOrFail();

        if ($ticket->order->status !== 'paid') {
            return back()->with('error', 'Il pagamento non è ancora stato confermato. Riprova tra poco.');
        }

        $pdf = Pdf::loadView('pdf.ticket', compact('ticket'));
        $fileName = 'Biglietto_ValdOrcia_Gravel_' . str_replace(' ', '_', $ticket->last_name) . '.pdf';
        
        return $pdf->download($fileName);
    }
}
