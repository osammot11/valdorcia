{{-- Tutti gli scaglioni pubblici (dal database, modificabili dall'admin) più l'iscrizione in loco --}}
@php
    $routeNames = collect(config('event.routes'))->map(fn ($r) => $r['short_name'] ?? $r['name']);
    $onsiteRoutes = $routeNames->except('luculliano')->implode(', ');
    $fmt = fn ($v) => number_format((float) $v, 0, ',', '.') . ' €';
@endphp
<ul class="price-list tier-list">
    @foreach ($tiers as $tier)
        @php
            // Percorsi con lo stesso prezzo raggruppati: "Frugale, Saporito, Goloso 25 € · Luculliano 70 €"
            $groups = $routeNames->keys()
                ->filter(fn ($k) => $tier->priceFor($k) !== null)
                ->groupBy(fn ($k) => (string) $tier->priceFor($k))
                ->map(fn ($keys, $price) => $keys->map(fn ($k) => $routeNames[$k])->implode(', ') . ' ' . $fmt($price));
        @endphp
        <li>
            <span>Dal {{ $tier->starts_on->format('d/m/Y') }} al {{ $tier->ends_on->format('d/m/Y') }}</span>
            <strong>{{ $groups->implode(' · ') }}</strong>
        </li>
    @endforeach
    <li>
        <span>{{ config('event.saturday_label') }} {{ config('event.edition_year') }}, in loco</span>
        <strong>{{ $onsiteRoutes }} {{ $fmt(config('event.onsite_price')) }}</strong>
    </li>
</ul>
