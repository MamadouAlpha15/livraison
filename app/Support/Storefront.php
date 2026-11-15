<?php

namespace App\Support;

use App\Models\Shop;

/**
 * Réglages de la vitrine publique d'une boutique (« Ma vitrine »).
 *
 * La vitrine s'affiche toujours avec un des deux thèmes de Shopio (PageModels : Express ou Signature).
 * L'ancienne apparence (blocs, polices, fonds, mises en page Luxe/Mode…, thèmes Tabaski/Ramadan,
 * collections) n'existe plus : ces anciennes clés sont ignorées et disparaissent au prochain enregistrement.
 *
 * Stockée en JSON dans shops.storefront. Toute entrée (publication, aperçu, base) passe par
 * normalize() : seules les clés, types et valeurs connus sont gardés, les textes sont nettoyés
 * et bornés, le reste retombe sur les valeurs par défaut.
 *
 *  - page_model : thème de la vitrine (express ou signature).
 *  - home       : sections de la page d'accueil, une liste par thème (Boutique → Sections).
 *  - form       : formulaire de commande de la page produit (éditeur produit → Formulaire).
 *  - cover      : couverture (diapos, hauteur, voile, slogan) ; logo : forme, position, taille.
 *  - settings   : texte des boutons, options des fiches, mode vacances, preuves de confiance.
 *  La couleur principale reste dans shops.accent_color.
 */
class Storefront
{
    // ─────────────────── Couverture et logo (menu Boutique → Couverture et logo)
    public const COVER_SOURCES = ['default' => 'Bannière actuelle', 'product' => 'Photo produit', 'night' => 'Dégradé nuit', 'gold' => 'Dégradé or', 'blue' => 'Dégradé bleu', 'photo' => 'Ma photo'];
    public const COVER_GRADIENTS = [
        'default' => 'radial-gradient(120% 140% at 100% 0%, rgba(31,124,246,.35), transparent 55%), linear-gradient(135deg, #0B1E3D, #132B52)',
        'night'   => 'linear-gradient(135deg, #061226 0%, #0B1E3D 50%, #1F3F75 100%)',
        'gold'    => 'linear-gradient(135deg, #2E2205 0%, #7A5A0E 45%, #D4A017 100%)',
        'blue'    => 'linear-gradient(135deg, #1260C9 0%, #1F7CF6 55%, #7DB4FF 100%)',
    ];
    public const COVER_HEIGHTS = ['l' => 'Grande', 'm' => 'Moyenne', 's' => 'Fine'];
    public const SLIDES_MAX = 4;
    public const LOGO_SHAPES = ['round' => 'Rond', 'rounded' => 'Arrondi', 'square' => 'Carré'];
    public const LOGO_POSITIONS = ['left' => 'À gauche', 'center' => 'Centré', 'over' => 'Sur la photo'];
    /** Couleurs proposées à côté du nom (onglet Couverture et logo). */
    public const IDENTITY_COLORS = ['#1F7CF6' => 'Bleu Shopio', '#0B1E3D' => 'Bleu nuit', '#F59E0B' => 'Ambre', '#15803D' => 'Vert'];

    /** Dernières publications gardées (shops.storefront_history). */
    public const HISTORY_MAX = 10;

    /** Adresses réservées (pages du site ou noms trompeurs). */
    public const RESERVED_SLUGS = ['admin', 'shopio', 'boutique', 'boutiques', 'shop', 'shops', 'support', 'api', 'login', 'register', 'client', 'vendeur', 'livreur', 'produit', 'produits', 'products', 'aide', 'help', 'contact', 'compte', 'panier', 'cart', 'suivi', 'marketing', 'www', 'app'];

    /** Couvertures importées (avec une variante « large » de 1600 px). */
    public const COVER_PATH = '#^covers/medium/[A-Za-z0-9]{20}\.webp$#';

    // ─────────────────────────────────────────────────────────────── Valeurs par défaut

