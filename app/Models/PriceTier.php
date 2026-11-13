<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceTier extends Model
{
    protected $fillable = ['name', 'starts_on', 'ends_on', 'access_code', 'prices'];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'prices' => 'array',
    ];

    public function priceFor(string $route): ?float
    {
        $price = $this->prices[$route] ?? null;

        return $price === null || $price === '' ? null : (float) $price;
    }

    public function isReserved(): bool
    {
        return filled($this->access_code);
    }
}
