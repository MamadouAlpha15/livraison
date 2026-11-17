<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Relance des clients et entreprises de livraison inactifs (App\Services\InactivityReminderService) :
 * dernière activité et désinscription des relances sur users, journal des relances envoyées, réglages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable()->index()->after('remember_token');
            $table->timestamp('reminders_opt_out_at')->nullable()->after('last_active_at');
        });

        Schema::create('inactivity_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('audience', 20);          // client | company
            $table->unsignedTinyInteger('step');     // 1er, 2e, 3e rappel d'une même période d'inactivité
            $table->json('channels');                // canaux réellement utilisés
            $table->timestamp('sent_at');
            $table->timestamp('returned_at')->nullable(); // revenu sur Shopio après ce rappel
            $table->timestamps();
            $table->index(['user_id', 'sent_at']);
            $table->index(['audience', 'sent_at']);
        });

        Schema::create('reminder_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled');
            $table->unsignedSmallInteger('client_days');   // inactif depuis… avant le 1er rappel
            $table->unsignedSmallInteger('company_days');
            $table->unsignedSmallInteger('interval_days'); // entre deux rappels
            $table->unsignedTinyInteger('max_steps');      // rappels au plus par période d'inactivité
            $table->unsignedSmallInteger('daily_limit');   // envois au plus par jour
            $table->boolean('channel_push');
            $table->boolean('channel_mail');
            $table->boolean('channel_sms');
            $table->timestamps();
        });

        DB::table('reminder_settings')->insert([
            'enabled' => true, 'client_days' => 30, 'company_days' => 14, 'interval_days' => 15, 'max_steps' => 3,
            'daily_limit' => 300, 'channel_push' => true, 'channel_mail' => true, 'channel_sms' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->backfillLastActivity();
    }

    /**
     * Dernière activité connue de chaque client et entreprise : dernière session web, dernier usage
     * de l'application (jeton), dernière commande, ou à défaut la date d'inscription.
     */
    private function backfillLastActivity(): void
    {
        DB::table('users')->whereIn('role', ['client', 'company'])->select('id', 'created_at')->orderBy('id')
            ->chunk(500, function ($users) {
                $ids = $users->pluck('id');
                $sessions = Schema::hasTable('sessions')
                    ? DB::table('sessions')->whereIn('user_id', $ids)->groupBy('user_id')->selectRaw('user_id, max(last_activity) as t')->pluck('t', 'user_id')
                    : collect();
                $tokens = Schema::hasTable('personal_access_tokens')
                    ? DB::table('personal_access_tokens')->where('tokenable_type', 'App\\Models\\User')->whereIn('tokenable_id', $ids)
                        ->groupBy('tokenable_id')->selectRaw('tokenable_id, max(last_used_at) as t')->pluck('t', 'tokenable_id')
                    : collect();
                $orders = DB::table('orders')->whereIn('user_id', $ids)->groupBy('user_id')->selectRaw('user_id, max(created_at) as t')->pluck('t', 'user_id');

                foreach ($users as $user) {
                    $dates = array_filter([
                        $user->created_at ? Carbon::parse($user->created_at) : null,
                        isset($sessions[$user->id]) ? Carbon::createFromTimestamp((int) $sessions[$user->id]) : null,
                        isset($tokens[$user->id]) && $tokens[$user->id] ? Carbon::parse($tokens[$user->id]) : null,
                        isset($orders[$user->id]) ? Carbon::parse($orders[$user->id]) : null,
                    ]);
                    if ($dates) {
                        DB::table('users')->where('id', $user->id)->update(['last_active_at' => max($dates)]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_settings');
        Schema::dropIfExists('inactivity_reminders');
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['last_active_at']);
            $table->dropColumn(['last_active_at', 'reminders_opt_out_at']);
        });
    }
};
