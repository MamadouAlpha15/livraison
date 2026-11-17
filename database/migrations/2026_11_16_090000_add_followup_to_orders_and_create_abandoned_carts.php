<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Page « Commandes » (suivi des appels) et « Paniers abandonnés ».
 *  - orders.followup     : précision du suivi quand le statut seul ne suffit pas
 *                          (injoignable, programmee, retournee) — le statut reste un des 5 statuts Shopio
 *  - orders.followup_at  : date de rappel ou de livraison prévue (commande programmée)
 *  - orders.vendor_note  : notes de la boutique sur la commande (jamais montrées au client)
 *  - abandoned_carts     : clients qui ont commencé à remplir le formulaire de commande sans le valider
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('followup', 20)->nullable()->after('status');
            $table->timestamp('followup_at')->nullable()->after('followup');
            $table->text('vendor_note')->nullable()->after('followup_at');
            $table->index(['shop_id', 'status', 'followup']);
        });

        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('visitor', 40);                 // identifiant aléatoire du navigateur (pas de donnée personnelle)
            $table->string('name', 120)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('commune', 60)->nullable();
            $table->string('landmark', 120)->nullable();
            $table->json('items')->nullable();            // [{id, name, qty, total}]
            $table->unsignedBigInteger('total')->default(0);
            $table->string('state', 20)->default('a_rappeler'); // a_rappeler, injoignable, pas_interesse, recupere
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->unique(['shop_id', 'visitor']);
            $table->index(['shop_id', 'state', 'last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'status', 'followup']);
            $table->dropColumn(['followup', 'followup_at', 'vendor_note']);
        });
    }
};
