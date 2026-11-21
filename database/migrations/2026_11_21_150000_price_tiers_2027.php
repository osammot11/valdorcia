<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Scaglioni 2027 comunicati da Federico Ricci (mail del 3/11/2026): iscrizioni online dal
 * 10 dicembre 2026, uguali per tutti (niente più promo ex iscritti con codice).
 */
return new class extends Migration
{
    public function up(): void
    {
        $prices = fn (float $base, float $luculliano) => json_encode([
            'frugale' => $base, 'saporito' => $base, 'goloso' => $base, 'luculliano' => $luculliano,
        ]);
        $now = now();

        DB::table('price_tiers')->delete();
        DB::table('price_tiers')->insert([
            ['name' => 'Prezzo agevolato', 'starts_on' => '2026-12-10', 'ends_on' => '2027-01-30', 'access_code' => null, 'prices' => $prices(25, 70), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Secondo scaglione', 'starts_on' => '2027-02-01', 'ends_on' => '2027-06-30', 'access_code' => null, 'prices' => $prices(35, 80), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Terzo scaglione', 'starts_on' => '2027-07-01', 'ends_on' => '2027-09-30', 'access_code' => null, 'prices' => $prices(45, 85), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ultimi giorni', 'starts_on' => '2027-10-01', 'ends_on' => '2027-10-14', 'access_code' => null, 'prices' => $prices(50, 90), 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        // Gli scaglioni precedenti erano provvisori: non si ripristinano.
    }
};
