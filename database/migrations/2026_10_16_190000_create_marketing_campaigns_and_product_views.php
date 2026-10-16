<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Page Marketing du vendeur :
 *  - marketing_campaigns : liens de campagne nommés (« Influenceuse Awa », « Promo Tabaski »),
 *    éventuellement liés à un code promo ; visites et commandes suivies à part ;
 *  - shop_traffic.campaign_id / orders.campaign_id : campagne d'arrivée (0 / null = aucune) ;
 *  - product_views : vues de chaque fiche produit par jour (statistiques par produit).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('slug', 40);
            $table->string('channel', 20);
            $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['shop_id', 'slug']);
        });

        // campaign_id à 0 plutôt que NULL : la clé unique doit fonctionner avec upsert()
        Schema::table('shop_traffic', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id')->default(0)->after('source');
            $table->dropUnique(['shop_id', 'day', 'source']);
            $table->unique(['shop_id', 'day', 'source', 'campaign_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id')->nullable()->after('source');
            $table->index('campaign_id');
        });

        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('shop_id')->index();
            $table->date('day');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'day']);
            $table->index(['shop_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_views');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['campaign_id']);
            $table->dropColumn('campaign_id');
        });
        Schema::table('shop_traffic', function (Blueprint $table) {
            $table->index('shop_id'); // garde un index pour la clé étrangère pendant l'échange
            $table->dropUnique(['shop_id', 'day', 'source', 'campaign_id']);
            $table->dropColumn('campaign_id');
        });
        Schema::table('shop_traffic', function (Blueprint $table) {
            $table->unique(['shop_id', 'day', 'source']);
        });
        Schema::dropIfExists('marketing_campaigns');
    }
};
