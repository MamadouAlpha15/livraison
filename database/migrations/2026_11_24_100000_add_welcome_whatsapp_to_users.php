<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * « Bienvenue WhatsApp » (Admin → Bienvenue WhatsApp) : l'équipe Shopio écrit à chaque nouvel inscrit.
 * welcome_sent_at : quand le message a été envoyé (null = pas encore contacté) ; welcome_sent_by : l'admin qui l'a envoyé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('welcome_sent_at')->nullable()->index();
            $table->unsignedBigInteger('welcome_sent_by')->nullable();
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
