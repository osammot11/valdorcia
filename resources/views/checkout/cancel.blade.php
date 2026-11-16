@extends('layouts.app')

@section('title', 'Pagamento non completato | ' . config('event.name'))

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <main class="confirm">
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Pagamento non completato</span>
                <h1>L'iscrizione non è ancora valida.</h1>
                <p class="lead">Il pagamento è stato annullato o non è andato a buon fine, quindi non ti è stato addebitato nulla e i biglietti non sono stati emessi.</p>
            </div>
        </section>

        <section class="confirm__body">
            <div class="inner">
                @if (session('error'))
                    <p class="confirm__alert" role="alert">{{ session('error') }}</p>
                @endif

                @if ($order)
                    <dl class="confirm__order">
                        <div>
                            <dt>Codice ordine</dt>
                            <dd class="mono">{{ $order->group_code }}</dd>
                        </div>
                        <div>
                            <dt>{{ $order->tickets->count() > 1 ? 'Partecipanti' : 'Partecipante' }}</dt>
                            <dd>{{ $order->tickets->count() }}</dd>
                        </div>
                        <div>
                            <dt>Totale</dt>
                            <dd>{{ number_format((float) $order->total_amount, 2, ',', '.') }} €</dd>
                        </div>
                    </dl>
                @endif

                <p class="confirm__note">
                    @if ($canRetry)
                        I posti restano tenuti per te ancora per qualche minuto: puoi riprovare subito con la carta. Dopo bisogna rifare l'iscrizione.
                    @else
                        Per iscriverti rifai l'iscrizione dal modulo. Se pensi che il pagamento sia andato a buon fine scrivici a <a class="hyperlink" href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>{{ $order ? ' indicando il codice ordine' : '' }}.
                    @endif
                </p>

                <div class="confirm__actions">
                    @if ($canRetry)
                        <a href="{{ route('checkout.payment', ['order_code' => $order->group_code]) }}" class="btn">Riprova il pagamento</a>
                        <a href="{{ route('iscrizione') }}" class="hyperlink">Rifai l'iscrizione</a>
                    @else
                        <a href="{{ route('iscrizione') }}" class="btn">Torna all'iscrizione</a>
                        <a href="/" class="hyperlink">Torna alla home</a>
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection
