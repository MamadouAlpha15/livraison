<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contenu de la page produit longue (modèle « Express ») : accroche, avantages, packs,
 * sections « Pour qui ? », « Pourquoi vous allez l'adorer », comparatif, questions…
 * Rempli par le vendeur ou par Shopio IA. Format et contrôles : App\Support\ProductPage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('landing')->nullable()->after('gallery');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('landing');
        });
    }
};
