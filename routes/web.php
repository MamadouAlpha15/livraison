<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Employe\DashboardController as EmployeDashboard;
use App\Http\Controllers\Vendeur\DashboardController as VendeurDashboard;
use App\Http\Controllers\Livreur\DashboardController as LivreurDashboard;
use App\Http\Controllers\Client\DashboardController as ClientDashboard;
use App\Http\Controllers\Vendeur\ShopController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Vendeur\ProductController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Vendeur\OrderController as VendeurOrderController;
use App\Http\Controllers\Livreur\OrderController as LivreurOrderController;
use App\Http\Controllers\Employe\OrderController as EmployeOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Vendeur\ReviewController as VendeurReviewController;  // 🔹 Import du contrôleur Review
use App\Http\Controllers\Vendeur\PaymentController as VendeurPaymentController;  // 🔹 Import du contrôleur Payment
use App\Http\Controllers\Admin\StatController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\PublicShopController;
use App\Http\Controllers\Client\ShopSubscriptionController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

     Route::get('/notifications/read/{id}', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    


});

// SuperAdmin
Route::middleware(['auth', 'role:superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Gestion des boutiques
        Route::resource('shops', AdminShopController::class)->only(['index','update']);

    });


     // Admin BOUTIQUE (propriétaire de la boutique)
Route::middleware(['auth', 'role:admin'])
    ->prefix('boutique')
    ->name('boutique.')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Vendeur\ShopController::class, 'admin'])
            ->name('dashboard');
             Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index'); // Gestion des paiements
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index'); // Gestion des rapports
         Route::get('stats', [StatController::class, 'index'])->name('stats.index'); // Statistiques

        // Gestion de la boutique
        Route::resource('shops', \App\Http\Controllers\Vendeur\ShopController::class)->only(['create','store','index']);

        // Gestion des employés (vendeurs, livreurs)
        Route::resource('employees', \App\Http\Controllers\Vendeur\EmployeeController::class)->except(['show']);
        Route::get('orders', [EmployeOrderController::class, 'index'])->name('orders.index'); // Liste des commandes confirmées
        Route::put('orders/{order}/assign', [EmployeOrderController::class, 'assign'])->name('orders.assign'); // Assignation des commandes aux livreurs
        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index'); // Gestion des paiements
    });



    
// Livreur
Route::middleware(['auth', 'role:livreur'])
->prefix('livreur')->name('livreur.')
    ->group(function () {
        Route::get('/dashboard', [LivreurDashboard::class, 'index'])->name('dashboard');
        Route::get('orders', [LivreurOrderController::class, 'index'])->name('orders.index');
        Route::put('orders/{order}/start', [LivreurOrderController::class, 'start'])->name('orders.start');
        Route::put('orders/{order}/complete', [LivreurOrderController::class, 'complete'])->name('orders.complete');
});

// Employé
Route::middleware(['auth', 'role:employe,superadmin,admin'])
->prefix('employe')->name('employe.')
    ->group(function () {
        Route::get('/dashboard', [EmployeDashboard::class, 'index'])->name('dashboard'); // Tableau de bord employé
        Route::get('orders', [EmployeOrderController::class, 'index'])->name('orders.index'); // Liste des commandes confirmées
        Route::put('orders/{order}/assign', [EmployeOrderController::class, 'assign'])->name('orders.assign'); // Assignation des commandes aux livreurs
        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index'); // Gestion des paiements
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index'); // Gestion des rapports
         Route::get('stats', [StatController::class, 'index'])->name('stats.index'); // Statistiques
});

// Vendeur
Route::middleware(['auth', 'role:vendeur,admin'])->group(function () {
   
    Route::get('/vendeur/dashboard', [VendeurDashboard::class, 'index'])->name('vendeur.dashboard');
    Route::resource('shop', ShopController::class); // Gestion des boutiques
     Route::resource('products', ProductController::class)->except(['show']);

      Route::get('orders', [VendeurOrderController::class, 'index'])->name('orders.index');
        Route::put('orders/{order}/confirm', [VendeurOrderController::class, 'confirm'])->name('orders.confirm'); // Nouvelle route pour confirmer une commande
        Route::put('orders/{order}/cancel', [VendeurOrderController::class, 'cancel'])->name('orders.cancel'); // Nouvelle route pour annuler une commande
        Route::get('reviews', [VendeurReviewController::class, 'index'])->name('reviews.index'); // 🔹 Route pour les avis
         Route::get('payments', [VendeurPaymentController::class, 'index'])->name('payments.index'); // 🔹 Route pour les paiements
         // employés de SA boutique
    
    
});


// Client
Route::middleware(['auth', 'role:client'])
->prefix('client')->name('client.')
    ->group(function () {

        Route::get('/dashboard', [ClientDashboard::class, 'index'])->name('dashboard');

        Route::resource('orders', OrderController::class)->only(['index','store','create']);

         // Route pour laisser un avis sur une commande
        Route::get('orders/{order}/review', [ReviewController::class, 'create'])->name('reviews.create');
        Route::post('orders/{order}/review', [ReviewController::class, 'store'])->name('reviews.store');
        Route::post('/shops/{shop}/subscribe', [ShopSubscriptionController::class, 'store'])->name('shops.subscribe');

    // Flux commande PRODUIT (ajouts sans toucher ton store() existant)
    Route::get('/client/orders/create-from-product/{product}', [ClientOrderController::class, 'createFromProduct'])->name('orders.createFromProduct');
    Route::post('/client/orders/store-product', [ClientOrderController::class, 'storeProduct'])->name('orders.storeProduct');
    // 🔹 Voir les produits d’une boutique
        Route::get('shops/{shop}', [\App\Http\Controllers\Client\ShopController::class, 'show'])
            ->name('shops.show');


        
});
    
    


require __DIR__.'/auth.php';

// Routes publiques pour les boutiques
// Page d'accueil publique (welcome) remplace la closure actuelle
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Page publique des produits d'une boutique
Route::get('/shops/{shop}/products', [PublicShopController::class, 'products'])->name('public.shops.products');
