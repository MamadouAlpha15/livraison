<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campagnes publicitaires payées par les boutiques (espace vendeur → Campagnes) :
 *  - ad_packages : formules et prix, modifiables par l'équipe Shopio ;
 *  - ad_campaigns : campagne commandée par une boutique, prix figé au moment de la commande ;
 *  - ad_campaign_items : produits promus (product_id null = toute la boutique) ;
 *  - ad_campaign_payments : paiements et remboursements ;
 *  - ad_campaign_creatives : visuels des campagnes réseaux sociaux, validés par le vendeur ;
 *  - ad_campaign_stats_daily : statistiques par jour (auto pour la mise en avant, saisies par l'admin pour les réseaux) ;
 *  - shop_credit_entries : « Crédit Shopio » de la boutique (remboursements, paiements de campagne) ;
 *  - orders.ad_campaign_id : campagne qui a amené la commande.
 * Préfixe ad_ : les « campagnes » de la page Marketing (liens de suivi) existent déjà
 * dans marketing_campaigns et orders.campaign_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_packages', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);                       // boost | social
            $table->string('code', 30)->unique();
            $table->string('name', 60);
            $table->string('placement', 20);                  // category_search | category_top | home_featured | social
            $table->unsignedSmallInteger('duration_days');    // durée de référence (prix affiché)
            $table->json('prices');                           // {"3": 25000, "7": 50000, "30": 150000} en GNF
            $table->unsignedBigInteger('ad_budget_gnf')->default(0); // part reversée à Meta / TikTok (social)
            $table->unsignedBigInteger('fee_gnf')->default(0);       // frais Shopio (social)
            $table->unsignedTinyInteger('max_items')->default(0);    // produits promus au maximum (0 = aucun)
            $table->boolean('allows_shop_wide')->default(false);     // peut promouvoir toute la boutique
            $table->unsignedSmallInteger('max_actives')->nullable(); // places simultanées (null = pas de limite)
            $table->string('capacity_scope', 10)->default('global'); // global | category
            $table->json('details')->nullable();              // réseaux, visuels, vidéos, ciblage…
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ad_package_id')->constrained('ad_packages')->restrictOnDelete();
            $table->string('type', 10);                       // boost | social (copié de la formule)
            $table->string('objective', 20)->nullable();      // messages | visites | notoriete (social)
            $table->json('targeting')->nullable();            // {"communes":[…],"age_min":18,"age_max":45,"gender":"tous"}
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedSmallInteger('duration_days');
            $table->string('status', 20)->default('a_payer');
            $table->unsignedBigInteger('price_gnf');          // prix total figé à la commande
            $table->unsignedBigInteger('ad_budget_gnf')->default(0);
            $table->unsignedBigInteger('fee_gnf')->default(0);
            $table->string('pause_reason', 20)->nullable();   // rupture | admin | vendeur
            $table->text('refusal_reason')->nullable();
            $table->string('external_ref', 100)->nullable();  // identifiant de la pub Meta / TikTok
            $table->unsignedTinyInteger('revision_count')->default(0); // modifications de visuels demandées
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('creatives_sent_at')->nullable();
            $table->timestamp('creatives_reminded_at')->nullable();
            $table->timestamp('launched_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('ending_soon_notified_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refused_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['shop_id', 'status']);
            $table->index(['status', 'starts_on']);
        });

        Schema::create('ad_campaign_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); // null = toute la boutique
            $table->timestamps();
            $table->index('product_id');
        });

        Schema::create('ad_campaign_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->string('method', 20);                     // mobile_money | credit | manuel
            $table->string('operator', 20)->nullable();       // orange_money | mtn_momo, si l'agrégateur le renvoie
            $table->unsignedBigInteger('amount_gnf');
            $table->string('status', 20)->default('en_attente'); // en_attente | paye | echoue | rembourse
            $table->string('gateway_operation_id', 100)->nullable()->index();
            $table->string('transaction_ref', 100)->nullable();
            $table->unsignedBigInteger('refund_amount_gnf')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete(); // paiement manuel
            $table->timestamps();
        });

        Schema::create('ad_campaign_creatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('type', 10);                       // image | video
            $table->text('caption')->nullable();
            $table->string('status', 25)->default('en_attente'); // en_attente | validee | modification_demandee
            $table->text('seller_comment')->nullable();
            $table->unsignedTinyInteger('round')->default(1); // aller-retour (1 à 3)
            $table->timestamps();
        });

        Schema::create('ad_campaign_stats_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->date('day');
            $table->string('source', 10)->default('auto');    // auto | admin
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('messages')->default(0);
            $table->unsignedInteger('orders')->default(0);
            $table->unsignedBigInteger('revenue_gnf')->default(0);
            $table->unsignedBigInteger('spend_gnf')->default(0);
            $table->timestamps();
            $table->unique(['ad_campaign_id', 'day', 'source']);
        });

        Schema::create('shop_credit_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount_gnf');                 // + crédit (remboursement), − débit (paiement)
            $table->string('reason', 30);                     // remboursement | paiement_campagne | ajustement
            $table->foreignId('ad_campaign_id')->nullable()->constrained('ad_campaigns')->nullOnDelete();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['shop_id', 'created_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('ad_campaign_id')->nullable()->after('campaign_id')
                ->constrained('ad_campaigns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ad_campaign_id');
        });
        Schema::dropIfExists('shop_credit_entries');
        Schema::dropIfExists('ad_campaign_stats_daily');
        Schema::dropIfExists('ad_campaign_creatives');
        Schema::dropIfExists('ad_campaign_payments');
        Schema::dropIfExists('ad_campaign_items');
        Schema::dropIfExists('ad_campaigns');
        Schema::dropIfExists('ad_packages');
    }
};
