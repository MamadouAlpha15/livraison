<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * « Faire grandir ma vitrine » :
 *  - brouillon de la vitrine et publication programmée (onglet Personnalisation) ;
 *  - lien d'aperçu partageable, valable 48 h, et avis reçus sur cet aperçu (onglet Qualité) ;
 *  - clics par section de la vitrine (onglet Qualité, « clics par section »).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->json('storefront_draft')->nullable()->after('storefront_history');
            $table->timestamp('storefront_publish_at')->nullable()->after('storefront_draft');
            $table->string('preview_token', 32)->nullable()->unique()->after('storefront_publish_at');
            $table->timestamp('preview_expires_at')->nullable()->after('preview_token');
            $table->json('preview_feedback')->nullable()->after('preview_expires_at');
        });

        // Lien de campagne (onglet Partage) : page visée, une catégorie ou toute la boutique
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->string('landing', 120)->nullable()->after('channel');
        });

        Schema::create('storefront_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->string('block', 20);
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamps();
            $table->unique(['shop_id', 'day', 'block']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_clicks');
        Schema::table('marketing_campaigns', fn (Blueprint $table) => $table->dropColumn('landing'));
        Schema::table('shops', function (Blueprint $table) {
            $table->dropUnique(['preview_token']);
            $table->dropColumn(['storefront_draft', 'storefront_publish_at', 'preview_token', 'preview_expires_at', 'preview_feedback']);
        });
    }
};
