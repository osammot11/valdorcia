<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// Certificato medico mancante: un promemoria ogni 10 giorni dall'iscrizione, fino al giorno dell'evento.
// Gira ogni giorno con lo scheduler (sul server: cron con php artisan schedule:run ogni minuto).
Artisan::command('certificates:remind', function () {
    $today = \App\Services\Registration::today();
    if ($today->gt(\Illuminate\Support\Carbon::parse(config('event.date_iso'), 'Europe/Rome'))) {
        $this->info("Evento passato: nessun promemoria.");

        return self::SUCCESS;
    }

    $due = now()->subDays(10);
    $tickets = \App\Models\Ticket::query()
        ->where('status', 'active')
        ->whereNotNull('unique_ticket_code')
        ->whereNotNull('email')
        ->where(fn ($q) => $q->whereNull('medical_certificate_path')->orWhere('medical_certificate_path', ''))
        ->whereHas('order', fn ($q) => $q->where('status', 'paid'))
        ->where(fn ($q) => $q
            ->where(fn ($q) => $q->whereNull('certificate_reminded_at')->where('created_at', '<=', $due))
            ->orWhere('certificate_reminded_at', '<=', $due))
        ->get();

    foreach ($tickets as $ticket) {
        // Segnato subito, così un secondo giro nello stesso giorno non lo rimanda
        $ticket->update(['certificate_reminded_at' => now()]);
        \App\Jobs\SendCertificateReminder::dispatch($ticket);
    }

    $this->info($tickets->count() . ' promemoria per il certificato medico in coda.');

    return self::SUCCESS;
})->purpose('Manda il promemoria a chi non ha ancora caricato il certificato medico');

\Illuminate\Support\Facades\Schedule::command('certificates:remind')->dailyAt('10:00')->timezone('Europe/Rome');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('local:import-tickets-sql {path : Percorso del dump tickets.sql} {--force : Conferma la cancellazione dei dati locali tickets/orders}', function (string $path) {
    if (app()->environment('production')) {
        $this->error('Import bloccato: APP_ENV=production. Imposta APP_ENV=local prima di usare questo comando.');
        return self::FAILURE;
    }

    if (! $this->option('force')) {
        $this->error('Questo comando svuota tickets e orders nel database corrente. Riesegui con --force se sei sul DB locale.');
        return self::FAILURE;
    }

    if (DB::connection()->getDriverName() !== 'mysql') {
        $this->error('Questo import e pensato per MySQL/MariaDB locale.');
        return self::FAILURE;
    }

    if (! File::isFile($path)) {
        $this->error("File non trovato: {$path}");
        return self::FAILURE;
    }

    $sql = File::get($path);

    preg_match_all('/INSERT INTO `tickets`[\s\S]*?;\s*(?=INSERT INTO `tickets`|--|ALTER TABLE|COMMIT|$)/', $sql, $matches);

    if (empty($matches[0])) {
        $this->error('Nessun INSERT INTO `tickets` trovato nel dump.');
        return self::FAILURE;
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    try {
        DB::table('tickets')->truncate();
        DB::table('orders')->truncate();

        foreach ($matches[0] as $insertStatement) {
            DB::unprepared($insertStatement);
        }

        $orderSummaries = DB::table('tickets')
            ->selectRaw('order_id, SUM(price_paid) as total_amount, MIN(payment_tag) as payment_tag, MIN(created_at) as created_at, MAX(updated_at) as updated_at')
            ->whereNotNull('order_id')
            ->groupBy('order_id')
            ->orderBy('order_id')
            ->get();

        foreach ($orderSummaries as $summary) {
            $paymentTag = strtolower((string) $summary->payment_tag);

            $paymentMethod = match (true) {
                str_contains($paymentTag, 'stripe') => 'stripe',
                str_contains($paymentTag, 'paypal') => 'paypal',
                str_contains($paymentTag, 'csv') || str_contains($paymentTag, 'import') => 'import_csv',
                default => 'import_sql',
            };

            DB::table('orders')->insert([
                'id' => $summary->order_id,
                'group_code' => 'SQL_' . str_pad((string) $summary->order_id, 6, '0', STR_PAD_LEFT),
                'total_amount' => $summary->total_amount ?? 0,
                'payment_method' => $paymentMethod,
                'is_sports_association' => false,
                'association_name' => null,
                'discount_code' => null,
                'status' => 'paid',
                'created_at' => $summary->created_at,
                'updated_at' => $summary->updated_at,
            ]);
        }

        $nextTicketId = ((int) DB::table('tickets')->max('id')) + 1;
        $nextOrderId = ((int) DB::table('orders')->max('id')) + 1;

        DB::statement("ALTER TABLE tickets AUTO_INCREMENT = {$nextTicketId}");
        DB::statement("ALTER TABLE orders AUTO_INCREMENT = {$nextOrderId}");
    } finally {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    $this->info('Import completato.');
    $this->line('Tickets importati: ' . DB::table('tickets')->count());
    $this->line('Orders ricostruiti: ' . DB::table('orders')->count());

    return self::SUCCESS;
})->purpose('Importa un dump tickets.sql nel database locale e ricostruisce ordini placeholder');
