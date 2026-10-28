<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Shopio IA dans la messagerie (App\Services\ShopioAI) : FAQ de Shopio, réglages par boutique,
 * conversations reprises par le vendeur, suggestions à valider, journal des outils, consommation et limites.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_entries', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30);
            $table->string('question');
            $table->text('answer');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('shop_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->string('mode', 20)->default('suggestion');    // suggestion | auto
            $table->string('address_form', 5)->default('tu');     // tu | vous
            $table->unsignedTinyInteger('max_discount_percent')->nullable(); // remise que l'IA peut accorder seule
            $table->text('opening_hours')->nullable();
            $table->text('delivery_info')->nullable();
            $table->text('return_policy')->nullable();
            $table->text('faq')->nullable();                      // réponses fréquentes de la boutique
            $table->text('tone_notes')->nullable();               // ton souhaité, consignes
            $table->timestamps();
        });

        Schema::create('ai_conversation_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('human_until')->nullable();          // le vendeur répond lui-même jusqu'à…
            $table->timestamp('handed_off_at')->nullable();        // transférée au vendeur par l'IA
            $table->string('handoff_reason')->nullable();
            $table->timestamps();
            $table->unique(['shop_id', 'client_id']);
        });

        Schema::create('ai_reply_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reply_to_message_id')->nullable()->constrained('shop_messages')->nullOnDelete();
            $table->text('body');
            $table->string('status', 20)->default('pending');     // pending | sent | dismissed
            $table->foreignId('shop_message_id')->nullable()->constrained('shop_messages')->nullOnDelete();
            $table->timestamps();
            $table->index(['shop_id', 'status']);
        });

        Schema::create('ai_tool_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('shop_message_id')->nullable(); // message du client traité
            $table->string('tool', 40);
            $table->json('input');                                 // paramètres, sans données sensibles
            $table->boolean('ok');
            $table->string('summary')->nullable();                 // résumé du résultat
            $table->unsignedInteger('duration_ms')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['shop_id', 'created_at']);
        });

        Schema::create('ai_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('month', 7);                            // AAAA-MM
            $table->unsignedInteger('messages')->default(0);       // réponses ou suggestions produites
            $table->unsignedInteger('handoffs')->default(0);
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->unsignedBigInteger('cost_micro_usd')->default(0);
            $table->timestamps();
            $table->unique(['shop_id', 'month']);
        });

        Schema::create('shopio_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('free_monthly_limit');
            $table->unsignedInteger('pro_monthly_limit');
            $table->boolean('free_auto_allowed')->default(false); // réponse automatique au plan gratuit
            $table->timestamps();
        });
        DB::table('shopio_ai_settings')->insert([
            'enabled' => true, 'free_monthly_limit' => 50, 'pro_monthly_limit' => 1000, 'free_auto_allowed' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Schema::table('shop_messages', function (Blueprint $table) {
            $table->boolean('is_ai')->default(false)->after('follow_up_id'); // écrit par Shopio IA
        });

        $this->seedFaq();
    }

    /** FAQ de départ : uniquement des faits présents dans le code de Shopio. L'admin la complète. */
    private function seedFaq(): void
    {
        $pro = number_format((int) config('chapchappay.plans.pro', 150000), 0, ',', ' ');
        $business = number_format((int) config('chapchappay.plans.business', 100000), 0, ',', ' ');
        $rows = [
            ['paiement', 'Comment payer ma commande ?', 'Par défaut, tu paies en espèces au livreur, à la livraison. Tu n\'as rien à payer en ligne pour commander.'],
            ['offres', 'Comment négocier un prix ?', 'Dans la messagerie du produit, propose ton prix. Le vendeur peut l\'accepter, le refuser ou te faire une contre-offre. Quand tu confirmes l\'offre du vendeur, ta commande est créée automatiquement.'],
            ['compte', 'Comment créer un compte ?', 'Clique sur « S\'inscrire », remplis ton nom, ton e-mail et ton mot de passe, puis saisis le code reçu par e-mail.'],
            ['plans', 'Combien coûte le plan Pro des boutiques ?', "Le plan Pro coûte {$pro} GNF par mois. Chaque nouvelle boutique a 30 jours d'essai gratuit. Au plan gratuit, une boutique peut avoir 5 produits et traiter 10 commandes par mois."],
            ['plans', 'Combien coûte le plan Business des entreprises de livraison ?', "Le plan Business coûte {$business} GNF par mois, avec 30 jours d'essai gratuit pour chaque nouvelle entreprise."],
            ['parrainage', 'Comment marche le parrainage des boutiques ?', 'Une boutique ou un affilié partage son lien. Quand une boutique parrainée paie son abonnement, le parrain touche une commission, versée par Mobile Money.'],
        ];
        foreach ($rows as $i => [$cat, $q, $a]) {
            DB::table('faq_entries')->insert(['category' => $cat, 'question' => $q, 'answer' => $a, 'is_active' => true, 'position' => $i, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('shop_messages', function (Blueprint $table) {
            $table->dropColumn('is_ai');
        });
        foreach (['shopio_ai_settings', 'ai_usages', 'ai_tool_calls', 'ai_reply_suggestions', 'ai_conversation_states', 'shop_ai_settings', 'faq_entries'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
