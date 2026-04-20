<?php

/*
|==========================================================================
| FICHIER : routes/web.php
| APPLICATION : Système de gestion boutique en ligne (Laravel 12)
|==========================================================================
|
| INDEX DES ROUTES PAR RÔLE / SECTION :
|
|  1.  IMPORTS (use statements)
|  2.  ROUTES PUBLIQUES          → /, /shops, /orders (tracking)
|  3.  AUTH / PROFIL             → /profile, /dashboard
|  4.  NOTIFICATIONS             → /notifications
|  5.  SUPERADMIN                → /admin
|  6.  ADMIN BOUTIQUE            → /boutique
|  7.  VENDEUR / ADMIN           → /products, /shop, /orders (vendeur)
|  8.  EMPLOYÉ                   → /employe
|  9.  LIVREUR                   → /livreur
| 10.  CLIENT                    → /client
| 11.  ENTREPRISES DE LIVRAISON  → /delivery-companies, /company
| 12.  SUPPORT                   → /support
| 13.  TRACKING GPS              → /orders/{order}/position
| 14.  AUTH (fichier externe)    → auth.php
|
|==========================================================================
*/

use Illuminate\Support\Facades\Route;

/* ── Contrôleurs : Auth & Profil ── */
use App\Http\Controllers\ProfileController;

/* ── Contrôleurs : Dashboards par rôle ── */
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Employe\DashboardController as EmployeDashboard;
use App\Http\Controllers\Vendeur\DashboardController as VendeurDashboard;
use App\Http\Controllers\Livreur\DashboardController as LivreurDashboard;
use App\Http\Controllers\Client\DashboardController as ClientDashboard;

/* ── Contrôleurs : Boutiques / Shops ── */
use App\Http\Controllers\Vendeur\ShopController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\PublicShopController;

/* ── Contrôleurs : Produits ── */
use App\Http\Controllers\Vendeur\ProductController;

/* ── Contrôleurs : Commandes ── */
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Vendeur\OrderController as VendeurOrderController;
use App\Http\Controllers\Livreur\OrderController as LivreurOrderController;
use App\Http\Controllers\Employe\OrderController as EmployeOrderController;

/* ── Contrôleurs : Paiements & Commissions ── */
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Vendeur\PaymentController as VendeurPaymentController;
use App\Http\Controllers\Livreur\CommissionController as LivreurCommissionController;
use App\Http\Controllers\Vendeur\CommissionController as VendeurCommissionController;

/* ── Contrôleurs : Rapports & Statistiques ── */
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StatController;

/* ── Contrôleurs : Avis & Reviews ── */
use App\Http\Controllers\Vendeur\ReviewController as VendeurReviewController;
use App\Http\Controllers\Client\ReviewController;

/* ── Contrôleurs : Entreprises de livraison ── */
use App\Http\Controllers\DeliveryCompanyController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DeliveryChatController;

/* ── Contrôleurs : Export (Excel / PDF) ── */
use App\Http\Controllers\Boutique\ExportController;

/* ── Contrôleurs : Disponibilité ── */
use App\Http\Controllers\Livreur\AvailabilityController;
use App\Http\Controllers\Boutique\AvailabilityController as BoutiqueAvailabilityController;

/* ── Contrôleurs : Notifications ── */
use App\Http\Controllers\NotificationController;

/* ── Contrôleurs : Tracking GPS ── */
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\OrderTrackingTestController;
use App\Http\Controllers\SuiviController;

/* ── Contrôleurs : Support Client ── */
use App\Http\Controllers\Support\SupportTicketController;
use App\Http\Controllers\Support\SupportMessageController;

/* ── Contrôleurs : Divers ── */
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Client\ShopSubscriptionController;
use App\Http\Controllers\Admin\DashboardController;

/*contrôleur pour les clients* message */
 
use App\Http\Controllers\Client\ShopMessageController;

use App\Http\Controllers\Boutique\BoutiqueMessageController;
use App\Http\Controllers\Client\ProductController as ClientProductController;

/* ══════════════════════════════════════════════════════════════════════════
|  2. ROUTES PUBLIQUES
|  Accessibles sans authentification
══════════════════════════════════════════════════════════════════════════ */

/* Page d'accueil */
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

/* Liste publique de toutes les boutiques */
Route::get('/shops', [PublicShopController::class, 'index'])
    ->name('shops.index');

