<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courier_commissions', function (Blueprint $table) {
            // Lien vers le lot de livraison groupée — une seule commission par batch
            $table->string('delivery_batch_id', 36)->nullable()->after('shop_id');
            $table->index('delivery_batch_id');
        });
    }

    public function down(): void
    {
        Schema::table('courier_commissions', function (Blueprint $table) {
            $table->dropIndex(['delivery_batch_id']);
            $table->dropColumn('delivery_batch_id');
        });
    }
};
