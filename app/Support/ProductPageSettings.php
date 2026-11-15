<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Shop;

/**
 * Page produit personnalisable par le vendeur.
 *   - Réglages de la boutique (shops.product_page) : bandeau, couleur du bouton, badges de confiance,
 *     étapes « Comment ça se passe », questions fréquentes, frais de livraison par commune.
 *   - Réglages du produit (products.page) : offres groupées, texte et points forts, urgence de stock,
 *     bouton « Faire une offre », questions du produit.
 * Les prix des offres sont lus ici, côté serveur, au moment de la commande : jamais repris du navigateur.
 */
class ProductPageSettings
{
    public const MAX_OFFERS = 4;
    public const MAX_POINTS = 6;
    public const MAX_FAQ = 6;

    /** Couleurs proposées pour le bouton « Commander » (bleu Shopio par défaut) */
    public const ACCENTS = ['#1F7CF6' => 'Bleu Shopio', '#F59E0B' => 'Ambre', '#C8F135' => 'Citron', '#16A34A' => 'Vert', '#E11D48' => 'Framboise', '#0B1E3D' => 'Bleu nuit'];

    /**
     * Modèles de page prêts à l'emploi : ordre des sections, ce qui est mis en avant et style.
     * Inspirés des pages de vente à la livraison qui convertissent le mieux. Aucun n'invente d'urgence :
     * le compte à rebours n'apparaît que pendant une vraie vente flash, le stock affiché est le vrai.
     */
    public const TEMPLATES = [
        'classique' => [
            'name'     => 'Classique',
            'for'      => 'La plupart des produits',
            'pitch'    => 'Offres groupées, puis le formulaire de commande juste en dessous. Le modèle de départ.',
            'sections' => ['variants', 'offers', 'order', 'trust', 'why', 'steps', 'reviews', 'faq', 'guarantee', 'whatsapp'],
        ],
        'express' => [
            'name'     => 'Express',
            'for'      => 'Pubs Facebook et TikTok, petits prix',
            'pitch'    => 'Le formulaire arrive tout de suite sous le prix : le client commande en 20 secondes, sans défiler.',
            'sections' => ['offers', 'variants', 'order', 'trust', 'why', 'faq', 'whatsapp'],
        ],
        'confiance' => [
            'name'     => 'Confiance',
            'for'      => 'Produits chers : téléphones, montres, parfums',
            'pitch'    => 'Garanties, avis et réponses aux doutes d\'abord, la commande ensuite : rassure avant de vendre.',
            'sections' => ['trust', 'guarantee', 'reviews', 'why', 'variants', 'offers', 'order', 'steps', 'faq', 'whatsapp'],
        ],
        'promo' => [
            'name'     => 'Promo',
            'for'      => 'Ventes flash, déstockage, prix barrés',
            'pitch'    => 'L\'économie en grand, le vrai compte à rebours de la vente flash et les lots mis en avant.',
            'sections' => ['countdown', 'offers', 'variants', 'order', 'trust', 'why', 'faq', 'whatsapp'],
        ],
        'histoire' => [
            'name'     => 'Histoire',
            'for'      => 'Beauté, bien-être, produits à expliquer',
            'pitch'    => 'Le texte et les bienfaits racontés en grand, puis les étapes et les avis, et la commande pour finir.',
            'sections' => ['why', 'steps', 'reviews', 'variants', 'offers', 'order', 'trust', 'faq', 'guarantee', 'whatsapp'],
        ],
    ];

    public const BADGES = [
        'cod'      => ['label' => 'Paiement à la livraison', 'icon' => 'wallet'],
        'tracking' => ['label' => 'Livraison suivie en direct', 'icon' => 'map-pin'],
        'verified' => ['label' => 'Boutique vérifiée', 'icon' => 'shield'],
    ];

