<?php

// ─────────────────────────────────────────────────────────────────────────────
// Migration : ajoute le paiement en ligne (ChapChap Pay) et le suivi du
// reversement à la boutique sur la table payments.
// ─────────────────────────────────────────────────────────────────────────────

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // 'method' était enum('cash') seul jusqu'ici — on ajoute le paiement en ligne
            $table->string('method', 20)->default('cash')->change();

            // Référence de l'opération E-Commerce ChapChap Pay (pour retrouver/vérifier le paiement)
            $table->string('gateway_operation_id', 191)->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('gateway_operation_id');

            // Reversement à la boutique (uniquement pertinent pour method='chapchappay')
            // payout_status : null (n/a, ex: cash), 'due', 'processing', 'sent', 'failed'
            $table->string('payout_status', 20)->nullable()->after('paid_at');
            $table->unsignedBigInteger('platform_fee_amount')->nullable()->after('payout_status'); // en GNF (entier)
            $table->unsignedBigInteger('payout_amount')->nullable()->after('platform_fee_amount');  // montant net reversé à la boutique
            $table->string('payout_reference', 191)->nullable()->after('payout_amount');            // payout_request_id ChapChap Pay
            $table->timestamp('payout_sent_at')->nullable()->after('payout_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_operation_id', 'paid_at', 'payout_status',
                'platform_fee_amount', 'payout_amount', 'payout_reference', 'payout_sent_at',
            ]);
            $table->enum('method', ['cash'])->default('cash')->change();
        });
    }
};
