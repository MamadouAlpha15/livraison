<?php

// ─────────────────────────────────────────────────────────────────────────────
// Migration : table subscriptions
// Stocke tous les abonnements (boutiques et entreprises).
// subscriber_type + subscriber_id = polymorphisme (Shop ou DeliveryCompany)
// ─────────────────────────────────────────────────────────────────────────────

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // Relation polymorphe : lié à un Shop ou à un DeliveryCompany
            $table->morphs('subscriber');               // subscriber_type + subscriber_id

            // Plan souscrit : 'pro' (boutique) ou 'business' (entreprise)
            $table->string('plan', 20);

            // Montant payé en GNF
            $table->unsignedBigInteger('amount');

            // Devise (toujours GNF pour la Guinée)
            $table->string('currency', 10)->default('GNF');

            // Méthode de paiement choisie par l'utilisateur
            $table->string('payment_method', 40)->nullable();

            // Référence de transaction retournée par GenuisPay
            $table->string('payment_reference', 191)->nullable()->unique();

            // Statut du paiement : pending → active → expired | failed
            $table->enum('status', ['pending', 'active', 'expired', 'failed'])->default('pending');

            // Dates de l'abonnement
            $table->timestamp('started_at')->nullable();    // Date d'activation (après confirmation paiement)
            $table->timestamp('expires_at')->nullable();    // Date d'expiration (started_at + 30 jours)

            // Données brutes retournées par GenuisPay (pour debug/audit)
            $table->json('gateway_response')->nullable();

            $table->timestamps();

            // Index pour la recherche rapide par référence
            $table->index(['subscriber_type', 'subscriber_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
