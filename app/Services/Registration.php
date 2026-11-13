<?php

namespace App\Services;

use App\Models\PriceTier;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Regole delle iscrizioni online: scaglione di prezzo valido oggi e posti rimasti.
 */
class Registration
{
    /** Un ordine non pagato tiene il posto per questo numero di minuti. */
    public const HOLD_MINUTES = 30;

    public static function today(): Carbon
    {
        return now('Europe/Rome')->startOfDay();
    }

    /** @return Collection<int, PriceTier> */
    public static function tiersActiveToday(): Collection
    {
        $today = static::today()->toDateString();

        return PriceTier::whereDate('starts_on', '<=', $today)
            ->whereDate('ends_on', '>=', $today)
            ->orderBy('starts_on')
            ->get();
    }

    /** Scaglione aperto a tutti valido oggi. */
    public static function publicTier(): ?PriceTier
    {
        return static::tiersActiveToday()->first(fn (PriceTier $t) => ! $t->isReserved());
    }

    /** Scaglione riservato (es. ex iscritti) valido oggi, se esiste. */
    public static function reservedTier(): ?PriceTier
    {
        return static::tiersActiveToday()->first(fn (PriceTier $t) => $t->isReserved());
    }

    /**
     * Scaglione da applicare: quello riservato se il codice è giusto, altrimenti quello pubblico.
     */
    public static function tierFor(?string $code): ?PriceTier
    {
        $reserved = static::reservedTier();
        if ($reserved && filled($code) && strcasecmp(trim($code), $reserved->access_code) === 0) {
            return $reserved;
        }

        return static::publicTier();
    }

    public static function isOpen(): bool
    {
        return static::tiersActiveToday()->isNotEmpty();
    }

    /** Primo scaglione pubblico futuro: quando riaprono le iscrizioni per tutti. */
    public static function nextPublicTier(): ?PriceTier
    {
        return PriceTier::whereNull('access_code')
            ->whereDate('starts_on', '>', static::today()->toDateString())
            ->orderBy('starts_on')
            ->first();
    }

    /** Posti occupati per percorso: biglietti pagati, in approvazione o in pagamento da poco. */
    public static function takenByRoute(): array
    {
        $counts = Ticket::query()
            ->where(function ($q) {
                $q->whereIn('status', ['active', 'in-approvazione'])
                  ->orWhere(fn ($q) => $q->where('status', 'pending')->where('created_at', '>=', now()->subMinutes(static::HOLD_MINUTES)));
            })
            ->selectRaw('route_choice, COUNT(*) as total')
            ->groupBy('route_choice')
            ->pluck('total', 'route_choice')
            ->all();

        $taken = [];
        foreach (array_keys(config('event.routes')) as $route) {
            $taken[$route] = (int) ($counts[$route] ?? 0);
        }

        return $taken;
    }

    /** Posti ancora liberi per percorso, tenendo conto sia del limite del percorso sia di quello totale. */
    public static function remainingByRoute(): array
    {
        $taken = static::takenByRoute();
        $totalLeft = (int) config('event.capacity') - array_sum($taken);

        $remaining = [];
        foreach (config('event.routes') as $route => $data) {
            $routeLeft = $data['limit'] === null ? PHP_INT_MAX : $data['limit'] - $taken[$route];
            $remaining[$route] = max(0, min($routeLeft, $totalLeft));
        }

        return $remaining;
    }

    /**
     * Riconosce il percorso da un testo libero (import CSV): chiave, nome, km o vecchi valori "Gravel 73K".
     */
    public static function routeFromLabel(?string $label): ?string
    {
        $needle = mb_strtolower(trim((string) $label));
        if ($needle === '') {
            return null;
        }

        foreach (config('event.routes') as $key => $route) {
            $candidates = [$key, $route['name'], $route['short_name'] ?? $route['name'], (string) $route['km'], $route['km'] . ' km', 'gravel ' . $route['km'] . 'k'];
            if (in_array($needle, array_map('mb_strtolower', $candidates), true)) {
                return $key;
            }
        }

        // Vecchio Frugale da 39 km
        return in_array($needle, ['39', '39 km', 'gravel 39k'], true) ? 'frugale' : null;
    }
}
