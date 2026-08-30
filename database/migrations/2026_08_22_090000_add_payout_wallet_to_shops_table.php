<?php

// ─────────────────────────────────────────────────────────────────────────────
// Migration : ajoute le numéro Mobile Money de la boutique où lui reverser
// sa part des commandes payées en ligne (ChapChap Pay).
// ─────────────────────────────────────────────────────────────────────────────

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Canaux disponibles côté ChapChap Pay pour un règlement (voir doc API "Règlements")
            $table->enum('payout_wallet_type', ['paycard', 'orange_money', 'mtn_momo', 'kulu', 'soutra_money', 'akiba'])
                  ->nullable()->after('commission_rate');
            $table->string('payout_wallet_number', 30)->nullable()->after('payout_wallet_type');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['payout_wallet_type', 'payout_wallet_number']);
        });
    }
};
