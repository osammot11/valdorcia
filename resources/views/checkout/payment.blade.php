<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento | {{ config('event.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="apple-touch-icon" href="/images/valdorcia-gravel/logo-valdorcia-gravel.png">
    <link rel="stylesheet" href="/css/controls.css?v=1">
    <link rel="stylesheet" href="/css/checkout.css?v=4">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="center-items checkout-body">

    <div class="checkout-wrapper checkout-narrow stack-large">
        <div class="stack-mid center-text">
            <p class="checkout-kicker">Ordine {{ $order->group_code }}</p>
            <h1>Pagamento iscrizione</h1>
            <p>Completa il pagamento con carta senza uscire dal sito.</p>
        </div>

        <div class="checkout-summary stack-mid">
            <div class="checkout-summary-row">
                <span>Partecipanti</span>
                <strong>{{ $order->tickets->count() }}</strong>
            </div>
            <div class="checkout-summary-row">
                <span>Metodo</span>
                <strong>Carta</strong>
            </div>
            <div class="checkout-summary-row checkout-summary-total">
                <span>Totale</span>
                <strong>{{ number_format($order->total_amount, 2, ',', '.') }}€</strong>
            </div>
        </div>

        <form id="payment-form" class="stack-large">
            <div class="stripe-payment-box">
                <div id="payment-element"></div>
            </div>

            <div id="payment-message" class="payment-message hidden"></div>

            <button class="btn fullwidth" id="submit" type="submit">
                <span id="button-text">Paga {{ number_format($order->total_amount, 2, ',', '.') }}€</span>
                <span id="spinner" class="hidden">Pagamento in corso...</span>
            </button>
        </form>

        <div class="center-text">
            <a href="{{ route('iscrizione') }}" class="hyperlink">Torna al modulo iscrizione</a>
        </div>
    </div>

    <script>
        const stripe = Stripe(@json($stripeKey));
        const elements = stripe.elements({
            clientSecret: @json($clientSecret),
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#a9501f',
                    colorBackground: '#ffffff',
                    colorText: '#221e18',
                    colorDanger: '#9e2a1d',
                    fontFamily: 'Archivo, system-ui, sans-serif',
                    borderRadius: '0px',
                    spacingUnit: '5px',
                },
                rules: {
                    '.Input': {
                        border: '1px solid #d5c8b0',
                        boxShadow: 'none',
                        fontSize: '16px',
                    },
                    '.Input:focus': {
                        border: '1px solid #a9501f',
                        boxShadow: '0 0 0 1px #a9501f',
                    },
                    '.Label': {
                        fontSize: '14px',
                        color: '#221e18',
                    },
                },
            },
        });

        const paymentElement = elements.create('payment', {
            layout: 'tabs',
            fields: {
                billingDetails: {
                    name: 'auto',
                    email: 'auto',
                },
            },
        });

        paymentElement.mount('#payment-element');

        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');
        const message = document.getElementById('payment-message');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            setLoading(true);
            setMessage('');

            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: @json(route('checkout.success', ['order_code' => $order->group_code])),
                    payment_method_data: {
                        billing_details: {
                            name: @json(trim(($order->tickets->first()?->first_name ?? '') . ' ' . ($order->tickets->first()?->last_name ?? ''))),
                            email: @json($order->tickets->first()?->email),
                        },
                    },
                },
                redirect: 'if_required',
            });

            if (error) {
                setMessage(error.message || 'Pagamento non riuscito. Controlla i dati della carta e riprova.');
                setLoading(false);
                return;
            }

            if (paymentIntent && paymentIntent.status === 'succeeded') {
                window.location.href = @json(route('checkout.success', ['order_code' => $order->group_code]));
                return;
            }

            setMessage('Pagamento in elaborazione. Attendi qualche secondo e controlla la conferma.');
            setLoading(false);
        });

        function setLoading(isLoading) {
            submitButton.disabled = isLoading;
            buttonText.classList.toggle('hidden', isLoading);
            spinner.classList.toggle('hidden', !isLoading);
        }

        function setMessage(text) {
            message.textContent = text;
            message.classList.toggle('hidden', !text);
        }
    </script>
</body>
</html>
