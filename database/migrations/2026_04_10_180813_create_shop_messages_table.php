<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table : shop_messages
 * Gère le chat entre un client et le propriétaire d'une boutique
 * autour d'un produit précis.
 *
 * Colonnes :
 *   shop_id      → boutique concernée
 *   product_id   → produit concerné (nullable si message général)
 *   sender_id    → celui qui envoie (client ou admin boutique)
 *   receiver_id  → celui qui reçoit
 *   body         → texte du message
 *   read_at      → timestamp de lecture (null = non lu)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'sender_id', 'receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_messages');
    }
};