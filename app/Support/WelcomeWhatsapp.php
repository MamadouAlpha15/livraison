<?php

namespace App\Support;

use App\Http\Controllers\Vendeur\ShopOnboardingController;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Message de bienvenue envoyé sur WhatsApp aux nouveaux inscrits (Admin → Bienvenue WhatsApp).
 * L'envoi se fait à la main, en un clic : le lien ouvre WhatsApp avec le message déjà écrit, l'équipe
 * appuie sur « Envoyer ». Deux messages modifiables (vendeurs, clients), avec des variables remplacées
 * pour chaque personne : {prenom}, {boutique}, {lien_boutique}, {academie}.
 */
class WelcomeWhatsapp
{
    private const FILE = 'shopio/welcome-whatsapp.json';
    public const MAX = 1000;
    public const VARS = [
        '{prenom}'        => 'Prénom de la personne',
        '{boutique}'      => 'Nom de sa boutique (vendeurs)',
        '{lien_boutique}' => 'Adresse de sa vitrine (vendeurs)',
        '{academie}'      => 'Lien vers l’Académie Shopio (guides)',
    ];

    public const DEFAULTS = [
        'vendor' => "Bonjour {prenom} 👋\n\nBienvenue sur Shopio ! Votre boutique « {boutique} » est créée : {lien_boutique}\n\nJe suis là pour vous aider à bien démarrer : ajouter vos produits, partager votre vitrine, recevoir vos premières commandes.\n\nAvez-vous besoin d’aide pour quelque chose ? Répondez simplement à ce message.\n\nNos guides gratuits : {academie}",
        'client' => "Bonjour {prenom} 👋\n\nBienvenue sur Shopio ! Vous pouvez maintenant commander dans les boutiques et payer à la livraison.\n\nAvez-vous besoin d’aide pour votre première commande ? Répondez simplement à ce message, on est là.",
    ];

    /** Messages enregistrés par l'équipe, sinon ceux par défaut. */
    public static function texts(): array
    {
        $saved = [];
        try {
            if (Storage::disk('local')->exists(self::FILE)) $saved = json_decode(Storage::disk('local')->get(self::FILE), true) ?: [];
        } catch (\Throwable $e) {}
        return [
            'vendor' => is_string($saved['vendor'] ?? null) && trim($saved['vendor']) !== '' ? $saved['vendor'] : self::DEFAULTS['vendor'],
            'client' => is_string($saved['client'] ?? null) && trim($saved['client']) !== '' ? $saved['client'] : self::DEFAULTS['client'],
        ];
    }

    public static function save(string $vendor, string $client): void
    {
        $clean = fn ($t) => mb_substr(trim(str_replace("\r", '', strip_tags($t))), 0, self::MAX);
        Storage::disk('local')->put(self::FILE, json_encode(['vendor' => $clean($vendor), 'client' => $clean($client)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * Numéro WhatsApp au format international (chiffres seulement, ex. 224620000000), ou null s'il manque
     * ou ne ressemble pas à un numéro. Les numéros saisis sans indicatif prennent celui du pays du compte.
     */
    public static function number(?string $raw, ?string $country): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') return null;
        $plus = str_starts_with($raw, '+') || str_starts_with($raw, '00');
        $d = preg_replace('/\D/', '', $raw);
        if (str_starts_with($d, '00')) $d = substr($d, 2);
        if (!$plus) {
            $c = ShopOnboardingController::COUNTRIES[strtoupper((string) $country)] ?? null;
            if ($c) {
                [, $dial, $min, $max] = $c;
                if (strtoupper((string) $country) === 'FR' && strlen($d) === 10 && $d[0] === '0') $d = substr($d, 1);
                if (strlen($d) >= $min && strlen($d) <= $max) $d = $dial . $d;
            }
        }
        return strlen($d) >= 8 && strlen($d) <= 15 ? $d : null;
    }

    /** La personne à contacter : sorte (vendor | client), prénom, numéro, boutique. */
    public static function contact(User $u): array
    {
        $vendor = $u->role === 'admin';
        $shop = $vendor ? $u->shop : null;
        $first = trim(explode(' ', trim((string) $u->name))[0] ?? '') ?: 'et bienvenue';
        return [
            'kind'   => $vendor ? 'vendor' : 'client',
            'first'  => $first,
            'shop'   => $shop,
            'number' => self::number($shop?->phone ?: $u->phone, $shop?->country ?: $u->country),
        ];
    }

    /** Message prêt à envoyer à cette personne (variables remplacées). */
    public static function message(User $u, ?array $texts = null): string
    {
        $c = self::contact($u);
        $texts ??= self::texts();
        $shopUrl = $c['shop'] ? Storefront::publicUrl($c['shop']) : '';
        $msg = strtr($texts[$c['kind']], [
            '{prenom}' => $c['first'],
            '{boutique}' => $c['shop']->name ?? 'votre boutique',
            '{lien_boutique}' => $shopUrl,
            '{academie}' => route('academy.index'),
        ]);
        // Ligne vide laissée par une variable sans valeur (ex. pas encore de vitrine)
        return trim(preg_replace("/[ \t]+\n/", "\n", preg_replace("/\n{3,}/", "\n\n", $msg)));
    }

    /** Lien qui ouvre WhatsApp sur la conversation, message déjà écrit. Null sans numéro. */
    public static function link(User $u, ?array $texts = null): ?string
    {
        $n = self::contact($u)['number'];
        return $n ? 'https://wa.me/' . $n . '?text=' . rawurlencode(self::message($u, $texts)) : null;
    }
}
