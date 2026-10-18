<?php

namespace App\Support;

use App\Models\Shop;
use Carbon\Carbon;

/**
 * Vitrine publique d'une boutique (« Éditeur de ma boutique »).
 *
 * Stockée en JSON dans shops.storefront. Toute entrée (publication, aperçu, base) passe par
 * normalize() : seules les clés, types et valeurs connus sont gardés, les textes sont nettoyés
 * et bornés, le reste retombe sur les valeurs par défaut. Les anciennes vitrines (liste de
 * « sections ») sont converties automatiquement en blocs.
 *
 *  - blocks   : la page, dans l'ordre. Blocs « de base » (un exemplaire chacun, masquables)
 *               et blocs ajoutés par le vendeur (texte, image, vidéo, galerie…).
 *  - style    : police, taille du texte, forme et remplissage des boutons, fond de page.
 *               La couleur principale reste dans shops.accent_color.
 *  - settings : texte des boutons, options des fiches, mode vacances, message de bienvenue.
 *  - themes   : apparences enregistrées par le vendeur (activables d'un clic).
 */
class Storefront
{
    /**
     * Types de blocs : [libellé, description, exemplaire unique, toujours affiché].
     * « Toujours affiché » = bloc de confiance commun à toutes les boutiques : déplaçable,
     * mais ni masquable ni supprimable.
     */
    public const BLOCKS = [
        'announcement' => ["Bandeau d'annonce", 'Une phrase en haut de la page', true, false],
        'categories'   => ['Catégories', 'Filtres par catégorie de produits', true, false],
        'featured'     => ['Produit à la une', 'Jusqu’à 3 produits mis en grand', true, false],
        'products'     => ['Tous les produits', 'Le catalogue complet de la boutique', true, false],
        'reviews'      => ['Avis clients', 'Les avis écrits par de vrais clients', true, true],
        'delivery'     => ['Paiement et livraison', 'Paiement à la livraison, suivi, contact', true, true],
        'contact'      => ['Bloc contact', 'Téléphone, WhatsApp, adresse', true, false],
        'text'         => ['Texte libre', 'Un titre et un paragraphe', false, false],
        'imgtext'      => ['Image et texte', 'Une photo à côté d’un texte', false, false],
        'video'        => ['Vidéo', 'Un lien YouTube ou TikTok', false, false],
        'gallery'      => ['Galerie photos', 'Jusqu’à 8 photos', false, false],
        'countdown'    => ['Compte à rebours', 'Pour une promotion limitée', false, false],
        'faq'          => ['Questions fréquentes', 'Les questions de vos clients', false, false],
        'story'        => ['Notre histoire', 'Présentez la boutique', false, false],
        'hours'        => ['Adresse et horaires', 'Où vous trouver, quand vous êtes ouvert', false, false],
        'social'       => ['Réseaux sociaux', 'Liens WhatsApp, Facebook, TikTok, Instagram', false, false],
        'selection'    => ['Sélection de produits', 'Une catégorie mise en avant', false, false],
    ];

    /** Blocs de base, dans l'ordre par défaut. */
    public const CORE = ['announcement', 'categories', 'featured', 'products', 'reviews', 'delivery', 'contact'];

    public const MAX_BLOCKS = 30;
    public const FEATURED_MAX = 3;
    public const ANNOUNCEMENT_MAX = 140;
    public const GALLERY_MAX = 8;
    public const FAQ_MAX = 6;
    public const THEMES_MAX = 8;
    public const HISTORY_MAX = 10;

    public const LAYOUTS = ['grid' => 'Grille', 'list' => 'Liste', 'carousel' => 'Carrousel'];
    public const COLUMNS = [2, 3, 4];
    public const SORTS   = ['featured' => 'Mis en avant', 'recent' => 'Plus récents', 'price_asc' => 'Prix croissant', 'price_desc' => 'Prix décroissant'];
    public const CARDS   = ['rounded' => 'Arrondies', 'square' => 'Droites'];
    public const BANNERS = ['large' => 'Grande', 'compact' => 'Compacte', 'none' => 'Sans'];

