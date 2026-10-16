<?php

namespace App\Support;

use App\Models\Shop;

/**
 * Réglages de la vitrine publique d'une boutique (« Personnaliser ma vitrine »).
 *
 * Stockés en JSON dans shops.storefront. Toute entrée (formulaire, aperçu en direct)
 * passe par normalize() : seules les clés et valeurs connues sont gardées, le reste
 * retombe sur les valeurs par défaut. La vue publique n'affiche donc jamais une valeur
 * libre, à part le texte d'annonce (échappé à l'affichage).
 */
class Storefront
{
    /** Sections de la page, dans l'ordre par défaut, avec leur libellé dans l'éditeur. */
    public const SECTIONS = [
        'announcement' => "Bandeau d'annonce",
        'categories'   => 'Catégories',
        'featured'     => 'Produit à la une',
        'products'     => 'Tous les produits',
        'reviews'      => 'Avis clients',
        'delivery'     => 'Paiement et livraison',
        'contact'      => 'Bloc contact',
    ];

    public const LAYOUTS = ['grid' => 'Grille', 'list' => 'Liste', 'carousel' => 'Carrousel'];
    public const COLUMNS = [2, 3, 4];
    public const SORTS   = ['featured' => 'Mis en avant', 'recent' => 'Plus récents', 'price_asc' => 'Prix croissant', 'price_desc' => 'Prix décroissant'];
    public const CARDS   = ['rounded' => 'Arrondies', 'square' => 'Droites'];
    public const BANNERS = ['large' => 'Grande', 'compact' => 'Compacte', 'none' => 'Sans'];

    /** Couleur du texte lisible sur chaque couleur d'accent (contraste ≥ 4,5:1). */
    public const ACCENT_INK = ['#1F7CF6' => '#FFFFFF', '#0B1E3D' => '#FFFFFF', '#F59E0B' => '#16162A', '#15803D' => '#FFFFFF'];

    public const ANNOUNCEMENT_MAX = 140;

    /** Nombre maximum de produits choisis pour la section « Produit à la une ». */
    public const FEATURED_MAX = 3;

    /**
     * Modèles prêts à l'emploi : un point de départ cohérent selon le type de boutique.
     * Appliqués dans l'éditeur (non publiés tant que le vendeur ne clique pas sur « Publier »).
     */
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

    public static function defaults(): array
    {
        return [
            'sections'     => array_map(fn ($k) => ['key' => $k, 'on' => true], array_keys(self::SECTIONS)),
            'announcement' => '',
            'layout'       => 'grid',
            'columns'      => 4,
            'sort'         => 'featured',
            'cards'        => 'rounded',
            'banner'       => 'large',
            'featured_ids' => [],
        ];
    }

    /** Nettoie une configuration quelconque (tableau venant de la base, d'un formulaire ou de l'aperçu). */
    public static function normalize($input): array
    {
        $d = self::defaults();
        if (!is_array($input)) return $d;

        // Sections : on garde l'ordre fourni pour les clés connues, sans doublon,
        // puis on ajoute à la fin celles qui manquent (nouvelle section ajoutée plus tard).
        $sections = [];
        foreach ((array) ($input['sections'] ?? []) as $s) {
            $key = is_array($s) ? ($s['key'] ?? null) : null;
            if (!is_string($key) || !isset(self::SECTIONS[$key]) || isset($sections[$key])) continue;
            $on = $s['on'] ?? true;
            $sections[$key] = ['key' => $key, 'on' => filter_var($on, FILTER_VALIDATE_BOOLEAN)];
        }
        foreach (array_keys(self::SECTIONS) as $key) {
            if (!isset($sections[$key])) $sections[$key] = ['key' => $key, 'on' => true];
        }

        $pick = fn ($value, array $allowed, $default) => in_array($value, $allowed, true) ? $value : $default;
        $announcement = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($input['announcement'] ?? ''))));

        return [
            'sections'     => array_values($sections),
            'announcement' => mb_substr($announcement, 0, self::ANNOUNCEMENT_MAX),
            'layout'       => $pick($input['layout'] ?? null, array_keys(self::LAYOUTS), $d['layout']),
            'columns'      => $pick((int) ($input['columns'] ?? 0), self::COLUMNS, $d['columns']),
            'sort'         => $pick($input['sort'] ?? null, array_keys(self::SORTS), $d['sort']),
            'cards'        => $pick($input['cards'] ?? null, array_keys(self::CARDS), $d['cards']),
            'banner'       => $pick($input['banner'] ?? null, array_keys(self::BANNERS), $d['banner']),
            // Identifiants de produits (entiers > 0, sans doublon, 3 max). L'appartenance à la
            // boutique est vérifiée au moment de l'affichage (requête limitée à la boutique).
            'featured_ids' => array_slice(array_values(array_unique(array_filter(
                array_map('intval', (array) ($input['featured_ids'] ?? [])),
                fn ($id) => $id > 0
            ))), 0, self::FEATURED_MAX),
        ];
    }

    /** Configuration enregistrée d'une boutique (ou valeurs par défaut). */
    public static function forShop(Shop $shop): array
    {
        return self::normalize($shop->storefront);
    }

    /** Décode la configuration d'aperçu passée dans l'URL (?vitrine=… en base64url JSON). */
    public static function fromPreviewParam(?string $param): ?array
    {
        if (!$param || strlen($param) > 4000) return null;
        $json = base64_decode(strtr($param, '-_', '+/'), true);
        $data = $json ? json_decode($json, true) : null;
        return is_array($data) ? self::normalize($data) : null;
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

        $items = [
            ['key' => 'description', 'label' => 'Une description de la boutique',      'done' => mb_strlen(trim((string) $shop->description)) >= 30, 'url' => $edit(3), 'cta' => 'Écrire'],
            ['key' => 'image',       'label' => 'Un logo ou une photo de la boutique', 'done' => (bool) $shop->image,                                  'url' => $edit(4), 'cta' => 'Ajouter'],
            ['key' => 'phone',       'label' => 'Un téléphone de contact',             'done' => (bool) $shop->phone,                                  'url' => $edit(2), 'cta' => 'Ajouter'],
            ['key' => 'address',     'label' => 'Une adresse ou un point de repère',   'done' => (bool) $shop->address,                                'url' => $edit(2), 'cta' => 'Ajouter'],
            ['key' => 'products',    'label' => 'Au moins 4 produits en ligne',        'done' => $activeProducts >= 4,                                 'url' => route('products.create'), 'cta' => 'Ajouter un produit'],
            ['key' => 'featured',    'label' => 'Un produit à la une',                 'done' => !empty($config['featured_ids']) || $hasFlagged,       'url' => '#vfFeatured', 'cta' => 'Choisir', 'live' => true, 'flagged' => $hasFlagged],
            ['key' => 'payout',      'label' => 'Le paiement en ligne (Mobile Money)', 'done' => $shop->payout_wallet_type && $shop->payout_wallet_number, 'url' => $edit(2), 'cta' => 'Activer'],
        ];

        return $items;
    }

    /** Nombre de sections visibles. */
    public static function visibleCount(array $config): int
    {
        return count(array_filter($config['sections'], fn ($s) => $s['on']));
    }
}
