<?php

namespace App\Support;

use App\Services\ImageOptimizer;

/**
 * Sections de la page d'accueil de la vitrine, réglées dans « Boutique → Sections ».
 * Chaque thème a sa propre liste, dans l'ordre d'affichage : shops.storefront['home'][thème].
 *
 *  - Sections du thème (une de chaque, à masquer, déplacer et dont on réécrit les textes) :
 *    un texte laissé vide garde le texte prévu par le thème (affiché en exemple dans l'éditeur).
 *  - Sections ajoutées par le vendeur (plusieurs possibles, à supprimer) : texte, image et texte,
 *    bannière, produits d'une catégorie, questions fréquentes.
 *
 * Toute entrée passe par normalize() : types connus, textes nettoyés et bornés, photos importées
 * par la boutique seulement (covers/medium/…webp), liens http(s) seulement.
 */
class HomeSections
{
    public const CUSTOM_MAX = 12;
    public const FAQ_MAX = 8;

    /** [libellé, description]. */
    public const TYPES = [
        'hero'       => ['Accueil', 'Grand titre, texte et bouton en haut de la page'],
        'promise'    => ['Engagements', 'Trois engagements : livraison, paiement, suivi…'],
        'categories' => ['Catégories', 'Vos catégories en tuiles photo'],
        'products'   => ['Produits', 'Vos produits phares, prêts à commander'],
        'spotlight'  => ['Produit vedette', 'Un produit mis en avant, en grand'],
        'how'        => ['Comment commander', 'Les 3 étapes de la commande'],
        'results'    => ['Chiffres réels', 'Note moyenne et commandes livrées (s’il y en a)'],
        'reviews'    => ['Avis clients', 'Les vrais avis laissés par vos clients'],
        'faq'        => ['Questions fréquentes', 'Questions et réponses'],
        'text'       => ['Texte', 'Un titre et un paragraphe : votre histoire, vos conditions…'],
        'imgtext'    => ['Image et texte', 'Une photo à côté d’un titre, d’un texte et d’un bouton'],
        'banner'     => ['Bannière', 'Un message en couleur avec un bouton : promo, nouveauté…'],
        'category'   => ['Produits d’une catégorie', 'Une sélection de produits d’une de vos catégories'],
        'qa'         => ['Questions et réponses', 'Vos propres questions et réponses'],
    ];

    /** Sections propres à chaque thème, dans l'ordre par défaut. */
    public const BUILTIN = [
        'express'   => ['hero', 'promise', 'products', 'results', 'reviews', 'faq'],
        'signature' => ['hero', 'promise', 'categories', 'products', 'spotlight', 'how', 'reviews', 'faq'],
    ];

    /** Sections que le vendeur peut ajouter (dans les deux thèmes). */
    public const ADDABLE = ['text', 'imgtext', 'banner', 'category', 'qa'];

    /**
     * Champs modifiables : [clé, sorte, libellé, longueur max]. Sortes : line, text, img, link,
     * side (gauche / droite), tone (couleur), cat (catégorie), limit (4 / 8), items (3 engagements), faq.
     */
    public const FIELDS = [
        'hero'       => [['title', 'line', 'Titre', 90], ['text', 'text', 'Texte', 300], ['button', 'line', 'Texte du bouton', 30], ['img', 'img', 'Photo (sinon votre produit phare)', 0]],
        'promise'    => [['items', 'items', 'Engagements', 0]],
        'categories' => [['title', 'line', 'Titre', 60]],
        'products'   => [['title', 'line', 'Titre', 60], ['text', 'line', 'Sous-titre', 160]],
        'spotlight'  => [],
        'how'        => [['title', 'line', 'Titre', 60], ['text', 'text', 'Texte', 200]],
        'results'    => [['title', 'line', 'Titre', 60]],
        'reviews'    => [['title', 'line', 'Titre', 60]],
        'faq'        => [['title', 'line', 'Titre', 80], ['items', 'faq', 'Questions (vide = questions prévues par Shopio)', 0]],
        'text'       => [['title', 'line', 'Titre', 90], ['text', 'text', 'Texte', 1500], ['align', 'align', 'Alignement', 0]],
        'imgtext'    => [['img', 'img', 'Photo', 0], ['title', 'line', 'Titre', 90], ['text', 'text', 'Texte', 800], ['button', 'line', 'Texte du bouton', 30], ['link', 'link', 'Lien du bouton (vide = vos produits)', 300], ['side', 'side', 'Photo à', 0]],
        'banner'     => [['title', 'line', 'Message', 140], ['text', 'line', 'Petit texte', 160], ['button', 'line', 'Texte du bouton', 30], ['link', 'link', 'Lien du bouton (vide = vos produits)', 300], ['tone', 'tone', 'Couleur', 0]],
        'category'   => [['title', 'line', 'Titre (vide = nom de la catégorie)', 60], ['cat', 'cat', 'Catégorie', 100], ['limit', 'limit', 'Nombre de produits', 0]],
        'qa'         => [['title', 'line', 'Titre', 80], ['items', 'faq', 'Questions', 0]],
    ];

