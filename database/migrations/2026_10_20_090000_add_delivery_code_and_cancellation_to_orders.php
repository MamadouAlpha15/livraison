<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Code à 4 chiffres que le client donne au livreur à la remise du colis
            $table->string('delivery_code', 4)->nullable()->after('delivery_proof_photo');
            // Annulation demandée par le client lui-même (tant que la boutique n'a pas confirmé)
            $table->string('cancel_reason', 120)->nullable()->after('delivery_code');
            $table->string('cancelled_by', 20)->nullable()->after('cancel_reason');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_code', 'cancel_reason', 'cancelled_by']);
        });
    }
};
