<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Message de bienvenue WhatsApp (Admin → Bienvenue WhatsApp) : quand l'équipe Shopio a écrit au nouvel inscrit,
 * et qui l'a fait. Vide : pas encore contacté.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('welcome_sent_at')->nullable();
            $table->unsignedBigInteger('welcome_sent_by')->nullable();
            $table->index('welcome_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['welcome_sent_at']);
            $table->dropColumn(['welcome_sent_at', 'welcome_sent_by']);
        });
    }
};
