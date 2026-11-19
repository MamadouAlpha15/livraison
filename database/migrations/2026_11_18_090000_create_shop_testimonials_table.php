<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Témoignages des vendeurs sur Shopio, affichés sur la page d'accueil (/) une fois validés
 * par l'équipe Shopio (Admin → Avis → Témoignages Shopio). Un témoignage par boutique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('text', 400);
            $table->string('author', 60);          // nom affiché, ex. « Kadiatou »
            $table->string('author_role', 60)->nullable(); // ex. « Fondatrice »
            $table->string('status', 12)->default('pending'); // pending | approved | rejected
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique('shop_id');
            $table->index(['status', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_testimonials');
    }
};