    /** Textes prévus par chaque thème (affichés quand le champ est vide, et en exemple dans l'éditeur). */
    public const DEFAULTS = [
        'express' => [
            'hero'     => ['title' => 'Nos meilleurs produits, livrés chez vous.', 'text' => 'Des produits choisis avec soin. Commandez en toute confiance et payez seulement à la livraison.', 'button' => 'Voir la boutique'],
            'promise'  => ['items' => [['title' => 'Livraison à domicile', 'text' => 'Délai confirmé par téléphone'], ['title' => 'Paiement à la livraison', 'text' => 'En espèces, à la réception'], ['title' => 'Suivi de commande', 'text' => 'Chaque étape sur Shopio']]],
            'products' => ['title' => 'Nos produits phares', 'text' => 'Les préférés de la boutique, prêts à être livrés.'],
            'results'  => ['title' => 'Des résultats qui parlent'],
            'reviews'  => ['title' => 'Avis clients'],
            'faq'      => ['title' => 'Les réponses à vos questions importantes'],
        ],
        'signature' => [
            'hero'       => ['title' => '', 'text' => 'Choisissez, commandez en une minute, payez quand le livreur arrive chez vous.', 'button' => 'Découvrir la collection'],
            'promise'    => ['items' => [['title' => 'Paiement à la livraison', 'text' => 'En espèces, au livreur. Rien à payer avant.'], ['title' => 'Appel de confirmation', 'text' => 'La boutique vous appelle avant de livrer.'], ['title' => 'Suivi de commande', 'text' => 'Vous suivez votre colis sur Shopio.']]],
            'categories' => ['title' => 'Par univers'],
            'products'   => ['title' => 'Pensé pour durer', 'text' => ''],
            'how'        => ['title' => 'Commander en une minute', 'text' => 'Pas de carte bancaire, pas de compte obligatoire. Vous payez seulement quand vous avez le produit en main.'],
            'reviews'    => ['title' => 'Ce qu’en disent nos clients'],
            'faq'        => ['title' => 'Les réponses à vos questions'],
        ],
    ];

    /** Couleurs de la bannière. */
    public const TONES = ['accent' => 'Couleur de la boutique', 'dark' => 'Sombre', 'soft' => 'Claire'];

    // ─────────────────────────────────────────────────────────────── Données

    /** Liste par défaut d'un thème : ses sections, toutes affichées, textes du thème. */
    public static function defaults(string $theme): array
    {
        return array_map(fn ($t) => ['id' => $t, 'type' => $t, 'on' => true, 'd' => self::blank($t)], self::BUILTIN[$theme] ?? self::BUILTIN['express']);
    }

    /** Contenu vide d'une section (champs vides = textes du thème). */
    public static function blank(string $type): array
    {
        $d = [];
        foreach (self::FIELDS[$type] ?? [] as [$k, $kind]) {
            $d[$k] = match ($kind) {
                'items' => [['title' => '', 'text' => ''], ['title' => '', 'text' => ''], ['title' => '', 'text' => '']],
                'faq'   => [],
                'img'   => null,
                'side'  => 'left',
                'tone'  => 'accent',
                'align' => 'left',
                'limit' => 4,
                default => '',
            };
        }
        if ($type === 'qa') $d['items'] = [['q' => '', 'a' => '']];
        return $d;
    }

    /** Toutes les listes (une par thème), nettoyées. */
    public static function normalizeAll($input): array
    {
        $input = is_array($input) ? $input : [];
        $out = [];
        foreach (array_keys(self::BUILTIN) as $theme) $out[$theme] = self::normalize($theme, $input[$theme] ?? null);
        return $out;
    }

    /** Une liste : chaque section du thème une seule fois (ajoutée si absente), sections ajoutées bornées. */
    public static function normalize(string $theme, $list): array
    {
        $builtin = self::BUILTIN[$theme] ?? self::BUILTIN['express'];
        if (!is_array($list) || !$list) return self::defaults($theme);

        $out = []; $seen = []; $custom = 0;
        foreach ($list as $s) {
            if (!is_array($s)) continue;
            $type = (string) ($s['type'] ?? '');
            if (in_array($type, $builtin, true)) {
                if (isset($seen[$type])) continue;
                $seen[$type] = true;
                $id = $type;
            } elseif (in_array($type, self::ADDABLE, true)) {
                if ($custom >= self::CUSTOM_MAX) continue;
                $custom++;
                $id = preg_match('/^s[a-z0-9]{6,12}$/', (string) ($s['id'] ?? '')) ? $s['id'] : self::newId();
            } else {
                continue;
            }
            $out[] = ['id' => $id, 'type' => $type, 'on' => filter_var($s['on'] ?? true, FILTER_VALIDATE_BOOLEAN), 'd' => self::normalizeData($type, is_array($s['d'] ?? null) ? $s['d'] : [])];
        }
        // Section du thème absente (liste ancienne, envoi incomplet) : remise à sa place par défaut
        foreach ($builtin as $i => $type) {
            if (isset($seen[$type])) continue;
            $after = $i > 0 ? collect($out)->search(fn ($s) => $s['type'] === $builtin[$i - 1]) : -1;
            array_splice($out, $after === false ? count($out) : $after + 1, 0, [['id' => $type, 'type' => $type, 'on' => true, 'd' => self::blank($type)]]);
        }
        // Ids uniques (deux sections ajoutées ne partagent jamais un id)
        $ids = [];
        foreach ($out as &$s) { while (isset($ids[$s['id']])) $s['id'] = self::newId(); $ids[$s['id']] = true; }
        unset($s);
        return array_values($out);
    }

