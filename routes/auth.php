<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/* ── Google OAuth ── */
Route::get('auth/google',          [GoogleController::class, 'redirect'])    ->name('google.redirect');
Route::get('auth/google/callback', [GoogleController::class, 'callback'])    ->name('google.callback');
Route::get('auth/google/setup',    [GoogleController::class, 'setup'])       ->name('google.setup');
Route::post('auth/google/setup',   [GoogleController::class, 'storeSetup']) ->middleware('throttle:10,1')->name('google.setup.store');

/* App mobile Flutter : même redirect_uri Google que le site (fixé côté Google
   Cloud Console), mais un flag en session fait bifurquer callback() vers un
   retour par jeton (Sanctum) + Android App Links au lieu d'une session web. */
Route::get('auth/google/mobile', [GoogleController::class, 'redirectMobile'])->name('google.redirect.mobile');

/* App Capacitor (webview du site) : le bouton Google, depuis l'app, ouvre CETTE
   route directement dans le navigateur externe (pas la webview) — tout le
   flux (redirect → Google → callback) reste alors dans le même navigateur, et
   la session PHP survit normalement le temps du flux. Le retour vers l'app se
   fait ensuite par une URL signée (pas par cookie/session, puisque le
   navigateur externe et la webview de l'app ont chacun leur propre stockage) :
   Android App Links intercepte cette URL et la webview de l'app la charge
   elle-même, ce qui établit enfin la session DANS l'app. */
Route::get('auth/google/app', [GoogleController::class, 'redirectCapacitor'])->name('google.redirect.app');
Route::get('auth/mobile-bridge',       [GoogleController::class, 'mobileBridge'])      ->name('google.mobile-bridge')->middleware('signed');
Route::get('auth/mobile-bridge/setup', [GoogleController::class, 'mobileBridgeSetup']) ->name('google.mobile-bridge-setup')->middleware('signed');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:10,10');

    Route::get('verify-otp', [OtpVerificationController::class, 'show'])
        ->name('verification.otp.show');

    Route::post('verify-otp', [OtpVerificationController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('verification.otp.verify');

    Route::post('verify-otp/resend', [OtpVerificationController::class, 'resend'])
        ->middleware('throttle:5,10')
        ->name('verification.otp.resend');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
