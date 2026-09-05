<?php
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:30,1'])
    ->post('/orders/{order}/position', [OrderTrackingController::class, 'update']);

/* ══════════════════════════════════════════════════════════════════════════
|  API v1 — app mobile Flutter (rôle client)
|  Toutes les routes ici sont neuves, séparées du site web (/api/v1/...),
|  et ne modifient rien à l'existant.
══════════════════════════════════════════════════════════════════════════ */
Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register',    [AuthController::class, 'register'])   ->name('register');
        Route::post('/verify-otp',  [AuthController::class, 'verifyOtp'])  ->name('verify-otp');
        Route::post('/resend-otp',  [AuthController::class, 'resendOtp'])  ->name('resend-otp');
        Route::post('/login',       [AuthController::class, 'login'])      ->name('login');

        // Connexion "Continuer avec Google" — le flux OAuth passe par
        // auth.google.redirect.mobile (routes/auth.php) ; ici seulement la suite
        // (finalisation d'un nouveau compte + page de secours du retour vers l'app).
        Route::prefix('google')->name('google.')->group(function () {
            Route::get('/return',   [\App\Http\Controllers\Api\V1\GoogleAuthController::class, 'returnFallback'])->name('return');
            Route::post('/complete',[\App\Http\Controllers\Api\V1\GoogleAuthController::class, 'complete']) ->name('complete');
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/me',      [AuthController::class, 'me'])    ->name('me');
        });
    });

    /* Catalogue — accessible sans compte (comme le site) : pas de middleware
       auth:sanctum ici (il bloquerait les invités). Les infos personnalisées
       (favoris, pays du compte) s'ajoutent automatiquement dans le contrôleur
       via $request->user('sanctum'), qui renvoie null sans jeton au lieu de
       rejeter la requête — voir Api\V1\ProductController/ShopController. */
    Route::get('/products',            [\App\Http\Controllers\Api\V1\ProductController::class, 'index'])      ->name('products.index');
    Route::get('/products/{product}',  [\App\Http\Controllers\Api\V1\ProductController::class, 'show'])       ->name('products.show');
    Route::get('/categories',          [\App\Http\Controllers\Api\V1\ProductController::class, 'categories']) ->name('categories.index');

    Route::get('/shops',                  [\App\Http\Controllers\Api\V1\ShopController::class, 'index'])    ->name('shops.index');
    Route::get('/shops/{shop}',           [\App\Http\Controllers\Api\V1\ShopController::class, 'show'])     ->name('shops.show');
    Route::get('/shops/{shop}/products',  [\App\Http\Controllers\Api\V1\ShopController::class, 'products']) ->name('shops.products');
    Route::get('/shops/{shop}/reviews',   [\App\Http\Controllers\Api\V1\ShopController::class, 'reviews'])  ->name('shops.reviews');

    /* Tableau de bord (accueil) — ventes flash, populaire par catégorie,
       recommandations. Accessible sans compte comme le reste du catalogue. */
    Route::get('/home', [\App\Http\Controllers\Api\V1\HomeController::class, 'index'])->name('home');

    // Commande directe ("Commander" sur une fiche produit) : possible sans compte
    // (invité), comme sur le site — donc PAS dans le groupe auth:sanctum, sinon
    // les invités seraient rejetés. Le contrôleur gère lui-même le cas connecté
    // via $request->user('sanctum'). Attention à l'ordre : doit être déclarée
    // AVANT /orders/{order} (dans le groupe protégé plus bas), sinon Laravel
    // essaierait de traiter "direct" comme un ID de commande.
    Route::post('/orders/direct',       [\App\Http\Controllers\Api\V1\OrderController::class, 'storeDirect'])   ->name('orders.direct');
    Route::post('/orders/check-promo',  [\App\Http\Controllers\Api\V1\OrderController::class, 'checkPromoCode'])->name('orders.check-promo');

    /* Panier & Commandes — nécessitent un compte (comme sur le site) */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cart',                    [\App\Http\Controllers\Api\V1\CartController::class, 'index'])          ->name('cart.index');
        Route::post('/cart/items',              [\App\Http\Controllers\Api\V1\CartController::class, 'add'])            ->name('cart.add');
        Route::patch('/cart/items/{item}',      [\App\Http\Controllers\Api\V1\CartController::class, 'updateQuantity']) ->name('cart.update');
        Route::delete('/cart/items/{item}',     [\App\Http\Controllers\Api\V1\CartController::class, 'remove'])         ->name('cart.remove');
        Route::post('/cart/checkout',           [\App\Http\Controllers\Api\V1\CartController::class, 'checkout'])       ->name('cart.checkout');

        Route::get('/orders',              [\App\Http\Controllers\Api\V1\OrderController::class, 'index']) ->name('orders.index');
        Route::get('/orders/{order}',      [\App\Http\Controllers\Api\V1\OrderController::class, 'show'])  ->name('orders.show');
        Route::post('/orders/{order}/review', [\App\Http\Controllers\Api\V1\ReviewController::class, 'store'])->name('orders.review');
        Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'show'])->name('orders.invoice');

        Route::post('/assistant/chat', [\App\Http\Controllers\Api\V1\AssistantController::class, 'chat'])->name('assistant.chat');

        Route::get('/notifications',                   [\App\Http\Controllers\Api\V1\NotificationController::class, 'index'])         ->name('notifications.index');
        Route::post('/notifications/mark-orders-seen',  [\App\Http\Controllers\Api\V1\NotificationController::class, 'markOrdersSeen'])->name('notifications.mark-orders-seen');

        Route::get('/loyalty', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'index'])->name('loyalty.index');

        Route::get('/favorites',                    [\App\Http\Controllers\Api\V1\FavoriteController::class, 'index'])  ->name('favorites.index');
        Route::post('/products/{product}/favorite', [\App\Http\Controllers\Api\V1\FavoriteController::class, 'toggle']) ->name('favorites.toggle');

        // Boutiques suivies (distinct du wishlist produit ci-dessus)
        Route::get('/shop-favorites',            [\App\Http\Controllers\Api\V1\ShopFavoriteController::class, 'index'])  ->name('shop-favorites.index');
        Route::post('/shops/{shop}/favorite',    [\App\Http\Controllers\Api\V1\ShopFavoriteController::class, 'toggle']) ->name('shop-favorites.toggle');

        Route::get('/messages',                      [\App\Http\Controllers\Api\V1\MessageController::class, 'index']) ->name('messages.index');
        Route::get('/products/{product}/messages',   [\App\Http\Controllers\Api\V1\MessageController::class, 'show'])  ->name('messages.show');
        Route::post('/products/{product}/messages',  [\App\Http\Controllers\Api\V1\MessageController::class, 'store']) ->name('messages.store');
        Route::post('/products/{product}/messages/images', [\App\Http\Controllers\Api\V1\MessageController::class, 'sendImages'])->name('messages.images');

        Route::post('/messages/propose-price',           [\App\Http\Controllers\Api\V1\MessageController::class, 'proposePrice'])  ->name('messages.propose-price');
        Route::post('/messages/counter-offer',            [\App\Http\Controllers\Api\V1\MessageController::class, 'counterOffer'])  ->name('messages.counter-offer');
        Route::post('/messages/{message}/confirm-offer',  [\App\Http\Controllers\Api\V1\MessageController::class, 'confirmOffer'])  ->name('messages.confirm-offer');
        Route::post('/messages/{message}/refuse-offer',   [\App\Http\Controllers\Api\V1\MessageController::class, 'refuseOffer'])   ->name('messages.refuse-offer');

        /* Notifications push — réutilise le même contrôleur que le site (déjà
           compatible FCM nativement, voir PushService::sendFcm). L'app Flutter
           envoie ici le jeton FCM du téléphone avec type=fcm. */
        Route::post('/push/subscribe',   [\App\Http\Controllers\PushSubscriptionController::class, 'store'])  ->name('push.subscribe');
        Route::post('/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
    });
});
