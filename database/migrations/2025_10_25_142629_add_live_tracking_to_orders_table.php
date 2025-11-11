<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $t) {
            $t->decimal('current_lat', 10, 7)->nullable(); // latitude 
            $t->decimal('current_lng', 10, 7)->nullable(); // longitude
            $t->timestamp('last_ping_at')->nullable(); // dernier ping
        });
    }
    public function down(): void {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropColumn(['current_lat','current_lng','last_ping_at']);
        });
    }
};
