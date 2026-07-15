<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('loyalty_points')->default(0)->after('notif_state');
            $table->string('referral_code', 10)->nullable()->unique()->after('loyalty_points');
            $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
            $table->timestamp('referral_rewarded_at')->nullable()->after('referred_by');

            $table->foreign('referred_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['loyalty_points', 'referral_code', 'referred_by', 'referral_rewarded_at']);
        });
    }
};
