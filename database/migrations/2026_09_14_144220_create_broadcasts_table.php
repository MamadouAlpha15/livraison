<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            // Forcé en InnoDB : le reste de la base tourne en MyISAM (découvert lors
            // d'un audit récent — ne supporte ni transactions ni clés étrangères
            // réelles). Cette table est nouvelle, autant qu'elle soit fiable dès le départ.
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('target_role');            // client | admin (vendeur) | company
            $table->string('subject');
            $table->text('body');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('status')->default('queued'); // queued | sending | done
            // Pas de contrainte FK vers `users` : cette table est MyISAM (voir audit),
            // MySQL refuse une clé étrangère InnoDB → MyISAM. Intégrité gérée côté app.
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
