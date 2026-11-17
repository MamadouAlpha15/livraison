<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vue en direct (Boutique → En direct) : les visiteurs présents sur la vitrine en ce moment.
 * La vitrine envoie un signal toutes les 20 s avec un identifiant aléatoire du navigateur.
 * Aucune adresse IP n'est enregistrée : seulement le pays (code à 2 lettres, déduit de l'IP au moment du signal),
 * l'étape (regarde, panier, formulaire) et le produit regardé. Les lignes de plus d'un jour sont effacées chaque nuit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('visitor', 40);                       // identifiant aléatoire du navigateur
            $table->string('stage', 12)->default('browse');      // browse, product, cart, form
            $table->unsignedBigInteger('product_id')->nullable(); // produit regardé
            $table->unsignedSmallInteger('cart_items')->default(0);
            $table->char('country', 2)->nullable();              // pays seulement, jamais l'IP
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->unique(['shop_id', 'visitor']);
            $table->index(['shop_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_visitors');
    }
};
