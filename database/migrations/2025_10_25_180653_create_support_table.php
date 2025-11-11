<?php

// database/migrations/2025_01_01_000000_create_support_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('support_tickets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // créateur (client ou staff)
            $t->string('subject', 160);
            $t->enum('status', ['open', 'closed'])->default('open');
            $t->timestamps();

            $t->index(['shop_id', 'status']);
        });

        Schema::create('support_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->text('body');
            $t->timestamps();

            $t->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
    }
};
