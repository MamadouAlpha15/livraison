<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Programme d'affiliation des boutiques : affiliés (vendeurs ou externes), clics sur les liens ?ref=,
 * boutiques parrainées, commissions sur les abonnements payés, retraits, récompenses du parrain
 * et réglages du programme (une seule ligne). Logique : App\Services\AffiliateService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            // Vide pour un affilié externe sans compte Shopio
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('type', 20);                      // vendor | external
            $table->string('code', 20)->unique();
            // Coordonnées d'un affilié externe (un vendeur a celles de son compte)
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->decimal('commission_rate', 5, 2);        // en %
            $table->string('status', 20)->default('active'); // active | suspended
            $table->string('payout_method', 30)->nullable();
            $table->string('payout_phone', 30)->nullable();
            // Dernière IP (hachée) et appareil connus du parrain, pour repérer l'auto-parrainage
            $table->string('last_ip_hash', 64)->nullable();
            $table->string('device_hash', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('referral_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->string('ip_hash', 64);                   // jamais l'IP en clair
            $table->string('user_agent')->nullable();
            $table->string('landing_url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['affiliate_id', 'created_at']);
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            // Une boutique n'a qu'un seul parrain
            $table->foreignId('referred_shop_id')->unique()->constrained('shops')->cascadeOnDelete();
            $table->string('status', 20)->default('registered'); // registered | active | paying | cancelled
            $table->string('source', 40)->nullable();             // « Comment as-tu connu Shopio ? »
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('first_paid_at')->nullable();       // début de la durée des commissions
            $table->boolean('flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('device_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['affiliate_id', 'status']);
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');                // GNF
            $table->string('method', 30);
            $table->string('phone', 30);
            $table->string('status', 20)->default('requested'); // requested | paid | rejected
            $table->string('reference', 100)->nullable();
            $table->string('note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['affiliate_id', 'status']);
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_id')->constrained()->cascadeOnDelete();
            // Un paiement d'abonnement ne donne qu'une commission
            $table->foreignId('subscription_payment_id')->unique()->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('payout_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('amount');                // GNF, sans décimales
            $table->string('status', 20)->default('pending'); // pending | approved | paid | cancelled
            $table->timestamp('available_at');
            $table->timestamps();
            $table->index(['affiliate_id', 'status']);
            $table->index(['status', 'available_at']);
        });

        // Mois offerts au parrain vendeur (prolongation ou Crédit Shopio), comptés pour le plafond annuel
        Schema::create('affiliate_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);                       // extension | credit
            $table->unsignedTinyInteger('months');
            $table->unsignedInteger('amount_gnf')->default(0); // valeur du crédit
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_credit_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['affiliate_id', 'created_at']);
        });

        Schema::create('affiliate_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('default_rate', 5, 2);
            $table->unsignedSmallInteger('commission_months');
            $table->unsignedSmallInteger('validation_days');
            $table->unsignedInteger('payout_threshold');
            $table->unsignedTinyInteger('reward_months');
            $table->unsignedSmallInteger('cookie_days');
            $table->unsignedTinyInteger('annual_reward_cap');   // mois offerts au maximum par année civile
            $table->unsignedSmallInteger('active_min_products'); // « Active » : produits en ligne…
            $table->unsignedSmallInteger('active_min_orders');   // … et commandes reçues
            $table->timestamps();
        });

        // Valeurs de départ, modifiables par l'admin (Admin → Affiliation → Réglages)
        DB::table('affiliate_settings')->insert([
            'default_rate'        => 20,
            'commission_months'   => 12,
            'validation_days'     => 30,
            'payout_threshold'    => 50000,
            'reward_months'       => 1,
            'cookie_days'         => 30,
            'annual_reward_cap'   => 6,
            'active_min_products' => 3,
            'active_min_orders'   => 1,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_settings');
        Schema::dropIfExists('affiliate_rewards');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('referral_clicks');
        Schema::dropIfExists('affiliates');
    }
};