/* Produits publics d'une boutique */
Route::get('/shops/{shop}/products', [PublicShopController::class, 'products'])
    ->name('public.shops.products');

/* Suivi de commande (public — lien partageable) */
Route::get('/orders/{order}', [SuiviController::class, 'show'])
    ->name('orders.show');


/* ══════════════════════════════════════════════════════════════════════════
|  3. AUTH / PROFIL
|  Requiert : auth
══════════════════════════════════════════════════════════════════════════ */

// Redirection intelligente selon le rôle — évite la page Breeze vide "You're logged in!"
Route::get('/dashboard', function () {
    $user = auth()->user();
    return match($user->role ?? '') {
        'superadmin' => redirect()->route('admin.dashboard'),
        'admin'      => redirect()->route('boutique.dashboard'),
        'company'    => redirect()->route('company.dashboard'),
        'livreur'    => redirect()->route('livreur.dashboard'),
        'vendeur'    => redirect()->route('vendeur.dashboard'),
        'employe'    => redirect()->route('employe.dashboard'),
        default      => redirect()->route('client.dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {

    /* Profil utilisateur */
    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    /* ════════════════════════════════════════════════════════════════════
    |  4. NOTIFICATIONS
    |  Requiert : auth
    ════════════════════════════════════════════════════════════════════ */

    Route::get('/notifications/read/{id}',  [NotificationController::class, 'read'])   ->name('notifications.read');
    Route::get('/notifications/read-all',   [NotificationController::class, 'readAll'])->name('notifications.readAll');


    /* ════════════════════════════════════════════════════════════════════
    |  11. ENTREPRISES DE LIVRAISON
    |  Requiert : auth (toutes les routes de cette section)
    ════════════════════════════════════════════════════════════════════ */

    /* Listing public des entreprises + contact */
    Route::get('/delivery-companies',         [DeliveryCompanyController::class, 'index'])->name('delivery.companies.index');
    Route::get('/delivery-companies/{company}',[DeliveryCompanyController::class, 'show']) ->name('delivery.companies.show');

    /* Chat entre une boutique et une entreprise de livraison */
    Route::get('/company/{company}/chat',              [DeliveryChatController::class, 'show'])    ->name('company.chat.show');
    Route::post('/company/{company}/chat/send',        [DeliveryChatController::class, 'send'])    ->name('company.chat.send');
    Route::get('/company/{company}/chat/messages',     [DeliveryChatController::class, 'messages'])->name('company.chat.messages');

    /* Espace propriétaire d'entreprise de livraison */
    Route::middleware('role:admin,company')->prefix('company')->group(function () {
        Route::get('/', [DeliveryCompanyController::class, 'dashboard'])->name('company.dashboard');

        /* Gestion des chauffeurs */
        Route::post('/drivers',           [DriverController::class, 'store'])  ->name('company.drivers.store');
        Route::put('/drivers/{driver}',   [DriverController::class, 'update']) ->name('company.drivers.update');
        Route::delete('/drivers/{driver}',[DriverController::class, 'destroy'])->name('company.drivers.destroy');

        /* Création de l'entreprise */
        Route::get('/delivery-company/create', [DeliveryCompanyController::class, 'create'])->name('delivery.company.create');
        Route::post('/delivery-company',        [DeliveryCompanyController::class, 'store']) ->name('delivery.company.store');

        /* Page d'attente de validation */
        Route::get('/company/waiting', function () {
            return view('company.waiting_approval');
        })->name('company.waiting');
    });


    /* ════════════════════════════════════════════════════════════════════
    |  12. SUPPORT CLIENT
    |  Requiert : auth
    ════════════════════════════════════════════════════════════════════ */

    Route::get('/support',                          [SupportTicketController::class, 'index'])  ->name('support.index');
    Route::get('/support/create',                   [SupportTicketController::class, 'create']) ->name('support.create');
    Route::post('/support',                         [SupportTicketController::class, 'store'])  ->name('support.store');
    Route::get('/support/{ticket}',                 [SupportTicketController::class, 'show'])   ->name('support.show');
    Route::post('/support/{ticket}/close',          [SupportTicketController::class, 'close'])  ->name('support.close');
    Route::post('/support/{ticket}/messages',       [SupportMessageController::class, 'store']) ->name('support.messages.store');
    Route::get('/support/{ticket}/messages.json',   [SupportMessageController::class, 'listJson'])->name('support.messages.json');


    /* ════════════════════════════════════════════════════════════════════
    |  13. TRACKING GPS
    |  Requiert : auth
    ════════════════════════════════════════════════════════════════════ */

    /* Afficher la position d'une commande */
    Route::get('/orders/{order}/position', [OrderTrackingController::class, 'show'])
        ->name('orders.position.show');

    /* Mettre à jour la position GPS — max 30 requêtes/minute par livreur */
    Route::post('/orders/{order}/position', [OrderTrackingController::class, 'update'])
        ->middleware('throttle:30,1')
        ->name('orders.position.update');

}); // fin middleware('auth')


/* ══════════════════════════════════════════════════════════════════════════
|  5. SUPERADMIN
|  Requiert : auth + role:superadmin
|  Préfixe : /admin  |  Nom : admin.*
══════════════════════════════════════════════════════════════════════════ */

Route::middleware(['auth', 'role:superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /* Tableau de bord */
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        /* Gestion des boutiques */
        Route::resource('shops', AdminShopController::class)->only(['index', 'update']);

        /* Validation d'une entreprise de livraison */
        Route::post('/companies/{company}/approve', [DashboardController::class, 'approveCompany'])
            ->name('companies.approve');
    });


/* ══════════════════════════════════════════════════════════════════════════
|  6. ADMIN BOUTIQUE (propriétaire de la boutique)
|  Requiert : auth + role:admin
|  Préfixe : /boutique  |  Nom : boutique.*
══════════════════════════════════════════════════════════════════════════ */

Route::middleware(['auth', 'role:admin'])
    ->prefix('boutique')
    ->name('boutique.')
    ->group(function () {

        /* Tableau de bord boutique */
        Route::get('/dashboard', [ShopController::class, 'admin'])->name('dashboard');

        /* Gestion de la boutique */
        Route::resource('shops', ShopController::class)->only(['create', 'store', 'index']);

        /* Modification de la boutique (edit + update) */
        Route::get('shops/{shop}/edit',  [ShopController::class, 'edit'])  ->name('shops.edit');
        Route::put('shops/{shop}',       [ShopController::class, 'update'])->name('shops.update');
        
        // voir touts les livreurs de la boutique
        Route::get('livreurs', [\App\Http\Controllers\Boutique\LivreurController::class, 'index'])
     ->name('livreurs.index');

        /* Gestion des employés (vendeurs, livreurs) */
        Route::resource('employees', \App\Http\Controllers\Vendeur\EmployeeController::class)->except(['show']);

        /* Commandes */
        Route::get('orders', [EmployeOrderController::class, 'index'])                 ->name('orders.index');
        Route::put('orders/{order}/assign', [EmployeOrderController::class, 'assign']) ->name('orders.assign');

        /* Paiements */
        Route::get('payments', [VendeurPaymentController::class, 'index'])->name('payments.index');

        /* Commissions */
        Route::get('commissions',       [VendeurCommissionController::class, 'index'])->name('commissions.index');
        Route::post('commissions/pay',  [VendeurCommissionController::class, 'pay'])  ->name('commissions.pay');

        /* Rapports & Statistiques */
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('stats',   [StatController::class, 'index'])  ->name('stats.index');

        /* Clients de la boutique */
        Route::get('clients',         [\App\Http\Controllers\Boutique\ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{user}',  [\App\Http\Controllers\Boutique\ClientController::class, 'show']) ->name('clients.show');

        /* Analyse par période (AJAX) */
        Route::get('period-stats', \App\Http\Controllers\Boutique\PeriodStatsController::class)
            ->name('period.stats');

        /* Exports Excel */
        Route::get('export/orders/excel',   [ExportController::class, 'exportOrdersExcel'])  ->name('export.orders.excel');
        Route::get('export/payments/excel', [ExportController::class, 'exportPaymentsExcel'])->name('export.payments.excel');
        Route::get('export/stats/excel',    [ExportController::class, 'exportStatsExcel'])   ->name('export.stats.excel');

        /* Exports PDF */
        Route::get('export/orders/pdf',     [ExportController::class, 'exportOrdersPdf'])    ->name('export.orders.pdf');
        Route::get('export/payments/pdf',   [ExportController::class, 'exportPaymentsPdf']) ->name('export.payments.pdf');
        Route::get('export/stats/pdf',      [ExportController::class, 'exportStatsPdf'])    ->name('export.stats.pdf');

        // === HUB MESSAGES (page dédiée vendeur) ===
Route::get('messages',
    [BoutiqueMessageController::class, 'hub'])
    ->name('messages.hub');

        // === MESSAGES CLIENTS ===
Route::post('messages/reply/{client}/{product?}',
    [BoutiqueMessageController::class, 'reply'])
    ->name('messages.reply');

Route::get('messages/poll',
    [BoutiqueMessageController::class, 'poll'])
    ->name('messages.poll');

Route::get('notifications/poll',
    [BoutiqueMessageController::class, 'pollAll'])
    ->name('notifications.poll');

Route::post('messages/read',
    [BoutiqueMessageController::class, 'markAsRead'])
    ->name('messages.read');

// === NÉGOCIATION DE PRIX ===
Route::post('messages/price-offer',
    [BoutiqueMessageController::class, 'createPriceOffer'])
    ->name('messages.price-offer');

Route::post('messages/refuse-proposal/{message}',
    [BoutiqueMessageController::class, 'refuseProposal'])
    ->name('messages.refuse-proposal');

Route::get('messages/conversation',
    [BoutiqueMessageController::class, 'getConversation'])
    ->name('messages.conversation');

Route::post('messages/images/{client}/{product?}',
    [BoutiqueMessageController::class, 'sendImages'])
    ->name('messages.send-images');

Route::get('messages/image-status/{message}',
    [BoutiqueMessageController::class, 'imageStatus'])
    ->name('messages.image-status');
    });


/* ══════════════════════════════════════════════════════════════════════════
|  7. VENDEUR / ADMIN
|  Requiert : auth + role:vendeur,admin
|  (pas de préfixe dédié — routes accessibles sous /)
══════════════════════════════════════════════════════════════════════════ */

Route::middleware(['auth', 'role:vendeur,admin'])->group(function () {

    /* Tableau de bord vendeur */
    Route::get('/vendeur/dashboard', [VendeurDashboard::class, 'index'])->name('vendeur.dashboard');

    /* ── Boutique / Shop ── */
    Route::resource('shop', ShopController::class);
    /* Route nommée attendue par la vue edit → shop.update */
    Route::get('shop/{shop}/edit',  [ShopController::class, 'edit'])  ->name('shop.edit');
    Route::put('shop/{shop}',       [ShopController::class, 'update'])->name('shop.update');

    /* ── Produits ── */
    Route::resource('products', ProductController::class)->except(['show']);

    /* Actions supplémentaires produits */
    Route::post('products/{product}/toggle',    [ProductController::class, 'toggleActive'])->name('products.toggle');
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])   ->name('products.duplicate');

    /* Upload AJAX image unique (galerie ou principale) — évite le POST trop grand */
    Route::post('products/upload-image', [ProductController::class, 'uploadImage'])->name('products.upload.image');

    /* ── Commandes (vendeur) ── */
    Route::get('orders',                         [VendeurOrderController::class, 'index'])     ->name('orders.index');
    Route::put('orders/{order}/confirm',         [VendeurOrderController::class, 'confirm'])   ->name('orders.confirm');
    Route::put('orders/{order}/cancel',          [VendeurOrderController::class, 'cancel'])    ->name('orders.cancel');
    Route::get('orders/{order}/assign',          [VendeurOrderController::class, 'showAssign'])->name('orders.assign.show');
    Route::put('orders/{order}/assign',          [VendeurOrderController::class, 'assign'])    ->name('orders.assign');

    /* ── Avis / Reviews ── */
    Route::get('reviews', [VendeurReviewController::class, 'index'])->name('reviews.index');

    /* ── Paiements (vendeur) ── */
    Route::get('payments', [VendeurPaymentController::class, 'index'])->name('payments.index');
});


/* ══════════════════════════════════════════════════════════════════════════
|  8. EMPLOYÉ
|  Requiert : auth + role:employe,superadmin,admin,vendeur
|  Préfixe : /employe  |  Nom : employe.*
══════════════════════════════════════════════════════════════════════════ */

Route::middleware(['auth', 'role:employe,superadmin,admin,vendeur'])
    ->prefix('employe')
    ->name('employe.')
    ->group(function () {

        /* Tableau de bord */
        Route::get('/dashboard', [EmployeDashboard::class, 'index'])->name('dashboard');

        /* Commandes */
        Route::get('orders',                    [EmployeOrderController::class, 'index']) ->name('orders.index');
        Route::put('orders/{order}/assign',     [EmployeOrderController::class, 'assign'])->name('orders.assign');
        Route::put('orders/{order}/cancel',     [EmployeOrderController::class, 'cancel'])->name('orders.cancel');
        Route::put('orders/{order}/restore',    [EmployeOrderController::class, 'restore'])->name('orders.restore');

        /* Paiements */
        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');

        /* Rapports & Statistiques */
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('stats',   [StatController::class, 'index'])  ->name('stats.index');
    });


/* ══════════════════════════════════════════════════════════════════════════
|  9. LIVREUR
|  Requiert : auth + role:livreur
|  Préfixe : /livreur  |  Nom : livreur.*
══════════════════════════════════════════════════════════════════════════ */

Route::middleware(['auth', 'role:livreur'])
    ->prefix('livreur')
    ->name('livreur.')
    ->group(function () {

        /* Tableau de bord */
        Route::get('/dashboard', [LivreurDashboard::class, 'index'])->name('dashboard');

        /* Commandes */
        Route::get('orders',                        [LivreurOrderController::class, 'index'])   ->name('orders.index');
        Route::put('orders/{order}/start',          [LivreurOrderController::class, 'start'])   ->name('orders.start');
        Route::put('orders/{order}/complete',       [LivreurOrderController::class, 'complete'])->name('orders.complete');

        /* Disponibilité */
        Route::put('/availability/toggle', [AvailabilityController::class, 'toggle'])->name('availability.toggle');

        /* Commissions */
        Route::get('commissions', [LivreurCommissionController::class, 'index'])->name('commissions.index');
    });


/* ══════════════════════════════════════════════════════════════════════════
|  10. CLIENT
|  Requiert : auth + role:client
|  Préfixe : /client  |  Nom : client.*
══════════════════════════════════════════════════════════════════════════ */

Route::middleware(['auth', 'role:client'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        /* Tableau de bord */
        Route::get('/dashboard', [ClientDashboard::class, 'index'])->name('dashboard');
        
            /* Hub de messagerie (page dédiée style WhatsApp) */
            Route::get('/messages', [ShopMessageController::class, 'hub'])->name('messages.hub');

        /* Message pour les clients */
            Route::get('/products/{product}/messages',  [ShopMessageController::class, 'index'])->name('messages.index');
            Route::post('/products/{product}/message',  [ShopMessageController::class, 'store'])->name('messages.store');

            /* Négociation de prix */
            Route::post('/messages/propose-price',            [ShopMessageController::class, 'proposePrice']) ->name('messages.propose');
            Route::post('/messages/confirm-offer/{message}',  [ShopMessageController::class, 'confirmOffer']) ->name('messages.confirm');
            Route::get('/messages/poll',                       [ShopMessageController::class, 'poll'])          ->name('messages.client.poll');
            Route::get('/notifications/poll',                  [ShopMessageController::class, 'pollAll'])       ->name('client.notifications.poll');

        /* Commandes classiques */
        Route::resource('orders', OrderController::class)->only(['index', 'store', 'create']);

        /* Commander depuis un produit spécifique */
        Route::get('/orders/create-from-product/{product}', [ClientOrderController::class, 'createFromProduct'])->name('orders.createFromProduct');
        Route::post('/orders/store-product',                 [ClientOrderController::class, 'storeProduct'])     ->name('orders.storeProduct');

        /* Avis sur une commande */
        Route::get('orders/{order}/review',  [ReviewController::class, 'create'])->name('reviews.create');
        Route::post('orders/{order}/review', [ReviewController::class, 'store'])  ->name('reviews.store');

        /* S'abonner à une boutique */
        Route::post('/shops/{shop}/subscribe', [ShopSubscriptionController::class, 'store'])->name('shops.subscribe');

        /* Voir les produits d'une boutique */
        Route::get('shops/{shop}', [\App\Http\Controllers\Client\ShopController::class, 'show'])->name('shops.show');

        Route::get('/produit/{product}', [ClientProductController::class, 'show'])
    ->name('products.show');
    });


/* ══════════════════════════════════════════════════════════════════════════
|  14. AUTH (routes générées par Laravel Breeze / Fortify)
══════════════════════════════════════════════════════════════════════════ */

require __DIR__.'/auth.php';