    /** Polices : [libellé, pile CSS, paramètre Google Fonts (null = déjà chargée par le site)]. */
    public const FONTS = [
        'moderne'   => ['Moderne', "'Plus Jakarta Sans', system-ui, sans-serif", null],
        'elegante'  => ['Élégante', "'Playfair Display', Georgia, serif", 'Playfair+Display:wght@600;700;800'],
        'ronde'     => ['Ronde', "'Nunito', system-ui, sans-serif", 'Nunito:wght@500;700;800'],
        'classique' => ['Classique', "'Lora', Georgia, serif", 'Lora:wght@500;600;700'],
    ];
    public const SIZES  = ['s' => 'Petit', 'm' => 'Normal', 'l' => 'Grand'];
    public const BTN_SHAPES = ['pill' => 'Arrondis', 'square' => 'Carrés'];
    public const BTN_FILLS  = ['solid' => 'Pleins', 'outline' => 'Contour'];
    public const BACKGROUNDS = ['white' => 'Blanc', 'soft' => 'Doux', 'texture' => 'Texture', 'dark' => 'Sombre'];

    /** Couleurs proposées (la couleur libre reste possible). */
    public const COLOR_PRESETS = ['#1F7CF6' => 'Bleu Shopio', '#0B1E3D' => 'Bleu nuit', '#F59E0B' => 'Ambre', '#15803D' => 'Vert', '#9D174D' => 'Bordeaux', '#D4A017' => 'Or'];

    /** Thèmes proposés par Shopio (apparence complète, activable d'un clic). */
    public const THEMES = [
        'standard' => ['name' => 'Standard', 'desc' => 'Bleu Shopio, moderne', 'style' => ['color' => '#1F7CF6', 'font' => 'moderne', 'size' => 'm', 'btn' => 'pill', 'fill' => 'solid', 'bg' => 'soft']],
        'tabaski'  => ['name' => 'Tabaski', 'desc' => 'Vert et or, élégant', 'style' => ['color' => '#15803D', 'font' => 'elegante', 'size' => 'm', 'btn' => 'pill', 'fill' => 'solid', 'bg' => 'soft']],
        'ramadan'  => ['name' => 'Ramadan', 'desc' => 'Nuit et or, classique', 'style' => ['color' => '#D4A017', 'font' => 'classique', 'size' => 'm', 'btn' => 'pill', 'fill' => 'solid', 'bg' => 'dark']],
        'fin'      => ['name' => 'Fin d’année', 'desc' => 'Rouge festif, rond', 'style' => ['color' => '#C2410C', 'font' => 'ronde', 'size' => 'm', 'btn' => 'square', 'fill' => 'solid', 'bg' => 'texture']],
    ];

    /** Mises en page de départ selon le type de boutique (ordre des blocs de base + affichage). */
    public const TEMPLATES = [
        'mode' => [
            'label' => 'Mode', 'hint' => 'Photos en grand, nouveautés d’abord',
            'order' => ['announcement', 'featured', 'categories', 'products', 'reviews', 'delivery', 'contact'],
            'off' => [], 'layout' => 'grid', 'columns' => 3, 'sort' => 'recent', 'cards' => 'square', 'banner' => 'large', 'accent' => '#0B1E3D',
        ],
        'electronique' => [
            'label' => 'Électronique', 'hint' => 'Catalogue complet, prix visibles',
            'order' => ['featured', 'categories', 'products', 'delivery', 'reviews', 'contact', 'announcement'],
            'off' => ['announcement'], 'layout' => 'grid', 'columns' => 4, 'sort' => 'featured', 'cards' => 'rounded', 'banner' => 'compact', 'accent' => '#1F7CF6',
        ],
        'epicerie' => [
            'label' => 'Épicerie', 'hint' => 'Liste rapide, petits prix d’abord',
            'order' => ['announcement', 'categories', 'products', 'delivery', 'contact', 'featured', 'reviews'],
            'off' => ['featured'], 'layout' => 'list', 'columns' => 4, 'sort' => 'price_asc', 'cards' => 'rounded', 'banner' => 'compact', 'accent' => '#15803D',
        ],
        'restaurant' => [
            'label' => 'Restaurant', 'hint' => 'Plat du jour, menu en carrousel',
            'order' => ['announcement', 'featured', 'products', 'delivery', 'contact', 'reviews', 'categories'],
            'off' => ['categories'], 'layout' => 'carousel', 'columns' => 3, 'sort' => 'featured', 'cards' => 'rounded', 'banner' => 'large', 'accent' => '#F59E0B',
        ],
    ];

