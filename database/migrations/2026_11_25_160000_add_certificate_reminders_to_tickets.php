<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il certificato medico si può caricare anche dopo l'iscrizione: finché manca, ogni 10 giorni parte un promemoria.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('certificate_reminded_at')->nullable()->after('medical_certificate_path');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('certificate_reminded_at');
        });
    }
};
