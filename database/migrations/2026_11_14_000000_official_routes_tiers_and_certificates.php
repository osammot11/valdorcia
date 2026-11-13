<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Allinea il ticketing al regolamento ufficiale:
 * - scaglioni di prezzo per data (con scaglione riservato a chi ha un codice)
 * - tessera sportiva / tessera giornaliera e certificato medico per ogni biglietto
 * - percorsi ufficiali (Frugale, Saporito, Goloso, Luculliano)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('access_code')->nullable(); // se valorizzato lo scaglione vale solo con questo codice
            $table->json('prices');                     // { "frugale": 30, "saporito": 30, ... }
            $table->timestamps();
        });

        $regular = fn (float $base, float $luculliano) => json_encode([
            'frugale' => $base, 'saporito' => $base, 'goloso' => $base, 'luculliano' => $luculliano,
        ]);
        $now = now();

        // Stessi importi e stesse finestre del 2026, spostati di un anno. DA CONFERMARE.
        DB::table('price_tiers')->insert([
            ['name' => 'Promo ex iscritti', 'starts_on' => '2026-11-03', 'ends_on' => '2026-12-31', 'access_code' => 'BENTORNATO', 'prices' => $regular(25, 60), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Primo livello', 'starts_on' => '2027-01-02', 'ends_on' => '2027-02-28', 'access_code' => null, 'prices' => $regular(30, 70), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Secondo livello', 'starts_on' => '2027-03-01', 'ends_on' => '2027-06-30', 'access_code' => null, 'prices' => $regular(35, 75), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Terzo livello', 'starts_on' => '2027-07-01', 'ends_on' => '2027-09-30', 'access_code' => null, 'prices' => $regular(40, 80), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Quarto livello', 'starts_on' => '2027-10-01', 'ends_on' => '2027-10-14', 'access_code' => null, 'prices' => $regular(45, 85), 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('membership')->nullable()->after('phone');        // ente di tesseramento
            $table->string('membership_number')->nullable()->after('membership');
            $table->boolean('day_card')->default(false)->after('membership_number'); // tessera giornaliera da 10 €
            $table->string('medical_certificate_path')->nullable()->after('day_card');
            $table->string('tshirt_size')->nullable()->change();             // non più richiesta
        });

        // Vecchi valori dei percorsi -> chiavi ufficiali
        $map = ['Gravel 39K' => 'frugale', 'Gravel 73K' => 'saporito', 'Gravel 118K' => 'goloso', 'Gravel 220K' => 'luculliano'];
        foreach ($map as $old => $new) {
            DB::table('tickets')->where('route_choice', $old)->update(['route_choice' => $new]);
        }

        // I prezzi fissi per percorso sono sostituiti dagli scaglioni
        DB::table('settings')->where('key', 'like', 'price.%')->orWhere('key', 'ticket_price')->delete();
    }

    public function down(): void
    {
        Schema::dropIfExists('price_tiers');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['membership', 'membership_number', 'day_card', 'medical_certificate_path']);
        });
    }
};