    public static function defaults(): array
    {
        return [
            'page_model' => PageModels::DEFAULT,
            'home'       => HomeSections::normalizeAll(null),
            'form'       => ProductDesign::formDefaults(),
            'cover'      => self::coverDefaults(),
            'logo'       => self::logoDefaults(),
            'settings'   => self::settingsDefaults(),
            'orders'     => OrderBoard::settings([])['orders'],
            'carts'      => OrderBoard::settings([])['carts'],
        ];
    }

    /**
     * Couverture : « on » = réglée dans le menu Boutique (sinon l'en-tête d'origine est gardé).
     * slides : 1 à 4 diapos [src, img (photo importée), pid (produit dont on montre la photo)].
     */
    public static function coverDefaults(): array
    {
        return ['on' => false, 'slides' => [['src' => 'default', 'img' => null, 'pid' => null]], 'y' => 50, 'veil' => 30, 'height' => 'l', 'name_on' => true, 'slogan' => ''];
    }

    public static function logoDefaults(): array
    {
        return ['src' => 'current', 'shape' => 'round', 'pos' => 'left', 'size' => 104];
    }

    public static function settingsDefaults(): array
    {
        return [
            'btn_text' => 'Commander', 'show_stock' => true, 'show_strike' => true, 'show_nego' => false,
            'vacation' => false, 'vacation_msg' => 'De retour très bientôt',
            'welcome' => false, 'welcome_promo_id' => null,
            // Onglet Conversion : commande envoyée aussi sur WhatsApp, preuves de confiance affichables
            'wa_mode' => false, 'proof_delivered' => true, 'proof_response' => true,
        ];
    }

    // ─────────────────────────────────────────────────────────────── Nettoyage

    /** Nettoie une configuration quelconque (base, publication, aperçu). */
    public static function normalize($input): array
    {
        if (!is_array($input)) return self::defaults();

        return [
            // Thème de la vitrine (voir App\Support\PageModels) : Express par défaut, ou Signature.
            // Un ancien modèle supprimé (Standard, Prestige, Pop…) revient à Express.
            'page_model' => in_array($input['page_model'] ?? null, array_keys(PageModels::MODELS), true) ? $input['page_model'] : PageModels::DEFAULT,
            // Sections de la page d'accueil, une liste par thème (Boutique → Sections)
            'home'       => HomeSections::normalizeAll($input['home'] ?? null),
            // Formulaire de commande de la page produit, pour toute la boutique (éditeur produit → Formulaire)
            'form'       => ProductDesign::normalizeForm($input['form'] ?? null),
            'cover'      => self::normalizeCover($input['cover'] ?? null),
            'logo'       => self::normalizeLogo($input['logo'] ?? null),
            'settings'   => self::normalizeSettings($input['settings'] ?? null),
            // Commandes : message WhatsApp de confirmation ; paniers abandonnés (Commandes → Paniers abandonnés)
            'orders'     => OrderBoard::settings($input)['orders'],
            'carts'      => OrderBoard::settings($input)['carts'],
        ];
    }