    public const BTN_SUGGESTIONS = ['Commander', 'Je le veux', 'Acheter', 'Réserver'];

    /** Adresses réservées (pages du site ou noms trompeurs). */
    public const RESERVED_SLUGS = ['admin', 'shopio', 'boutique', 'boutiques', 'shop', 'shops', 'support', 'api', 'login', 'register', 'client', 'vendeur', 'livreur', 'produit', 'produits', 'products', 'aide', 'help', 'contact', 'compte', 'panier', 'cart', 'suivi', 'marketing', 'www', 'app'];

    private const IMG_PATH = '#^storefront/medium/[A-Za-z0-9]{20}\.webp$#';
    private const TIME = '/^([01]\d|2[0-3]):[0-5]\d$/';

    // ─────────────────────────────────────────────────────────────── Valeurs par défaut

    public static function defaults(): array
    {
        return [
            'blocks'       => array_map(fn ($t) => ['id' => $t, 'type' => $t, 'on' => true, 'd' => self::blockDefaults($t)], self::CORE),
            'layout'       => 'grid',
            'columns'      => 4,
            'sort'         => 'featured',
            'cards'        => 'rounded',
            'banner'       => 'large',
            'featured_ids' => [],
            'style'        => self::styleDefaults(),
            'settings'     => self::settingsDefaults(),
            'themes'       => [],
        ];
    }

    public static function styleDefaults(): array
    {
        return ['font' => 'moderne', 'size' => 'm', 'btn' => 'pill', 'fill' => 'solid', 'bg' => 'soft'];
    }

    public static function settingsDefaults(): array
    {
        return [
            'btn_text' => 'Commander', 'show_stock' => true, 'show_strike' => true, 'show_nego' => false,
            'vacation' => false, 'vacation_msg' => 'De retour très bientôt',
            'welcome' => false, 'welcome_promo_id' => null,
        ];
    }

    /** Contenu de départ d'un bloc ajouté (textes entre crochets = à remplacer par le vendeur). */
    public static function blockDefaults(string $type): array
    {
        return match ($type) {
            'announcement' => ['text' => ''],
            'text'      => ['title' => 'Bienvenue', 'body' => ''],
            'imgtext'   => ['title' => 'Titre de la section', 'body' => '', 'img' => null, 'side' => 'left'],
            'video'     => ['title' => 'Nos produits en vidéo', 'url' => ''],
            'gallery'   => ['title' => 'Galerie', 'imgs' => []],
            'countdown' => ['title' => 'Promotion', 'sub' => '', 'end' => now()->addDays(7)->setTime(20, 0)->format('Y-m-d\TH:i')],
            'faq'       => ['title' => 'Questions fréquentes', 'items' => [
                ['q' => 'Comment payer ?', 'a' => 'En espèces, au livreur, à la réception de la commande.'],
                ['q' => 'Quel est le délai de livraison ?', 'a' => ''],
            ]],
            'story'     => ['title' => 'Notre histoire', 'body' => ''],
            'hours'     => ['addr' => '', 'days' => 'Lundi – samedi', 'open' => '09:00', 'close' => '20:00'],
            'social'    => ['wa' => '', 'fb' => '', 'tt' => '', 'ig' => ''],
            'selection' => ['title' => 'Notre sélection', 'category' => '', 'limit' => 4],
            default     => [],
        };
    }

