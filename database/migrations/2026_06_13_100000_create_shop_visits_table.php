<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->date('visited_on');
            $table->unsignedInteger('count')->default(1);
            $table->unique(['shop_id', 'visited_on']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_visits');
    }
};
