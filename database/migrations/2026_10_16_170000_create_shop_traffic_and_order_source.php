<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Provenance des visites et des commandes d'une boutique (Facebook, Instagram, TikTok,
 * WhatsApp, QR code, Google, direct…). Voir App\Support\TrafficSource.
 *  - shop_traffic : visites par jour et par source (une visite = une personne qui arrive
 *    sur la vitrine ou une fiche produit, comptée une fois par jour et par boutique) ;
 *  - orders.source : source attribuée à la commande (dernier canal d'arrivée, 7 jours).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_traffic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->string('source', 20);
            $table->unsignedInteger('visits')->default(0);
            $table->timestamps();
            $table->unique(['shop_id', 'day', 'source']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->after('status');
            $table->index(['shop_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'source']);
            $table->dropColumn('source');
        });
        Schema::dropIfExists('shop_traffic');
    }
};