    public static function normalizeCover($c): array
    {
        $d = self::coverDefaults();
        $c = is_array($c) ? $c : [];
        $slides = [];
        foreach (is_array($c['slides'] ?? null) ? $c['slides'] : [] as $s) {
            if (!is_array($s) || count($slides) >= self::SLIDES_MAX) continue;
            $src = in_array($s['src'] ?? null, array_keys(self::COVER_SOURCES), true) ? $s['src'] : null;
            $img = is_string($s['img'] ?? null) && preg_match(self::COVER_PATH, $s['img']) ? $s['img'] : null;
            if (!$src || ($src === 'photo' && !$img)) continue;
            $pid = (int) ($s['pid'] ?? 0);
            $slides[] = ['src' => $src, 'img' => $src === 'photo' ? $img : null, 'pid' => $src === 'product' && $pid > 0 ? $pid : null];
        }
        $int = fn ($k, $min, $max) => max($min, min($max, (int) ($c[$k] ?? $d[$k])));
        return [
            'on'      => filter_var($c['on'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'slides'  => $slides ?: $d['slides'],
            'y'       => $int('y', 0, 100),
            'veil'    => $int('veil', 0, 70),
            'height'  => in_array($c['height'] ?? null, array_keys(self::COVER_HEIGHTS), true) ? $c['height'] : $d['height'],
            'name_on' => array_key_exists('name_on', $c) ? filter_var($c['name_on'], FILTER_VALIDATE_BOOLEAN) : $d['name_on'],
            'slogan'  => self::line($c['slogan'] ?? '', 60),
        ];
    }

    public static function normalizeLogo($l): array
    {
        $d = self::logoDefaults();
        $l = is_array($l) ? $l : [];
        return [
            'src'   => in_array($l['src'] ?? null, ['current', 'initials'], true) ? $l['src'] : $d['src'],
            'shape' => in_array($l['shape'] ?? null, array_keys(self::LOGO_SHAPES), true) ? $l['shape'] : $d['shape'],
            'pos'   => in_array($l['pos'] ?? null, array_keys(self::LOGO_POSITIONS), true) ? $l['pos'] : $d['pos'],
            'size'  => max(64, min(140, (int) ($l['size'] ?? $d['size']))),
        ];
    }

    public static function normalizeSettings($s): array
    {
        $d = self::settingsDefaults();
        $s = is_array($s) ? $s : [];
        $bool = fn ($k) => array_key_exists($k, $s) ? filter_var($s[$k], FILTER_VALIDATE_BOOLEAN) : $d[$k];
        $btn = self::line($s['btn_text'] ?? '', 20);
        $promo = (int) ($s['welcome_promo_id'] ?? 0);
        return [
            'btn_text'         => $btn !== '' ? $btn : $d['btn_text'],
            'show_stock'       => $bool('show_stock'),
            'show_strike'      => $bool('show_strike'),
            'show_nego'        => $bool('show_nego'),
            'vacation'         => $bool('vacation'),
            'vacation_msg'     => self::line($s['vacation_msg'] ?? $d['vacation_msg'], 120),
            'welcome'          => $bool('welcome'),
            'welcome_promo_id' => $promo > 0 ? $promo : null,
            'wa_mode'          => $bool('wa_mode'),
            'proof_delivered'  => $bool('proof_delivered'),
            'proof_response'   => $bool('proof_response'),
        ];
    }

    private static function line($v, int $max): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $v))), 0, $max);
    }

    // ─────────────────────────────────────────────────────────────── Accès

    /** Configuration publiée d'une boutique (ou valeurs par défaut). */
    public static function forShop(Shop $shop): array
    {
        return self::normalize($shop->storefront);
    }

    public static function settings(Shop $shop): array
    {
        return self::forShop($shop)['settings'];
    }

    /** Boutique en pause (mode vacances) : visible, mais les commandes sont refusées. */
    public static function onVacation(?Shop $shop): bool
    {
        return $shop ? (bool) (self::normalizeSettings($shop->storefront['settings'] ?? null)['vacation']) : false;
    }

    public static function vacationMessage(Shop $shop): string
    {
        $msg = self::normalizeSettings($shop->storefront['settings'] ?? null)['vacation_msg'];
        return 'La boutique ' . $shop->name . ' est en pause' . ($msg !== '' ? ' : ' . $msg : '') . '. Les commandes reprendront à son retour.';
    }

    // ─────────────────────────────────────────────────────────────── Couleurs

    public static function isHex($v): bool
    {
        return is_string($v) && (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $v);
    }

    private static function luminance(string $hex): float
    {
        $c = array_map(fn ($i) => hexdec(substr($hex, $i, 2)) / 255, [1, 3, 5]);
        $c = array_map(fn ($v) => $v <= 0.03928 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4), $c);
        return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
    }

    public static function contrast(string $a, string $b): float
    {
        $x = self::luminance($a);
        $y = self::luminance($b);
        return (max($x, $y) + 0.05) / (min($x, $y) + 0.05);
    }

    /** Texte lisible sur la couleur (blanc ou encre foncée, le plus contrasté des deux). */
    public static function inkFor(string $hex): string
    {
        // Blanc dès qu'il reste lisible en gras (3:1), comme le bleu Shopio ; texte sombre sur les couleurs claires (jaune, ambre…)
        return self::contrast($hex, '#FFFFFF') >= 3 ? '#FFFFFF' : '#16162A';
    }

    // ─────────────────────────────────────────────────────────────── Adresse de la boutique

    public static function slugify(string $v): string
    {
        $v = \Illuminate\Support\Str::slug($v);
        return mb_substr(trim($v, '-'), 0, 40);
    }

    /** Message d'erreur pour une adresse, ou null si elle est disponible pour cette boutique. */
    public static function slugError(string $slug, ?int $shopId): ?string
    {
        if (mb_strlen($slug) < 3) return '3 caractères minimum (lettres, chiffres, tirets).';
        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) return 'Lettres minuscules, chiffres et tirets uniquement.';
        if (in_array($slug, self::RESERVED_SLUGS, true)) return 'Cette adresse est réservée par Shopio.';
        $taken = Shop::where('slug', $slug)->when($shopId, fn ($q) => $q->where('id', '!=', $shopId))->exists();
        return $taken ? 'Cette adresse est déjà prise par une autre boutique.' : null;
    }

    public static function publicUrl(Shop $shop): string
    {
        return $shop->slug ? route('public.shops.slug', $shop->slug) : route('public.shops.products', $shop);
    }

    // ─────────────────────────────────────────────────────────────── Catégories (un lien par catégorie)

    /**
     * Catégories des produits en ligne de la boutique, triées : [nom, fin de lien, lien, nombre de produits].
     * Chaque catégorie a son lien automatiquement, tiré de son nom (« Électronique & Téléphones » →
     * …/electronique-telephones) : rien à régler, le lien marche dès qu'un produit est rangé dans la catégorie.
     */
    public static function categoryLinks(Shop $shop): array
    {
        $used = [];
        return $shop->products()->where('is_active', true)->whereNotNull('category')->where('category', '!=', '')
            ->selectRaw('category, COUNT(*) as n')->groupBy('category')->orderBy('category')->get()
            ->map(function ($r) use ($shop, &$used) {
                // Deux noms proches (« Téléphones », « telephones ») ne partagent jamais le même lien
                $base = mb_substr(\Illuminate\Support\Str::slug($r->category), 0, 40) ?: 'categorie';
                $slug = $base;
                for ($i = 2; isset($used[$slug]); $i++) $slug = $base . '-' . $i;
                $used[$slug] = true;

                return [
                    'name'  => $r->category,
                    'slug'  => $slug,
                    'url'   => $shop->slug
                        ? route('public.shops.slug.category', [$shop->slug, $slug])
                        : route('public.shops.category', [$shop, $slug]),
                    'count' => (int) $r->n,
                ];
            })
            ->values()->all();
    }

    // ─────────────────────────────────────────────────────────────── Aides d'affichage

    /** Vidéo : ['youtube', id] (lecteur intégré) ou ['link', url, site] (lien vers la plateforme). */
    public static function video(string $url): ?array
    {
        if ($url === '') return null;
        if (preg_match('#(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $m)) {
            return ['kind' => 'youtube', 'id' => $m[1], 'url' => $url];
        }
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $site = str_contains($host, 'tiktok') ? 'TikTok' : (str_contains($host, 'youtu') ? 'YouTube' : 'Facebook');
        return ['kind' => 'link', 'url' => $url, 'site' => $site];
    }
}
