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
        // Chaque étape est protégée (déjà fait ? déjà présent ?) : cette migration
        // a été interrompue en plein milieu lors d'une première tentative (table
        // déjà créée, puis échec plus loin) — ces vérifications lui permettent de
        // reprendre proprement là où elle s'était arrêtée, au lieu de replanter sur
        // "la table existe déjà".
        if (!Schema::hasTable('marketing_campaigns')) {
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
        }

        // campaign_id à 0 plutôt que NULL : la clé unique doit fonctionner avec upsert()
        if (!Schema::hasColumn('shop_traffic', 'campaign_id')) {
            Schema::table('shop_traffic', function (Blueprint $table) {
                $table->unsignedBigInteger('campaign_id')->default(0)->after('source');
            });
        }
        try {
            Schema::table('shop_traffic', function (Blueprint $table) {
                $table->dropUnique(['shop_id', 'day', 'source']);
            });
        } catch (\Throwable $e) {
            // déjà supprimé lors d'une tentative précédente
        }
        try {
            Schema::table('shop_traffic', function (Blueprint $table) {
                $table->unique(['shop_id', 'day', 'source', 'campaign_id']);
            });
        } catch (\Throwable $e) {
            // déjà créé lors d'une tentative précédente
        }

        if (!Schema::hasColumn('orders', 'campaign_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('campaign_id')->nullable()->after('source');
                $table->index('campaign_id');
            });
        }

        if (!Schema::hasTable('product_views')) {
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
