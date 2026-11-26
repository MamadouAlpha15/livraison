<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parrainage (Boutique → Parrainage) : code d'invitation de chaque boutique, boutique qui a invité,
 * date de la récompense du parrain (au premier paiement de la boutique invitée).
 * Témoignages en vidéo : vidéo courte jointe au témoignage d'un vendeur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('referral_code', 12)->nullable()->unique()->after('slug');
            $table->foreignId('referred_by_shop_id')->nullable()->after('referral_code')->constrained('shops')->nullOnDelete();
            $table->timestamp('referral_rewarded_at')->nullable()->after('referred_by_shop_id');
        });
        Schema::table('shop_testimonials', function (Blueprint $table) {
            $table->string('video', 255)->nullable()->after('author_role');
        });
    }

    public function down(): void
    {
        Schema::table('shop_testimonials', fn (Blueprint $t) => $t->dropColumn('video'));
        Schema::table('shops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_shop_id');
            $table->dropColumn(['referral_code', 'referral_rewarded_at']);
        });
    }
};
