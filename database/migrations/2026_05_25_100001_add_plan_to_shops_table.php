<?php

// ─────────────────────────────────────────────────────────────────────────────
// Migration : ajoute les colonnes plan + plan_expires_at à la table shops
// plan = 'free' (défaut) ou 'pro'
// plan_expires_at = null si gratuit, sinon date d'expiration du plan payant
// ─────────────────────────────────────────────────────────────────────────────

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Plan actif de la boutique
            $table->enum('plan', ['free', 'pro'])->default('free')->after('currency');

            // Date d'expiration du plan Pro (null = plan gratuit)
            $table->timestamp('plan_expires_at')->nullable()->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['plan', 'plan_expires_at']);
        });
    }
};
