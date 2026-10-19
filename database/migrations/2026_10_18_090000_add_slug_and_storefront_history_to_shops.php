<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Éditeur de boutique :
 *  - shops.slug : adresse courte de la boutique (/s/{slug}), choisie dans Réglages ;
 *  - shops.storefront_history : dernières publications de la vitrine (restaurables).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('slug', 40)->nullable()->unique()->after('name');
            $table->json('storefront_history')->nullable()->after('storefront');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'storefront_history']);
        });
    }
};
