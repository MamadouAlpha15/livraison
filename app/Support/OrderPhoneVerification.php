<?php

namespace App\Support;

use App\Models\Shop;
use App\Support\Sms\NimbaSmsSender;
use App\Support\Sms\SmsSender;
use Illuminate\Support\Facades\Cache;

/**
 * Code SMS demandé avant une commande payée à la livraison, selon le réglage du formulaire de commande :
 * jamais, au-dessus d'un montant, pour les numéros à risque (CustomerRisk) ou toujours.
 * Seulement quand un vrai fournisseur SMS est branché (Nimba) : sans lui, personne ne recevrait le code
 * et aucune commande ne passerait, donc la vérification est ignorée.
 * Code à 6 chiffres, valable 10 minutes, 5 essais ; un envoi par minute et 5 par heure au plus par numéro.
 */
class OrderPhoneVerification
{
    public const MODES = [
        'never'  => 'Jamais',
        'risky'  => 'Seulement les numéros à risque',
        'above'  => 'Au-dessus d\'un montant',
        'always' => 'Toujours',
    ];

    private const TTL = 600;
    private const RESEND_AFTER = 60;
    private const SENDS_PER_HOUR = 5;
    private const MAX_ATTEMPTS = 5;

    public static function available(): bool
    {
        return NimbaSmsSender::configured();
    }

    /** Faut-il un code pour cette commande ? ($form : réglages du bloc Formulaire de commande) */
    public static function required(array $form, int $total, string $phone): bool
    {
        if (!self::available()) {
            return false;
        }
        return match ($form['verify_phone'] ?? 'never') {
            'always' => true,
            'above'  => $total >= (int) ($form['verify_above'] ?? 0),
            'risky'  => in_array(CustomerRisk::forPhone($phone)['level'], ['risky', 'watch'], true),
            default  => false,
        };
    }

    /**
     * Envoie un code (sauf si un code vient d'être envoyé). Renvoie [statut HTTP, réponse JSON] :
     * 202 code envoyé ou déjà envoyé, 429 trop d'envois, 503 envoi impossible.
     */
    public static function send(Shop $shop, string $phone, int $productId): array
    {
        $key = self::key($phone, $productId);
        $state = Cache::get($key);
        $masked = PhoneKey::masked($phone);

        if ($state && now()->getTimestamp() - $state['sent_at'] < self::RESEND_AFTER) {
            return [202, ['otp_required' => true, 'message' => "Un code t'a déjà été envoyé au {$masked}. Saisis-le ci-dessous (nouvel envoi possible dans une minute)."]];
        }
        $hourKey = 'order-otp-sends:' . PhoneKey::of($phone);
        if ((int) Cache::get($hourKey, 0) >= self::SENDS_PER_HOUR) {
            return [429, ['message' => 'Trop de codes envoyés à ce numéro. Réessaie dans une heure ou appelle la boutique.']];
        }

        $code = (string) random_int(100000, 999999);
        $sent = app(SmsSender::class)->send($phone, "Shopio : ton code pour confirmer ta commande chez {$shop->name} est {$code}. Il expire dans 10 minutes. Ne le donne à personne.");
        if (!$sent) {
            return [503, ['message' => 'Le SMS de confirmation n\'a pas pu partir. Vérifie ton numéro ou réessaie dans un instant.']];
        }
        Cache::put($key, ['hash' => self::hash($code, $key), 'sent_at' => now()->getTimestamp(), 'attempts' => 0], self::TTL);
        Cache::put($hourKey, (int) Cache::get($hourKey, 0) + 1, 3600);

        return [202, ['otp_required' => true, 'message' => "Nous avons envoyé un code par SMS au {$masked}. Saisis-le pour confirmer ta commande."]];
    }

    /** null si le code est bon (il est alors consommé), sinon le message d'erreur */
    public static function check(string $phone, int $productId, string $code): ?string
    {
        $key = self::key($phone, $productId);
        $state = Cache::get($key);
        if (!$state) {
            return 'Ce code a expiré : demande un nouveau code.';
        }
        if ($state['attempts'] >= self::MAX_ATTEMPTS) {
            return 'Trop d\'essais : demande un nouveau code.';
        }
        if (!hash_equals($state['hash'], self::hash(preg_replace('/\D/', '', $code), $key))) {
            $state['attempts']++;
            Cache::put($key, $state, max(1, self::TTL - (now()->getTimestamp() - $state['sent_at'])));
            return 'Code incorrect. Vérifie le SMS reçu (' . (self::MAX_ATTEMPTS - $state['attempts']) . ' essai(s) restant(s)).';
        }
        Cache::forget($key);
        return null;
    }

    private static function key(string $phone, int $productId): string
    {
        return 'order-otp:' . PhoneKey::of($phone) . ':' . $productId;
    }

    private static function hash(string $code, string $key): string
    {
        return hash_hmac('sha256', $code . '|' . $key, (string) config('app.key'));
    }
}
