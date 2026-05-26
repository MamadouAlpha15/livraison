<?php

// ─────────────────────────────────────────────────────────────────────────────
// Migration : ajoute les colonnes plan + plan_expires_at à delivery_companies
// plan = 'free' (défaut) ou 'business'
// ─────────────────────────────────────────────────────────────────────────────

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_companies', function (Blueprint $table) {
            $table->enum('plan', ['free', 'business'])->default('free')->after('currency');
            $table->timestamp('plan_expires_at')->nullable()->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_companies', function (Blueprint $table) {
            $table->dropColumn(['plan', 'plan_expires_at']);
        });
    }
};
