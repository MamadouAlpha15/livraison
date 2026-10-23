<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Essais de code OTP comptés par compte (et non par adresse IP) : changer d'IP
 * ne permet pas de deviner les 6 chiffres. Un nouveau code remet le compteur à zéro.
 */
class OtpAttempts
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 600;

    private static function key(User $user): string
    {
        return 'otp-verify:' . $user->id;
    }

    /** Message à afficher si le compte a épuisé ses essais, sinon null. */
    public static function blockedMessage(User $user): ?string
    {
        if (! RateLimiter::tooManyAttempts(self::key($user), self::MAX_ATTEMPTS)) {
            return null;
        }
        $minutes = (int) ceil(RateLimiter::availableIn(self::key($user)) / 60);
        return "Trop d'essais. Demandez un nouveau code ou réessayez dans {$minutes} min.";
    }

    public static function failed(User $user): void
    {
        RateLimiter::hit(self::key($user), self::DECAY_SECONDS);
    }

    public static function reset(User $user): void
    {
        RateLimiter::clear(self::key($user));
    }
}
