<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Foundation\Vite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Paiement des campagnes : ChapChap Pay ; paiement simulé seulement hors production et sans clé configurée
        $this->app->bind(\App\Support\Campaigns\Payment\CampaignPaymentGateway::class, function ($app) {
            return !$app->isProduction() && blank(config('chapchappay.api_key'))
                ? new \App\Support\Campaigns\Payment\FakeCampaignGateway()
                : $app->make(\App\Support\Campaigns\Payment\ChapChapCampaignGateway::class);
        });

        // IA de Shopio (réponses du support…) : Claude si la clé est configurée, sinon une erreur claire
        $this->app->bind(\App\Support\Ai\TextModel::class, fn () => blank(config('services.anthropic.key'))
            ? new \App\Support\Ai\UnconfiguredTextModel()
            : new \App\Support\Ai\ClaudeTextModel((string) config('services.anthropic.key')));

        // Shopio IA dans la messagerie (outils) : Claude si la clé est configurée, sinon message de secours
        $this->app->bind(\App\Services\ShopioAI\ChatModel::class, fn () => blank(config('services.anthropic.key'))
            ? new \App\Services\ShopioAI\UnconfiguredChatModel()
            : new \App\Services\ShopioAI\ClaudeChatModel((string) config('services.anthropic.key')));

        // SMS : Nimba SMS si ses identifiants sont dans .env, sinon les messages vont dans le journal
        $this->app->bind(\App\Support\Sms\SmsSender::class, fn () => \App\Support\Sms\NimbaSmsSender::configured()
            ? new \App\Support\Sms\NimbaSmsSender((string) config('services.nimba.service_id'), (string) config('services.nimba.secret'), (string) config('services.nimba.sender'))
            : new \App\Support\Sms\LogSmsSender());
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // Montant en GNF : @gnf($montant) → « 150 000 GNF » (App\Support\Money)
        \Illuminate\Support\Facades\Blade::directive('gnf', fn ($expression) => "<?php echo e(\\App\\Support\\Money::gnf($expression)); ?>");

        // Programme d'affiliation : un produit ou une commande peut rendre « Active » une boutique parrainée
        \App\Models\Product::observe(\App\Observers\ReferralActivityObserver::class);
        \App\Models\Order::observe(\App\Observers\ReferralActivityObserver::class);

        // Shopio IA : permission de chaque outil (App\Policies\ShopioAiToolPolicy)
        \Illuminate\Support\Facades\Gate::define('shopio-ai.view-product', [\App\Policies\ShopioAiToolPolicy::class, 'viewProduct']);
        \Illuminate\Support\Facades\Gate::define('shopio-ai.view-order', [\App\Policies\ShopioAiToolPolicy::class, 'viewOrder']);
        \Illuminate\Support\Facades\Gate::define('shopio-ai.create-offer', [\App\Policies\ShopioAiToolPolicy::class, 'createOffer']);
        // Shopio IA répond aux messages des clients ; le vendeur qui écrit lui-même la met en pause
        \App\Models\ShopMessage::observe(\App\Observers\ShopioAiObserver::class);

        // Relances : un message du client ou une commande arrête les relances en cours
        \App\Models\ShopMessage::observe(\App\Observers\FollowUpActivityObserver::class);
        \App\Models\Order::observe(\App\Observers\FollowUpActivityObserver::class);

        /* Le CSS compilé par Vite (app.css, boutique-dashboard.css, ...) ne doit pas
           bloquer le premier affichage sur réseau lent : même astuce media="print" que
           pour Bootstrap/Google Fonts dans layouts/app.blade.php. Le loader plein écran
           #pg-loader (inline, toujours dispo immédiatement) masque déjà la page tant que
           ce CSS n'est pas prêt — voir le script "stylesReady" en bas de ce layout. */
        $this->app->make(Vite::class)->useStyleTagAttributes([
            'media' => 'print',
            'onload' => "this.media='all'",
        ]);
    }
}