    /** Réglages de départ de la boutique (uniquement des faits vrais sur Shopio) */
    public static function shopDefaults(): array
    {
        return [
            'banner'        => 'Paiement à la livraison · Commande en 30 secondes',
            'accent'        => '#1F7CF6',
            'badges'        => ['cod', 'tracking', 'verified'],
            'steps'         => [
                'Tu remplis le formulaire en 30 secondes',
                'La boutique te confirme par appel ou WhatsApp',
                'Tu suis le livreur sur la carte',
                'Tu paies à la réception, en main propre',
            ],
            'faq'           => [
                ['q' => 'Quand vais-je recevoir ma commande ?', 'a' => 'La boutique t\'appelle ou t\'écrit sur WhatsApp juste après ta commande pour confirmer le jour de livraison.'],
                ['q' => 'Dois-je payer avant la livraison ?', 'a' => 'Non. Tu paies en espèces au livreur, à la réception du colis.'],
                ['q' => 'Puis-je négocier le prix ?', 'a' => 'Oui : touche « Faire une offre au vendeur » et propose ton prix. Le vendeur accepte, refuse ou te fait une contre-offre.'],
            ],
            'delivery_fees' => [],   // commune => prix en GNF (vide : « à confirmer par la boutique »)
            'show_offer_button' => true,
            'template'      => 'classique',
            'guarantee'     => null,  // ex. « Échange sous 3 jours si le produit ne te convient pas » (vide : pas affiché)
            'whatsapp'      => false, // bouton « Une question ? WhatsApp » vers le téléphone de la boutique
        ];
    }

    public static function productDefaults(): array
    {
        return [
            'offers'          => [],  // [{key, label, subtitle, qty, price, badge}]
            'intro'           => null, // vide : description du produit
            'points'          => [],
            'urgency'         => true,
            'urgency_under'   => 10,
            'faq'             => [],
            'show_offer_button' => null, // null : réglage de la boutique
            'template'        => null, // null : modèle de la boutique
        ];
    }

    public static function shop(Shop $shop): array
    {
        $saved = is_array($shop->product_page) ? $shop->product_page : (json_decode((string) $shop->product_page, true) ?: []);
        return array_replace(self::shopDefaults(), array_intersect_key($saved, self::shopDefaults()));
    }

    public static function product(Product $product): array
    {
        $saved = is_array($product->page) ? $product->page : (json_decode((string) $product->page, true) ?: []);
        return array_replace(self::productDefaults(), array_intersect_key($saved, self::productDefaults()));
    }

    /**
     * Offres affichées : celles du vendeur, sinon une seule offre « 1 article » au prix actuel.
     * Chaque offre : key, label, subtitle, qty, price (total en GNF), badge, savings (économie réelle).
     */
    public static function offers(Product $product): array
    {
        $unit = (float) $product->current_price;
        $offers = self::product($product)['offers'];
        if ($offers === []) {
            $offers = [['key' => 'un', 'label' => '1 ' . ($product->unit ?: 'article'), 'subtitle' => $product->name, 'qty' => 1, 'price' => (int) round($unit), 'badge' => null]];
        }
        return array_map(function ($o) use ($unit) {
            $qty = max(1, (int) $o['qty']);
            $price = max(0, (int) $o['price']);
            return [
                'key'      => (string) $o['key'],
                'label'    => (string) $o['label'],
                'subtitle' => $o['subtitle'] ?? null,
                'qty'      => $qty,
                'price'    => $price,
                'badge'    => $o['badge'] ?? null,
                'savings'  => max(0, (int) round($unit * $qty) - $price),
            ];
        }, array_values($offers));
    }

    public static function findOffer(Product $product, ?string $key): ?array
    {
        foreach (self::offers($product) as $o) {
            if ($o['key'] === $key) {
                return $o;
            }
        }
        return null;
    }

    /** Offre mise en avant : celle qui a un badge, sinon la première */
    public static function defaultOffer(array $offers): array
    {
        foreach ($offers as $o) {
            if (filled($o['badge'])) {
                return $o;
            }
        }
        return $offers[0];
    }