    // ─────────────────────────────────────────────────────────────── Nettoyage

    /** Nettoie une configuration quelconque (base, publication, aperçu). */
    public static function normalize($input): array
    {
        $d = self::defaults();
        if (!is_array($input)) return $d;

        $pick = fn ($value, array $allowed, $default) => in_array($value, $allowed, true) ? $value : $default;

        // Ancien format (liste de sections + texte d'annonce) → blocs
        if (!isset($input['blocks']) && isset($input['sections'])) {
            $input['blocks'] = array_map(fn ($s) => [
                'id' => $s['key'] ?? '', 'type' => $s['key'] ?? '', 'on' => $s['on'] ?? true,
                'd' => ($s['key'] ?? '') === 'announcement' ? ['text' => $input['announcement'] ?? ''] : [],
            ], array_filter((array) $input['sections'], 'is_array'));
        }

        return [
            'blocks'       => self::normalizeBlocks($input['blocks'] ?? null),
            'layout'       => $pick($input['layout'] ?? null, array_keys(self::LAYOUTS), $d['layout']),
            'columns'      => $pick((int) ($input['columns'] ?? 0), self::COLUMNS, $d['columns']),
            'sort'         => $pick($input['sort'] ?? null, array_keys(self::SORTS), $d['sort']),
            'cards'        => $pick($input['cards'] ?? null, array_keys(self::CARDS), $d['cards']),
            'banner'       => $pick($input['banner'] ?? null, array_keys(self::BANNERS), $d['banner']),
            // Produits à la une : entiers > 0, sans doublon, 3 max. L'appartenance à la boutique
            // est vérifiée à l'affichage (requête limitée à la boutique) et à la publication.
            'featured_ids' => array_slice(array_values(array_unique(array_filter(
                array_map('intval', (array) ($input['featured_ids'] ?? [])),
                fn ($id) => $id > 0
            ))), 0, self::FEATURED_MAX),
            'style'        => self::normalizeStyle($input['style'] ?? null),
            'settings'     => self::normalizeSettings($input['settings'] ?? null),
            'themes'       => self::normalizeThemes($input['themes'] ?? null),
        ];
    }

