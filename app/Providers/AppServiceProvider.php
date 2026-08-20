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
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

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