    public static function normalizeData(string $type, array $d): array
    {
        $out = [];
        foreach (self::FIELDS[$type] ?? [] as [$k, $kind, , $max]) {
            $v = $d[$k] ?? null;
            $out[$k] = match ($kind) {
                'line'  => self::line($v, $max),
                'text'  => self::text($v, $max),
                'img'   => is_string($v) && preg_match(Storefront::COVER_PATH, $v) ? $v : null,
                'link'  => self::link($v),
                'side'  => in_array($v, ['left', 'right'], true) ? $v : 'left',
                'align' => in_array($v, ['left', 'center'], true) ? $v : 'left',
                'tone'  => isset(self::TONES[$v]) ? $v : 'accent',
                'cat'   => self::line($v, $max),
                'limit' => in_array((int) $v, [4, 8], true) ? (int) $v : 4,
                'items' => array_map(fn ($i) => [
                    'title' => self::line(is_array($v) ? ($v[$i]['title'] ?? '') : '', 40),
                    'text'  => self::line(is_array($v) ? ($v[$i]['text'] ?? '') : '', 80),
                ], [0, 1, 2]),
                'faq'   => array_values(array_slice(array_filter(array_map(fn ($q) => is_array($q) ? ['q' => self::line($q['q'] ?? '', 150), 'a' => self::text($q['a'] ?? '', 600)] : null, is_array($v) ? $v : []), fn ($q) => $q && ($q['q'] !== '' || $q['a'] !== '')), 0, self::FAQ_MAX)),
                default => '',
            };
        }
        return $out;
    }

    private static function line($v, int $max): string
    {
        $v = trim(preg_replace('/\s+/u', ' ', strip_tags(mb_scrub((string) (is_scalar($v) ? $v : ''), 'UTF-8'))));
        return mb_substr($v, 0, $max);
    }

    private static function text($v, int $max): string
    {
        $v = strip_tags(mb_scrub((string) (is_scalar($v) ? $v : ''), 'UTF-8'));
        $v = trim(preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', $v)));
        return mb_substr($v, 0, $max);
    }

    private static function link($v): string
    {
        $v = trim((string) (is_scalar($v) ? $v : ''));
        if ($v === '') return '';
        if (!preg_match('#^https?://#i', $v)) $v = 'https://' . ltrim($v, '/');
        return filter_var($v, FILTER_VALIDATE_URL) && mb_strlen($v) <= 300 ? $v : '';
    }

    public static function newId(): string
    {
        return 's' . strtolower(\Illuminate\Support\Str::random(8));
    }

    // ─────────────────────────────────────────────────────────────── Affichage

    /** Texte d'un champ : celui du vendeur, sinon celui du thème. */
    public static function txt(string $theme, array $sec, string $key): string
    {
        $v = $sec['d'][$key] ?? '';
        return is_string($v) && $v !== '' ? $v : (string) (self::DEFAULTS[$theme][$sec['type']][$key] ?? '');
    }

    /** Engagement n° $i (titre et texte) : celui du vendeur, sinon celui du thème. */
    public static function item(string $theme, array $sec, int $i): array
    {
        $mine = $sec['d']['items'][$i] ?? [];
        $def = self::DEFAULTS[$theme]['promise']['items'][$i] ?? ['title' => '', 'text' => ''];
        return ['title' => ($mine['title'] ?? '') !== '' ? $mine['title'] : $def['title'], 'text' => ($mine['text'] ?? '') !== '' ? $mine['text'] : $def['text']];
    }

    /** Adresse d'une photo importée (grand format). */
    public static function imgUrl(?string $path): ?string
    {
        if (!$path) return null;
        return ImageOptimizer::url(str_replace('/medium/', '/large/', $path), 'medium') ?? ImageOptimizer::url($path) ?? asset('storage/' . $path);
    }

    /** La section est-elle affichée ? (pour les liens du menu de la page) */
    public static function shows(array $list, string $type): bool
    {
        foreach ($list as $s) if ($s['type'] === $type && $s['on']) return true;
        return false;
    }
}
