<?php

namespace App\Support;

use App\Http\Controllers\Vendeur\ShopOnboardingController;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * « Bienvenue WhatsApp » : message de bienvenue que l'équipe Shopio envoie à chaque nouvel inscrit.
 * Pas d'API WhatsApp : l'admin ouvre https://wa.me/<numéro>?text=<message> et appuie lui-même sur « Envoyer ».
 * Les messages se modifient dans Admin → Bienvenue WhatsApp (fichier storage/app/shopio/welcome-whatsapp.json).
 */
class WelcomeWhatsapp
{
    public const FILE = 'shopio/welcome-whatsapp.json';
    public const MAX = 1000;

    /** Messages par défaut (vouvoiement). Pas de lien vers l'académie : cette page n'existe pas dans Shopio. */
    public const DEFAULTS = [
        'vendor' => "Bonjour {prenom} 👋\n\nBienvenue sur Shopio ! Votre boutique « {boutique} » est créée : {lien_boutique}\n\nJe suis là pour vous aider à bien démarrer : ajouter vos produits, partager votre vitrine, recevoir vos premières commandes.\n\nAvez-vous besoin d’aide pour quelque chose ? Répondez simplement à ce message.",
        'client' => "Bonjour {prenom} 👋\n\nBienvenue sur Shopio ! Vous pouvez maintenant commander dans les boutiques et payer à la livraison.\n\nAvez-vous besoin d’aide pour votre première commande ? Répondez simplement à ce message, on est là.",
    ];

    /** Variables utilisables dans les messages. */
    public const VARS = [
        '{prenom}'        => 'Prénom de la personne',
        '{boutique}'      => 'Nom de la boutique (vendeurs)',
        '{lien_boutique}' => 'Lien de la vitrine (vendeurs)',
    ];

    /** Messages enregistrés par l'équipe, sinon les messages par défaut. */
    public static function texts(): array
    {
        try {
            $disk = Storage::disk('local');
            if ($disk->exists(self::FILE)) {
                $j = json_decode((string) $disk->get(self::FILE), true);
                if (is_array($j)) {
                    return [
                        'vendor' => is_string($j['vendor'] ?? null) && trim($j['vendor']) !== '' ? $j['vendor'] : self::DEFAULTS['vendor'],
                        'client' => is_string($j['client'] ?? null) && trim($j['client']) !== '' ? $j['client'] : self::DEFAULTS['client'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            // fichier illisible : messages par défaut
        }
        return self::DEFAULTS;
    }

    /** Texte nettoyé : sans balises ni \r, 1000 caractères au plus. */
    public static function clean(string $text): string
    {
        return Str::limit(trim(str_replace("\r", '', strip_tags($text))), self::MAX, '');
    }

    public static function save(string $vendor, string $client): void
    {
        Storage::disk('local')->put(self::FILE, json_encode(
            ['vendor' => self::clean($vendor), 'client' => self::clean($client)],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));
    }

    /** Retour aux messages par défaut. */
    public static function reset(): void
    {
        Storage::disk('local')->delete(self::FILE);
    }

    /**
     * Numéro international en chiffres seulement (ex. 224620000001), ou null.
     * « +… » ou « 00… » : l'indicatif est déjà là. Sinon, l'indicatif du pays du compte est ajouté quand la longueur
     * correspond au pays (ShopOnboardingController::COUNTRIES) ; en France, le 0 d'un numéro à 10 chiffres est retiré.
     */
    public static function number(?string $raw, ?string $country): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') return null;
        $compact = preg_replace('/\s+/', '', $raw);
        $digits = preg_replace('/\D/', '', $raw);

        if (str_starts_with($compact, '+')) {
            // indicatif déjà présent
        } elseif (str_starts_with($compact, '00')) {
            $digits = substr($digits, 2);
        } else {
            $code = strtoupper((string) $country);
            $c = ShopOnboardingController::COUNTRIES[$code] ?? null;
            if ($c) {
                [, $prefix, $min, $max] = $c;
                if ($code === 'FR' && strlen($digits) === 10 && $digits[0] === '0') $digits = substr($digits, 1);
                if (strlen($digits) >= $min && strlen($digits) <= $max) $digits = $prefix . $digits;
            }
        }
        $len = strlen($digits);
        return $len >= 8 && $len <= 15 ? $digits : null;
    }

    /**
     * La personne à contacter : kind (vendor | client), first (prénom), shop (boutique d'un vendeur),
     * number (téléphone de la boutique, sinon celui du compte).
     * @return array{kind: string, first: string, shop: ?Shop, number: ?string}
     */
    public static function contact(User $u): array
    {
        $vendor = $u->role === 'admin';
        $shop = $vendor ? ($u->shop ?? Shop::where('user_id', $u->id)->first()) : null;
        $number = null;
        if ($shop && trim((string) $shop->phone) !== '') $number = self::number($shop->phone, $shop->country ?: $u->country);
        if (!$number) $number = self::number($u->phone, $u->country ?: $shop?->country);
        return [
            'kind'   => $vendor ? 'vendor' : 'client',
            'first'  => trim(explode(' ', trim((string) $u->name))[0] ?? ''),
            'shop'   => $shop,
            'number' => $number,
        ];
    }

    /** Message personnalisé (variables remplacées). */
    public static function message(User $u, ?array $contact = null, ?array $texts = null): string
    {
        $c = $contact ?? self::contact($u);
        $t = ($texts ?? self::texts())[$c['kind']];
        $msg = strtr($t, [
            '{prenom}'        => $c['first'],
            '{boutique}'      => $c['shop']?->name ?? '',
            '{lien_boutique}' => $c['shop'] ? Storefront::publicUrl($c['shop']) : '',
        ]);
        return trim(preg_replace("/\n{3,}/", "\n\n", $msg));
    }

    /**
     * Lien WhatsApp avec le message prêt, ou null : pas de numéro, ou vendeur dont la boutique n'existe pas encore
     * (le message parle de sa boutique).
     */
    public static function link(User $u, ?array $contact = null, ?string $message = null): ?string
    {
        $c = $contact ?? self::contact($u);
        if (!$c['number'] || ($c['kind'] === 'vendor' && !$c['shop'])) return null;
        return 'https://wa.me/' . $c['number'] . '?text=' . rawurlencode($message ?? self::message($u, $c));
    }
}
