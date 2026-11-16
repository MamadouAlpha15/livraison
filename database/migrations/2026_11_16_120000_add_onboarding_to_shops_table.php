<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réponses du parcours de création de boutique (expérience, ventes, équipe, livraison),
 * pour adapter l'accompagnement du vendeur. Voir App\Http\Controllers\Vendeur\ShopOnboardingController.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->json('onboarding')->nullable()->after('storefront');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('onboarding');
        });
    }
};
