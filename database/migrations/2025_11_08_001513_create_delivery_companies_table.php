<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable()->unique(); // unique slug for SEO
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // logo / image de l’entreprise
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('address')->nullable();
            $table->decimal('commission_percent', 5, 2)->default(0.00);
            $table->boolean('approved')->default(false); // approuvé par superadmin ?
            $table->timestamp('approved_at')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // propriétaire
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_companies');
    }
}