    /** Frais de livraison d'une commune (null : à confirmer par la boutique) */
    public static function deliveryFee(Shop $shop, ?string $commune): ?int
    {
        $fees = self::shop($shop)['delivery_fees'];
        return $commune !== null && isset($fees[$commune]) && $fees[$commune] !== '' && $fees[$commune] !== null ? (int) $fees[$commune] : null;
    }

    /** Modèle utilisé : aperçu demandé (?modele=), sinon celui du produit, sinon celui de la boutique */
    public static function template(Shop $shop, Product $product, ?string $preview = null): string
    {
        foreach ([$preview, self::product($product)['template'], self::shop($shop)['template']] as $key) {
            if ($key !== null && isset(self::TEMPLATES[$key])) {
                return $key;
            }
        }
        return 'classique';
    }

    /** Lien WhatsApp de la boutique (null si pas de téléphone ou bouton désactivé) */
    public static function whatsappUrl(Shop $shop, Product $product): ?string
    {
        return self::shop($shop)['whatsapp'] ? self::whatsappLink($shop, $product) : null;
    }

    /** Lien WhatsApp vers le téléphone de la boutique avec un message sur le produit (null sans téléphone) */
    public static function whatsappLink(Shop $shop, Product $product): ?string
    {
        if (blank($shop->phone)) {
            return null;
        }
        $phone = preg_replace('/\D/', '', $shop->phone);
        if (strlen($phone) === 9) {
            $phone = '224' . $phone;
        }
        return 'https://wa.me/' . $phone . '?text=' . rawurlencode('Bonjour ' . $shop->name . ', j\'ai une question sur « ' . $product->name . ' » : ' . route('client.products.show', $product));
    }

    public static function showOfferButton(Shop $shop, Product $product): bool
    {
        $own = self::product($product)['show_offer_button'];
        return $own === null ? (bool) self::shop($shop)['show_offer_button'] : (bool) $own;
    }

    /** Questions fréquentes : celles du produit d'abord, puis celles de la boutique */
    public static function faq(Shop $shop, Product $product): array
    {
        return array_values(array_filter(array_merge(self::product($product)['faq'], self::shop($shop)['faq']),
            fn ($f) => filled($f['q'] ?? null) && filled($f['a'] ?? null)));
    }

    /**
     * Couleur d'accent lisible sur le bleu nuit de la carte de commande (#0B1E3D) : la couleur du vendeur,
     * éclaircie vers le blanc jusqu'à un contraste d'au moins 4,5:1 (ex. bleu Shopio → bleu ciel).
     */
    public static function accentOnNight(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return '#7DB4FF';
        }
        $rgb = array_map('hexdec', str_split($hex, 2));
        for ($mix = 0; $mix <= 100; $mix += 5) {
            $c = array_map(fn ($v) => (int) round($v + (255 - $v) * $mix / 100), $rgb);
            if (self::contrast($c, [11, 30, 61]) >= 4.5) {
                return sprintf('#%02X%02X%02X', ...$c);
            }
        }
        return '#FFFFFF';
    }

    private static function luminance(array $rgb): float
    {
        $lin = fn ($c) => ($c /= 255) <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        return 0.2126 * $lin($rgb[0]) + 0.7152 * $lin($rgb[1]) + 0.0722 * $lin($rgb[2]);
    }

    private static function contrast(array $a, array $b): float
    {
        [$l1, $l2] = [self::luminance($a), self::luminance($b)];
        return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
    }

    /** Texte lisible sur la couleur du bouton (noir sur couleur claire, blanc sur couleur foncée) */
    public static function onAccent(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return '#16162A';
        }
        [$r, $g, $b] = array_map(fn ($c) => hexdec($c) / 255, str_split($hex, 2));
        $lin = fn ($c) => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        $lum = 0.2126 * $lin($r) + 0.7152 * $lin($g) + 0.0722 * $lin($b);
        return $lum > 0.35 ? '#16162A' : '#FFFFFF';
    }
}
