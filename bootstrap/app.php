<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ici on enregistre nos middlewares personnalisés
        $middleware->alias([
            'role'          => \App\Http\Middleware\RoleMiddleware::class,
            'scope.shop'    => \App\Http\Middleware\ScopeShop::class,
            'force.pwd'     => \App\Http\Middleware\ForcePasswordChange::class,
            // Plan boutique : bloque les routes réservées au Plan Pro
            'shop.plan'     => \App\Http\Middleware\CheckShopPlan::class,
            // Plan entreprise : bloque les routes réservées au Plan Business
            'company.plan'  => \App\Http\Middleware\CheckCompanyPlan::class,
            // Employé limité aux commandes uniquement (défini par le propriétaire de la boutique)
            'orders.only'   => \App\Http\Middleware\RestrictEmployeeToOrders::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ForcePasswordChange::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
