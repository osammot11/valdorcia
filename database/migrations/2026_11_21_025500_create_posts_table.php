<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->date('published_on');
            $table->boolean('published')->default(true);
            $table->string('image')->nullable();          // percorso pubblico, es. /images/blog/x.jpg o /uploads/blog/x.jpg
            $table->string('image_credit')->nullable();
            $table->text('body');                         // paragrafi separati da una riga vuota, "## " per i sottotitoli
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
