<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Carnet d'adresses du client (Maison, Bureau…) proposé en un clic à la commande
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40);
            $table->string('address', 255);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_default']);
        });

        // Créneau de livraison souhaité par le client (libellé lisible, ex. « Jeu. 23 oct. · matin (8h–12h) »)
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_slot', 80)->nullable()->after('delivery_destination');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_addresses');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_slot');
        });
    }
};
