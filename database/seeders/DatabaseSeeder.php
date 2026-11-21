<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Carica i comunicati del blog e crea (o aggiorna) l'utente dell'area admin.
     * Le credenziali stanno solo in .env: ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD.
     */
    public function run(): void
    {
        $this->call(BlogPostSeeder::class);

        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL o ADMIN_PASSWORD mancanti in .env: nessun utente admin creato.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => env('ADMIN_NAME', 'Admin'), 'password' => $password],
        );

        $this->command?->info("Utente admin pronto: {$email}");
    }
}
