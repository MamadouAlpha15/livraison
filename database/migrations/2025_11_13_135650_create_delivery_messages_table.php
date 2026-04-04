<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_company_id')->constrained('delivery_companies')->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained('shops')->nullOnDelete(); // la boutique qui contacte l'entreprise
            $table->unsignedBigInteger('sender_id')->nullable(); // user id who sent (optional)
            $table->string('sender_role')->nullable(); // 'shop' or 'company' or 'user' (utile pour affichage)
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['delivery_company_id','shop_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_messages');
    }
}
