<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Chiave in tabella settings del prezzo di un percorso, es. "price.Gravel 39K". */
    public static function routePriceKey(string $route): string
    {
        return 'price.' . $route;
    }

    /**
     * Prezzo di ogni percorso, nell'ordine di config/event.php.
     * Se un percorso non ha un prezzo salvato si usa il vecchio prezzo unico
     * (chiave "ticket_price") e infine il prezzo di riserva della config.
     *
     * @return array<string, float>
     */
    public static function routePrices(): array
    {
        $routes = array_keys(config('event.routes'));
        $fallback = (float) config('event.default_price');

        try {
            $stored = static::whereIn('key', array_merge(
                ['ticket_price'],
                array_map([static::class, 'routePriceKey'], $routes)
            ))->pluck('value', 'key');
        } catch (Throwable $e) {
            $stored = collect();
        }

        $fallback = (float) ($stored['ticket_price'] ?? $fallback);

        $prices = [];
        foreach ($routes as $route) {
            $prices[$route] = (float) ($stored[static::routePriceKey($route)] ?? $fallback);
        }

        return $prices;
    }

    public static function routePrice(string $route): float
    {
        return static::routePrices()[$route] ?? (float) config('event.default_price');
    }
}
