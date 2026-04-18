<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_messages', function (Blueprint $table) {
            // Type de message : text | price_proposal | price_offer | order_created
            $table->string('type')->default('text')->after('body');

            // Prix proposé/offert (jamais touché à product.price)
            $table->decimal('proposed_price', 15, 2)->nullable()->after('type');

            // Statut de la proposition/offre : pending | accepted | refused
            $table->string('proposal_status')->nullable()->after('proposed_price');

            // Commande créée suite à la négociation
            $table->foreignId('negotiated_order_id')
                  ->nullable()
                  ->after('proposal_status')
                  ->constrained('orders')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shop_messages', function (Blueprint $table) {
            $table->dropForeign(['negotiated_order_id']);
            $table->dropColumn(['type', 'proposed_price', 'proposal_status', 'negotiated_order_id']);
        });
    }
};