    private static function normalizeBlocks($blocks): array
    {
        $out = [];
        $seenCore = [];
        $seenIds = [];
        foreach (is_array($blocks) ? $blocks : [] as $b) {
            if (!is_array($b) || count($out) >= self::MAX_BLOCKS) continue;
            $type = $b['type'] ?? null;
            if (!is_string($type) || !isset(self::BLOCKS[$type])) continue;
            $core = in_array($type, self::CORE, true);
            if ($core && isset($seenCore[$type])) continue;

            $id = $core ? $type : (is_string($b['id'] ?? null) && preg_match('/^b[a-z0-9]{3,16}$/', $b['id']) ? $b['id'] : self::newId());
            while (isset($seenIds[$id])) $id = self::newId();
            $seenIds[$id] = true;
            if ($core) $seenCore[$type] = true;

            $trust = self::BLOCKS[$type][3];
            $out[] = [
                'id'   => $id,
                'type' => $type,
                'on'   => $trust ? true : filter_var($b['on'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'd'    => self::normalizeBlockData($type, is_array($b['d'] ?? null) ? $b['d'] : []),
            ];
        }
        // Les blocs de base ne disparaissent jamais (masqués au pire) : on rajoute ceux qui manquent.
        foreach (self::CORE as $type) {
            if (!isset($seenCore[$type])) {
                $out[] = ['id' => $type, 'type' => $type, 'on' => self::BLOCKS[$type][3], 'd' => self::blockDefaults($type)];
            }
        }
        return $out;
    }

    private static function text($v, int $max): string
    {
        $v = str_replace(["\r\n", "\r"], "\n", strip_tags((string) $v));
        $v = preg_replace("/[ \t]+/u", ' ', $v);
        $v = preg_replace("/\n{3,}/", "\n\n", $v);
        return mb_substr(trim($v), 0, $max);
    }

    private static function line($v, int $max): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $v))), 0, $max);
    }

    private static function img($v): ?string
    {
        return is_string($v) && preg_match(self::IMG_PATH, $v) ? $v : null;
    }

    public static function normalizeBlockData(string $type, array $d): array
    {
        return match ($type) {
            'announcement' => ['text' => self::line($d['text'] ?? '', self::ANNOUNCEMENT_MAX)],
            'text', 'story' => ['title' => self::line($d['title'] ?? '', 80), 'body' => self::text($d['body'] ?? '', 1500)],
            'imgtext' => [
                'title' => self::line($d['title'] ?? '', 80),
                'body'  => self::text($d['body'] ?? '', 1200),
                'img'   => self::img($d['img'] ?? null),
                'side'  => ($d['side'] ?? '') === 'right' ? 'right' : 'left',
            ],
            'video' => ['title' => self::line($d['title'] ?? '', 80), 'url' => self::videoUrl($d['url'] ?? '')],
            'gallery' => [
                'title' => self::line($d['title'] ?? '', 80),
                'imgs'  => array_slice(array_values(array_unique(array_filter(array_map(fn ($v) => self::img($v), (array) ($d['imgs'] ?? []))))), 0, self::GALLERY_MAX),
            ],
            'countdown' => [
                'title' => self::line($d['title'] ?? '', 80),
                'sub'   => self::line($d['sub'] ?? '', 140),
                'end'   => self::dateTime($d['end'] ?? '') ?? now()->addDays(7)->setTime(20, 0)->format('Y-m-d\TH:i'),
            ],
            'faq' => [
                'title' => self::line($d['title'] ?? '', 80),
                'items' => array_slice(array_values(array_filter(array_map(fn ($it) => [
                    'q' => self::line(is_array($it) ? ($it['q'] ?? '') : '', 140),
                    'a' => self::text(is_array($it) ? ($it['a'] ?? '') : '', 600),
                ], (array) ($d['items'] ?? [])), fn ($it) => $it['q'] !== '' || $it['a'] !== '')), 0, self::FAQ_MAX),
            ],
            'hours' => [
                'addr'  => self::line($d['addr'] ?? '', 160),
                'days'  => self::line($d['days'] ?? '', 60),
                'open'  => preg_match(self::TIME, (string) ($d['open'] ?? '')) ? $d['open'] : '09:00',
                'close' => preg_match(self::TIME, (string) ($d['close'] ?? '')) ? $d['close'] : '20:00',
            ],
            'social' => [
                'wa' => mb_substr(preg_replace('/[^\d+]/', '', (string) ($d['wa'] ?? '')), 0, 20),
                'fb' => self::handle($d['fb'] ?? ''),
                'tt' => self::handle($d['tt'] ?? ''),
                'ig' => self::handle($d['ig'] ?? ''),
            ],
            'selection' => [
                'title'    => self::line($d['title'] ?? '', 80),
                'category' => self::line($d['category'] ?? '', 80),
                'limit'    => in_array((int) ($d['limit'] ?? 4), [4, 8], true) ? (int) $d['limit'] : 4,
            ],
            default => [],
        };
    }

    private static function handle($v): string
    {
        return mb_substr(preg_replace('/\s+/', '', strip_tags((string) $v)), 0, 120);
    }

    private static function dateTime($v): ?string
    {
        if (!is_string($v) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $v)) return null;
        try { return Carbon::createFromFormat('Y-m-d\TH:i', $v)->format('Y-m-d\TH:i'); } catch (\Throwable $e) { return null; }
    }

    /** Lien vidéo accepté : YouTube, TikTok ou Facebook, en https. */
    private static function videoUrl($v): string
    {
        $v = trim((string) $v);
        if ($v === '' || mb_strlen($v) > 300) return '';
        if (!preg_match('#^https?://#i', $v)) $v = 'https://' . $v;
        $host = strtolower((string) parse_url($v, PHP_URL_HOST));
        foreach (['youtube.com', 'youtu.be', 'tiktok.com', 'facebook.com', 'fb.watch'] as $ok) {
            if ($host === $ok || str_ends_with($host, '.' . $ok)) return preg_replace('#^http://#i', 'https://', $v);
        }
        return '';
    }

    public static function normalizeStyle($s): array
    {
        $d = self::styleDefaults();
        $s = is_array($s) ? $s : [];
        $pick = fn ($k, array $allowed) => in_array($s[$k] ?? null, $allowed, true) ? $s[$k] : $d[$k];
        return [
            'font' => $pick('font', array_keys(self::FONTS)),
            'size' => $pick('size', array_keys(self::SIZES)),
            'btn'  => $pick('btn', array_keys(self::BTN_SHAPES)),
            'fill' => $pick('fill', array_keys(self::BTN_FILLS)),
            'bg'   => $pick('bg', array_keys(self::BACKGROUNDS)),
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
        ];
    }

    private static function normalizeThemes($themes): array
    {
        $out = [];
        foreach (is_array($themes) ? $themes : [] as $t) {
            if (!is_array($t) || count($out) >= self::THEMES_MAX) continue;
            $name = self::line($t['name'] ?? '', 40);
            $color = $t['style']['color'] ?? null;
            if ($name === '' || !self::isHex($color)) continue;
            $out[] = [
                'id'    => is_string($t['id'] ?? null) && preg_match('/^t[a-z0-9]{3,16}$/', $t['id']) ? $t['id'] : 't' . substr(self::newId(), 1),
                'name'  => $name,
                'style' => ['color' => strtoupper($color)] + self::normalizeStyle($t['style'] ?? []),
            ];
        }
        return $out;
    }

    public static function newId(): string
    {
        return 'b' . substr(base_convert(bin2hex(random_bytes(6)), 16, 36), 0, 10);
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

    public static function block(array $config, string $id): ?array
    {
        foreach ($config['blocks'] as $b) if ($b['id'] === $id) return $b;
        return null;
    }

    public static function visibleCount(array $config): int
    {
        return count(array_filter($config['blocks'], fn ($b) => $b['on']));
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

    /** Fond de page : [fond, encre, encre secondaire, ligne, surface, surface douce]. */
    public static function background(string $bg): array
    {
        return match ($bg) {
            'white'   => ['#FFFFFF', '#16162A', '#45455E', '#E3E8F2', '#FFFFFF', '#F1F5FB'],
            'texture' => ['#FBF8F3', '#16162A', '#45455E', '#E9E1D3', '#FFFFFF', '#F3ECE0'],
            'dark'    => ['#0B1E3D', '#FFFFFF', '#C9D6EA', '#24406E', '#132B52', '#132B52'],
            default   => ['#F6F9FF', '#16162A', '#45455E', '#E3E8F2', '#FFFFFF', '#EAF2FF'],
        };
    }

    /** Couleur des titres : la couleur principale si elle est lisible sur le fond, sinon l'encre. */
    public static function titleColor(string $accent, string $bg): string
    {
        $b = self::background($bg);
        return self::contrast($accent, $b[0]) >= 3 ? $accent : $b[1];
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

    /** Liens des réseaux renseignés : [[libellé, url, couleur]]. */
    public static function socialLinks(array $d): array
    {
        $url = fn ($v, $base) => preg_match('#^https?://#i', $v) ? $v : $base . ltrim($v, '@/');
        $links = [];
        if (($d['wa'] ?? '') !== '' && strlen(preg_replace('/\D/', '', $d['wa'])) >= 8) $links[] = ['WhatsApp', 'https://wa.me/' . preg_replace('/\D/', '', $d['wa']), '#15803D'];
        if (($d['fb'] ?? '') !== '') $links[] = ['Facebook', $url($d['fb'], 'https://facebook.com/'), '#1668D8'];
        if (($d['tt'] ?? '') !== '') $links[] = ['TikTok', $url($d['tt'], 'https://www.tiktok.com/@'), '#16162A'];
        if (($d['ig'] ?? '') !== '') $links[] = ['Instagram', $url($d['ig'], 'https://instagram.com/'), '#9D174D'];
        return $links;
    }

    /** La boutique est-elle ouverte maintenant (heure du site) ? */
    public static function isOpenNow(array $d): bool
    {
        $now = now()->format('H:i');
        return $d['open'] < $d['close'] ? ($now >= $d['open'] && $now < $d['close']) : ($now >= $d['open'] || $now < $d['close']);
    }

    /**
     * Produits de la section « à la une » : ceux choisis par le vendeur (dans l'ordre choisi,
     * uniquement s'ils appartiennent à la boutique et sont en ligne), sinon le dernier produit
     * marqué « mis en avant ».
     */
    public static function featuredProducts(Shop $shop, array $config)
    {
        $ids = $config['featured_ids'] ?? [];
        if ($ids) {
            $found = $shop->products()->where('is_active', true)->whereIn('id', $ids)->get()->keyBy('id');
            $picked = collect($ids)->map(fn ($id) => $found->get($id))->filter()->values();
            if ($picked->isNotEmpty()) return $picked;
        }
        return $shop->products()->where('is_active', true)->where('is_featured', true)->latest()->take(1)->get();
    }

    /**
     * Indicateur de qualité de la vitrine : points vérifiables, chacun avec le lien pour le corriger.
     * « live » = point que l'éditeur peut mettre à jour sans recharger (choix du produit à la une).
     */
    public static function checklist(Shop $shop, array $config, int $activeProducts): array
    {
        $edit = fn (int $step) => route('shop.edit', $shop) . '?etape=' . $step;
        $hasFlagged = $shop->products()->where('is_active', true)->where('is_featured', true)->exists();

        return [
            ['key' => 'description', 'label' => 'Une description de la boutique',      'done' => mb_strlen(trim((string) $shop->description)) >= 30, 'url' => $edit(3), 'cta' => 'Écrire'],
            ['key' => 'image',       'label' => 'Un logo ou une photo de la boutique', 'done' => (bool) $shop->image,                                  'url' => $edit(4), 'cta' => 'Ajouter'],
            ['key' => 'phone',       'label' => 'Un téléphone de contact',             'done' => (bool) $shop->phone,                                  'url' => $edit(2), 'cta' => 'Ajouter'],
            ['key' => 'address',     'label' => 'Une adresse ou un point de repère',   'done' => (bool) $shop->address,                                'url' => $edit(2), 'cta' => 'Ajouter'],
            ['key' => 'products',    'label' => 'Au moins 4 produits en ligne',        'done' => $activeProducts >= 4,                                 'url' => route('products.create'), 'cta' => 'Ajouter un produit'],
            ['key' => 'featured',    'label' => 'Un produit à la une',                 'done' => !empty($config['featured_ids']) || $hasFlagged,       'url' => '#bloc-featured', 'cta' => 'Choisir', 'live' => true, 'flagged' => $hasFlagged],
            ['key' => 'slug',        'label' => 'Une adresse courte pour la boutique', 'done' => (bool) $shop->slug,                                   'url' => '#reglages', 'cta' => 'Choisir'],
            ['key' => 'payout',      'label' => 'Le paiement en ligne (Mobile Money)', 'done' => $shop->payout_wallet_type && $shop->payout_wallet_number, 'url' => $edit(2), 'cta' => 'Activer'],
        ];
    }
}
