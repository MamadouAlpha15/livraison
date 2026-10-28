<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Relances automatiques et personnalisables (App\Services\FollowUpService) :
 * règles par boutique (ou de l'admin pour les vendeurs inactifs), relances prévues et envoyées,
 * désinscriptions (lien ou mot STOP), et lien entre un message de la messagerie et sa relance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_up_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete(); // vide = règle de l'admin
            $table->string('type', 40);
            $table->boolean('enabled')->default(false);
            $table->unsignedInteger('delay_minutes');        // avant la 1re relance
            $table->unsignedTinyInteger('max_attempts');     // relances au plus
            $table->unsignedInteger('interval_minutes');     // entre deux relances
            $table->text('template')->nullable();            // vide = modèle par défaut du ton et de la langue
            $table->string('tone', 20)->default('amical');   // amical | professionnel | urgent
            $table->string('locale', 5)->default('fr');
            $table->foreignId('promo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('send_from_hour')->default(8);  // heure de Conakry
            $table->unsignedTinyInteger('send_to_hour')->default(21);
            $table->json('excluded_days')->nullable();       // 0 = dimanche … 6 = samedi
            $table->json('channels');                        // in_app | messaging | sms | whatsapp
            $table->timestamps();
            $table->unique(['shop_id', 'type']);
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follow_up_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // destinataire
            $table->string('type', 40);
            $table->string('subject_type', 40);              // message | order | stock_alert | shop_sans_produit | absence
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); // produit concerné, si connu
            $table->unsignedTinyInteger('attempt');
            $table->string('status', 20)->default('scheduled'); // scheduled | sent | stopped | failed
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->json('channels')->nullable();            // canaux réellement utilisés
            $table->text('message')->nullable();
            $table->unsignedBigInteger('shop_message_id')->nullable(); // message posté dans la messagerie
            $table->string('notification_id', 36)->nullable();         // notification dans l'appli
            $table->timestamp('responded_at')->nullable();
            $table->string('stop_reason', 40)->nullable();
            $table->foreignId('recovered_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->unsignedBigInteger('recovered_amount')->default(0); // GNF
            $table->timestamps();
            // Une relance n'est jamais créée (donc envoyée) deux fois
            $table->unique(['type', 'subject_type', 'subject_id', 'user_id', 'attempt'], 'follow_ups_unique_attempt');
            $table->index(['status', 'scheduled_at']);
            $table->index(['user_id', 'sent_at']);
            $table->index(['shop_id', 'sent_at']);
        });

        Schema::create('follow_up_opt_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete(); // vide = toutes les boutiques
            $table->string('source', 20);                    // lien | stop
            $table->timestamps();
            $table->unique(['user_id', 'shop_id']);
        });

        Schema::table('shop_messages', function (Blueprint $table) {
            $table->foreignId('follow_up_id')->nullable()->after('negotiated_order_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shop_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('follow_up_id');
        });
        Schema::dropIfExists('follow_up_opt_outs');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('follow_up_rules');
    }
};
