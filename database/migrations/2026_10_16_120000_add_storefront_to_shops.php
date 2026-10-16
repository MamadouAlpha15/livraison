<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réglages de la vitrine publique (sections, affichage des produits, bannière, annonce).
 * Format et valeurs autorisées : App\Support\Storefront.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->json('storefront')->nullable()->after('accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('storefront');
        });
    }
};
