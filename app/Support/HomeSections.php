<?php

namespace App\Support;

use App\Services\ImageOptimizer;

/**
 * Sections de la page d'accueil de la vitrine, réglées dans « Boutique → Sections ».
 * Chaque thème a sa propre liste, dans l'ordre d'affichage : shops.storefront['home'][thème].
 *
 *  - Sections du thème (une de chaque, à masquer, déplacer et dont on réécrit les textes) :
 *    un texte laissé vide garde le texte prévu par le thème (affiché en exemple dans l'éditeur).
 *  - Sections ajoutées par le vendeur (plusieurs possibles, à supprimer), rangées comme dans la
 *    bibliothèque : Contenu, Preuve sociale, Média, Commerce (voir CATEGORIES).
 *
 * Honnêteté : les chiffres de « Résultats » sont calculés (jamais saisis) ; les témoignages sont
 * écrits par le vendeur, vides au départ, avec un rappel que seuls de vrais avis sont permis.
 * Toute entrée passe par normalize() : types connus, textes nettoyés et bornés, photos importées
 * par la boutique seulement (covers/medium/…webp), liens http(s), vidéos YouTube, TikTok, Facebook.
 */
class HomeSections
{
    public const CUSTOM_MAX = 20;
    public const LIST_MAX = 8;

    /** [libellé, description]. */
    public const TYPES = [
        // Sections des thèmes
        'hero'         => ['Accueil', 'Grand titre, texte et bouton en haut de la page'],
        'promise'      => ['Engagements', 'Trois engagements : livraison, paiement, suivi…'],
        'categories'   => ['Catégories', 'Vos catégories en tuiles photo'],
        'products'     => ['Produits', 'Vos produits phares, prêts à commander'],
        'spotlight'    => ['Produit vedette', 'Un produit mis en avant, en grand'],
        'how'          => ['Comment commander', 'Les 3 étapes de la commande'],
        'results'      => ['Chiffres réels', 'Note moyenne et commandes livrées (s’il y en a)'],
        'reviews'      => ['Avis vérifiés', 'Les avis laissés sur Shopio par vos clients livrés'],
        'faq'          => ['Questions fréquentes', 'Questions et réponses'],
        'studio'       => ['Cabine d’essayage', 'Le client compose sa tenue (ou son coffret) et l’essaie en direct'],
        // Sections de la page produit (contenu de chaque produit : écran « Page de vente »)
        'p_info'       => ['Informations', 'Volets « Comment l’utiliser ? », « Description détaillée », « Livraison et garantie »'],
        'p_highlight'  => ['Garantie avec photos', 'Titre, texte et bouton « Je le veux », avec vos photos inclinées'],
        'p_reviews'    => ['Témoignages vérifiés', 'Les avis laissés sur Shopio par vos clients livrés'],
        'p_band'       => ['Bandeau marque', 'Votre nom (ou un texte) qui défile sur une bande de couleur'],
        'p_story'      => ['Image + Texte', 'Une photo du produit à côté d’un texte (ex. « Avant / Après »)'],
        'p_audience'   => ['Image + Texte 2', 'Un deuxième bloc photo et texte (ex. « C’est pour qui ? »)'],
        'p_results'    => ['Résultats prouvés', 'Part d’avis 4–5 étoiles, note moyenne, commandes livrées (vrais chiffres)'],
        'p_love'       => ['Pourquoi l’aimer', 'Les points forts du produit, avec une photo'],
        'p_compare'    => ['Comparaison', '« Notre produit » face aux autres'],
        'p_related'    => ['Vous aimerez aussi', 'D’autres produits de la boutique'],
        'p_faq'        => ['FAQ 2 colonnes', 'Les questions du produit, sinon celles prévues par Shopio'],
        // Contenu
        'icons'        => ['Barre d’icônes', 'Des icônes avec un titre court : livraison, paiement, retours…'],
        'text'         => ['Texte', 'Un titre et un paragraphe : votre histoire, vos conditions…'],
        'imgtext'      => ['Image et texte', 'Une photo à côté d’un titre, d’un texte et d’un bouton'],
        'qa'           => ['FAQ', 'Vos questions et réponses, en accordéon'],
        'faq2'         => ['FAQ 2 colonnes', 'Le titre à gauche, les questions à droite'],
        'marquee'      => ['Barre d’annonces', 'Des messages qui défilent sur une bande de couleur'],
        'divider'      => ['Séparateur', 'Une vague, un trait ou un espace entre deux sections'],
        // Preuve sociale
        'quotes'       => ['Avis clients', 'Des cartes d’avis avec étoiles, écrits à partir de vrais retours'],
        'fbposts'      => ['Témoignages Facebook', 'Des avis présentés comme des publications Facebook'],
        'testimonials' => ['Témoignages en photos', 'Grandes photos de clients avec leur témoignage'],
        'stats'        => ['Résultats', 'Vos vrais chiffres : note moyenne, commandes livrées, produits'],
        'compare'      => ['Tableau de comparaison', '« Pourquoi nous choisir ? » : votre boutique face aux autres'],
        // Média
        'slideshow'    => ['Diaporama', 'Des grandes photos qui défilent, avec titre et bouton'],
        'videos'       => ['Grille vidéos', 'Vos vidéos YouTube, TikTok ou Facebook côte à côte'],
        'reel'         => ['Vitrine vidéo', '« Le produit en action » : vidéos verticales à faire défiler'],
        // Commerce
        'banner'       => ['Bannière', 'Un message en couleur avec un bouton : promo, nouveauté…'],
        'promo'        => ['Bannière code promo', 'Un code promo à copier en un clic'],
        'trust'        => ['Paiement en confiance', 'Une phrase rassurante et les moyens de paiement acceptés'],
        'category'     => ['Produits d’une catégorie', 'Une sélection de produits d’une de vos catégories'],
    ];

    /** Bibliothèque des sections à ajouter, par onglet. */
    public const CATEGORIES = [
        'contenu'  => ['Contenu', ['icons', 'text', 'imgtext', 'qa', 'faq2', 'marquee', 'divider']],
        'preuve'   => ['Preuve sociale', ['quotes', 'fbposts', 'testimonials', 'stats', 'compare']],
        'media'    => ['Média', ['slideshow', 'videos', 'reel']],
        'commerce' => ['Commerce', ['banner', 'promo', 'trust', 'category']],
    ];

    /** Sections propres à chaque thème, dans l'ordre par défaut. */
    public const BUILTIN = [
        'express'   => ['hero', 'promise', 'products', 'results', 'reviews', 'faq'],
        'signature' => ['hero', 'promise', 'categories', 'products', 'spotlight', 'how', 'reviews', 'faq'],
        'cabine'    => ['studio', 'promise', 'how', 'reviews', 'faq'],
        // Page produit (une seule mise en page pour tous les produits) : sous le haut de page fixe
        // (photos, prix, packs, commande), qui reste toujours en premier
        'product'   => ['p_info', 'p_highlight', 'p_reviews', 'p_band', 'p_story', 'p_audience', 'p_results', 'p_love', 'p_compare', 'p_faq', 'p_related'],
    ];

    /** Pages modifiables dans l'éditeur : [libellé, thème de rendu]. */
    public const PAGES = ['express' => ['Accueil · Express', 'express'], 'signature' => ['Accueil · Signature', 'signature'], 'cabine' => ['Accueil · Cabine', 'cabine'], 'product' => ['Page produit', 'express']];

    /** Sections que le vendeur peut ajouter (dans les deux thèmes). */
    public const ADDABLE = ['icons', 'text', 'imgtext', 'qa', 'faq2', 'marquee', 'divider', 'quotes', 'fbposts', 'testimonials', 'stats', 'compare', 'slideshow', 'videos', 'reel', 'banner', 'promo', 'trust', 'category'];

    /** Sections où le vendeur écrit des avis : rappel « vrais clients seulement » dans l'éditeur. */
    public const TESTIMONIAL_TYPES = ['quotes', 'fbposts', 'testimonials'];

    /** Icônes proposées (barre d'icônes) : [libellé, tracé SVG 24×24]. */
    public const ICONS = [
        'truck'  => ['Livraison', 'M1 6h13v10H1zM14 9h4l4 4v3h-8zM6 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM18 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4z'],
        'cash'   => ['Paiement', 'M2 6h20v12H2zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z'],
        'return' => ['Retour', 'M3 12a9 9 0 1 0 3-6.7M3 4v5h5'],
        'shield' => ['Garantie', 'M12 2l8 3v6c0 5-3.4 9.4-8 11-4.6-1.6-8-6-8-11V5zM8.5 12l2.5 2.5 4.5-5'],
        'phone'  => ['Téléphone', 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2'],
        'star'   => ['Qualité', 'm12 2 3 6.5 7 .8-5.2 4.8 1.4 7L12 17.6 5.8 21l1.4-7L2 9.3l7-.8z'],
        'gift'   => ['Cadeau', 'M3 9h18v4H3zM5 13h14v8H5zM12 9v12M12 9S10.5 4 8 4a2.5 2.5 0 0 0 0 5M12 9s1.5-5 4-5a2.5 2.5 0 0 1 0 5'],
        'clock'  => ['Rapidité', 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 7v5l3 2'],
        'chat'   => ['Contact', 'M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z'],
        'check'  => ['Vérifié', 'M20 6 9 17l-5-5'],
    ];

    /**
     * Champs modifiables : [clé, sorte, libellé, longueur max]. Sortes : line, text, img, link, bool,
     * side, align, tone, cat, limit, format, style (choix fixes), items (3 engagements), faq, icons,
     * rows (comparaison), people (témoignages), slides, videos, lines (annonces).
     */
    public const FIELDS = [
        'hero'         => [['title', 'line', 'Titre', 90], ['text', 'text', 'Texte', 300], ['button', 'line', 'Texte du bouton', 30], ['img', 'img', 'Photo (sinon votre produit phare)', 0]],
        'promise'      => [['items', 'items', 'Engagements', 0]],
        'categories'   => [['title', 'line', 'Titre', 60]],
        'products'     => [['title', 'line', 'Titre', 60], ['text', 'line', 'Sous-titre', 160]],
        'spotlight'    => [],
        'how'          => [['title', 'line', 'Titre', 60], ['text', 'text', 'Texte', 200]],
        'results'      => [['title', 'line', 'Titre', 60]],
        'reviews'      => [['title', 'line', 'Titre', 60]],
        'faq'          => [['title', 'line', 'Titre', 80], ['items', 'faq', 'Questions (vide = questions prévues par Shopio)', 0]],
        'studio'       => [['title', 'line', 'Titre (vide = nom de la boutique)', 60], ['text', 'line', 'Phrase d’accroche', 160], ['button', 'line', 'Texte du bouton de commande', 30],
                           ['img', 'img', 'Photo d’ambiance (bouton « Pièce » de la cabine, facultatif)', 0],
                           ['z1', 'cat', 'Rayon 1 (vide = choisi automatiquement)', 100], ['z2', 'cat', 'Rayon 2 (vide = automatique)', 100],
                           ['z3', 'cat', 'Rayon 3 (vide = automatique)', 100], ['z4', 'cat', 'Rayon 4 (vide = automatique)', 100]],
        'p_info'       => [],
        'p_highlight'  => [],
        'p_audience'   => [],
        'p_reviews'    => [['title', 'line', 'Titre', 80]],
        'p_band'       => [],
        'p_story'      => [],
        'p_results'    => [['title', 'line', 'Titre', 60]],
        'p_love'       => [],
        'p_compare'    => [],
        'p_related'    => [['title', 'line', 'Titre', 60]],
        'p_faq'        => [['title', 'line', 'Titre', 80]],
        'icons'        => [['title', 'line', 'Titre (facultatif)', 80], ['items', 'icons', 'Icônes', 0]],
        'text'         => [['title', 'line', 'Titre', 90], ['text', 'text', 'Texte', 1500], ['align', 'align', 'Alignement', 0]],
        'imgtext'      => [['img', 'img', 'Photo', 0], ['title', 'line', 'Titre', 90], ['text', 'text', 'Texte', 800], ['button', 'line', 'Texte du bouton', 30], ['link', 'link', 'Lien du bouton (vide = vos produits)', 300], ['side', 'side', 'Photo à', 0]],
        'qa'           => [['title', 'line', 'Titre', 80], ['items', 'faq', 'Questions', 0]],
        'faq2'         => [['title', 'line', 'Titre', 80], ['text', 'line', 'Sous-titre', 160], ['items', 'faq', 'Questions', 0]],
        'marquee'      => [['items', 'lines', 'Messages qui défilent', 0], ['tone', 'tone', 'Couleur', 0]],
        'divider'      => [['style', 'style', 'Forme', 0]],
        'quotes'       => [['title', 'line', 'Titre', 80], ['items', 'people', 'Avis', 0]],
        'fbposts'      => [['title', 'line', 'Titre', 80], ['items', 'people', 'Publications', 0]],
        'testimonials' => [['title', 'line', 'Titre', 60], ['title2', 'line', 'Suite du titre (en italique)', 60], ['text', 'line', 'Texte', 200], ['items', 'people', 'Témoignages', 0]],
        'stats'        => [['title', 'line', 'Titre', 80]],
        'compare'      => [['title', 'line', 'Titre', 80], ['text', 'line', 'Texte', 200], ['us', 'line', 'Votre colonne (vide = nom de la boutique)', 24], ['them', 'line', 'Colonne d’en face', 24], ['items', 'rows', 'Lignes', 0]],
        'slideshow'    => [['items', 'slides', 'Diapositives', 0], ['auto', 'bool', 'Défilement automatique', 0]],
        'videos'       => [['title', 'line', 'Titre', 80], ['items', 'videos', 'Vidéos', 0], ['format', 'format', 'Format', 0]],
        'reel'         => [['title', 'line', 'Titre', 80], ['items', 'videos', 'Vidéos (format vertical conseillé)', 0]],
        'banner'       => [['title', 'line', 'Message', 140], ['text', 'line', 'Petit texte', 160], ['button', 'line', 'Texte du bouton', 30], ['link', 'link', 'Lien du bouton (vide = vos produits)', 300], ['tone', 'tone', 'Couleur', 0]],
        'promo'        => [['text', 'line', 'Message', 100], ['code', 'code', 'Code promo', 30], ['tone', 'tone', 'Couleur', 0]],
        'trust'        => [['text', 'line', 'Phrase', 120], ['shield', 'bool', 'Afficher le bouclier', 0], ['cash', 'bool', 'Espèces à la livraison', 0], ['om', 'bool', 'Orange Money', 0], ['momo', 'bool', 'MTN Mobile Money', 0]],
        'category'     => [['title', 'line', 'Titre (vide = nom de la catégorie)', 60], ['cat', 'cat', 'Catégorie', 100], ['limit', 'limit', 'Nombre de produits', 0]],
    ];

    /** Textes prévus (affichés quand le champ est vide, et en exemple dans l'éditeur). */
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
        'cabine' => [
            'studio'  => ['title' => '', 'text' => 'Composez votre tenue, essayez-la en direct, payez à la livraison.', 'button' => 'Commander le look'],
            'promise' => ['items' => [['title' => 'Paiement à la livraison', 'text' => 'En espèces, au livreur'], ['title' => 'Appel de confirmation', 'text' => 'La boutique vous appelle avant l’envoi'], ['title' => 'Suivi de commande', 'text' => 'Chaque étape sur Shopio']]],
            'how'     => ['title' => 'Essayer, puis commander', 'text' => 'Touchez une pièce pour l’essayer, changez-la autant que vous voulez, puis commandez le look entier ou une seule pièce. Vous payez à la livraison.'],
            'reviews' => ['title' => 'Ce qu’en disent nos clients'],
            'faq'     => ['title' => 'Les réponses à vos questions'],
        ],
        'product' => [
            'p_reviews' => ['title' => 'Testé et approuvé par des clients de la boutique'],
            'p_results' => ['title' => 'Des résultats prouvés'],
            'p_related' => ['title' => 'Vous aimerez aussi'],
            'p_faq'     => ['title' => 'Les réponses à vos questions importantes'],
        ],
        // Sections ajoutées : mêmes textes dans les deux thèmes
        'custom' => [
            'qa'           => ['title' => 'Questions fréquentes'],
            'faq2'         => ['title' => 'Les réponses à vos questions'],
            'quotes'       => ['title' => 'Avis clients'],
            'fbposts'      => ['title' => 'Ils en parlent sur Facebook'],
            'testimonials' => ['title' => 'Des clients satisfaits', 'title2' => 'partout dans le pays', 'text' => 'Livraison rapide, paiement à la réception, produits conformes aux photos.'],
            'stats'        => ['title' => 'Des résultats qui parlent'],
            'compare'      => ['title' => 'Pourquoi nous choisir ?', 'text' => 'Découvrez ce qui nous différencie.', 'us' => '', 'them' => 'Autres'],
            'videos'       => ['title' => 'En vidéo'],
            'reel'         => ['title' => 'Le produit en action'],
            'promo'        => ['text' => 'Profitez de −10 % avec le code :'],
            'trust'        => ['text' => 'Commandez en toute confiance : paiement à la réception'],
        ],
    ];

    /** Couleurs (bannière, code promo, barre d'annonces). */
    public const TONES = ['accent' => 'Couleur de la boutique', 'dark' => 'Sombre', 'soft' => 'Claire'];
    public const STYLES = ['wave' => 'Vague', 'line' => 'Trait', 'space' => 'Espace'];
    public const FORMATS = ['square' => 'Carré', 'portrait' => 'Portrait', 'landscape' => 'Paysage'];

    // ─────────────────────────────────────────────────────────────── Données

    /** Liste par défaut d'un thème : ses sections, toutes affichées, textes du thème. */
    public static function defaults(string $theme): array
    {
        return array_map(fn ($t) => ['id' => $t, 'type' => $t, 'on' => true, 'sp' => [0, 0], 'd' => self::blank($t)], self::BUILTIN[$theme] ?? self::BUILTIN['express']);
    }

    /** Contenu vide d'une section (champs vides = textes prévus). */
    public static function blank(string $type): array
    {
        $d = [];
        foreach (self::FIELDS[$type] ?? [] as [$k, $kind]) {
            $d[$k] = match ($kind) {
                'items'  => [['title' => '', 'text' => ''], ['title' => '', 'text' => ''], ['title' => '', 'text' => '']],
                'faq', 'icons', 'rows', 'people', 'slides', 'videos', 'lines' => [],
                'img'    => null,
                'bool'   => true,
                'side'   => 'left',
                'tone'   => 'accent',
                'align'  => 'left',
                'style'  => 'wave',
                'format' => 'portrait',
                'limit'  => 4,
                default  => '',
            };
        }
        if ($type === 'slideshow') $d['auto'] = false;
        if ($type === 'promo') $d['tone'] = 'dark';
        if ($type === 'trust') { $d['om'] = false; $d['momo'] = false; } // moyens acceptés : cochés par le vendeur
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
            $sp = is_array($s['sp'] ?? null) ? array_values($s['sp']) : [0, 0];
            $out[] = ['id' => $id, 'type' => $type, 'on' => filter_var($s['on'] ?? true, FILTER_VALIDATE_BOOLEAN), 'sp' => [max(0, min(120, (int) ($sp[0] ?? 0))), max(0, min(120, (int) ($sp[1] ?? 0)))], 'd' => self::normalizeData($type, is_array($s['d'] ?? null) ? $s['d'] : [])];
        }
        // Section du thème absente (liste ancienne, envoi incomplet) : remise à sa place par défaut
        foreach ($builtin as $i => $type) {
            if (isset($seen[$type])) continue;
            $after = $i > 0 ? collect($out)->search(fn ($s) => $s['type'] === $builtin[$i - 1]) : -1;
            if ($after === -1) { array_unshift($out, ['id' => $type, 'type' => $type, 'on' => true, 'sp' => [0, 0], 'd' => self::blank($type)]); continue; }
            array_splice($out, $after === false ? count($out) : $after + 1, 0, [['id' => $type, 'type' => $type, 'on' => true, 'sp' => [0, 0], 'd' => self::blank($type)]]);
        }
        // Ids uniques (deux sections ajoutées ne partagent jamais un id)
        $ids = [];
        foreach ($out as &$s) { while (isset($ids[$s['id']])) $s['id'] = self::newId(); $ids[$s['id']] = true; }
        unset($s);
        return array_values($out);
    }

    public static function normalizeData(string $type, array $d): array
    {
        $blank = self::blank($type);
        $out = [];
        foreach (self::FIELDS[$type] ?? [] as [$k, $kind, , $max]) {
            $v = $d[$k] ?? null;
            $list = fn () => is_array($v) ? array_values(array_filter($v, 'is_array')) : [];
            $out[$k] = match ($kind) {
                'line', 'cat' => self::line($v, $max),
                'code'   => strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', self::line($v, $max))),
                'text'   => self::text($v, $max),
                'img'    => self::img($v),
                'link'   => self::link($v),
                'bool'   => $v === null ? $blank[$k] : filter_var($v, FILTER_VALIDATE_BOOLEAN),
                'side'   => in_array($v, ['left', 'right'], true) ? $v : 'left',
                'align'  => in_array($v, ['left', 'center'], true) ? $v : 'left',
                'tone'   => isset(self::TONES[$v]) ? $v : $blank[$k],
                'style'  => isset(self::STYLES[$v]) ? $v : 'wave',
                'format' => isset(self::FORMATS[$v]) ? $v : 'portrait',
                'limit'  => in_array((int) $v, [4, 8], true) ? (int) $v : 4,
                'items'  => array_map(fn ($i) => [
                    'title' => self::line(is_array($v) ? ($v[$i]['title'] ?? '') : '', 40),
                    'text'  => self::line(is_array($v) ? ($v[$i]['text'] ?? '') : '', 80),
                ], [0, 1, 2]),
                'faq'    => self::cap(array_filter(array_map(fn ($q) => ['q' => self::line($q['q'] ?? '', 150), 'a' => self::text($q['a'] ?? '', 600)], $list()), fn ($q) => $q['q'] !== '' || $q['a'] !== '')),
                'icons'  => self::cap(array_filter(array_map(fn ($i) => ['icon' => isset(self::ICONS[$i['icon'] ?? '']) ? $i['icon'] : 'check', 'title' => self::line($i['title'] ?? '', 40), 'text' => self::line($i['text'] ?? '', 80)], $list()), fn ($i) => $i['title'] !== '' || $i['text'] !== '')),
                'rows'   => self::cap(array_filter(array_map(fn ($r) => ['label' => self::line($r['label'] ?? '', 60), 'us' => filter_var($r['us'] ?? true, FILTER_VALIDATE_BOOLEAN), 'them' => filter_var($r['them'] ?? false, FILTER_VALIDATE_BOOLEAN)], $list()), fn ($r) => $r['label'] !== '')),
                'people' => self::cap(array_filter(array_map(fn ($p) => [
                    'img' => self::img($p['img'] ?? null), 'name' => self::line($p['name'] ?? '', 40), 'label' => self::line($p['label'] ?? '', 40),
                    'title' => self::line($p['title'] ?? '', 80), 'text' => self::text($p['text'] ?? '', 400), 'rating' => max(1, min(5, (int) ($p['rating'] ?? 5))),
                ], $list()), fn ($p) => $p['text'] !== '' || $p['title'] !== '')),
                'slides' => self::cap(array_filter(array_map(fn ($s) => [
                    'img' => self::img($s['img'] ?? null), 'title' => self::line($s['title'] ?? '', 80), 'text' => self::line($s['text'] ?? '', 160),
                    'button' => self::line($s['button'] ?? '', 30), 'link' => self::link($s['link'] ?? ''),
                ], $list()), fn ($s) => $s['img'] !== null)),
                'videos' => self::cap(array_filter(array_map(fn ($u) => self::video(is_array($u) ? ($u['url'] ?? '') : $u), is_array($v) ? array_values($v) : []))),
                'lines'  => self::cap(array_filter(array_map(fn ($l) => self::line(is_array($l) ? ($l['text'] ?? '') : $l, 80), is_array($v) ? array_values($v) : []), fn ($l) => $l !== '')),
                default  => '',
            };
        }
        return $out;
    }

    private static function cap(array $items): array
    {
        return array_values(array_slice($items, 0, self::LIST_MAX));
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

    private static function img($v): ?string
    {
        return is_string($v) && preg_match(Storefront::COVER_PATH, $v) ? $v : null;
    }

    private static function link($v): string
    {
        $v = trim((string) (is_scalar($v) ? $v : ''));
        if ($v === '') return '';
        if (!preg_match('#^https?://#i', $v)) $v = 'https://' . ltrim($v, '/');
        return filter_var($v, FILTER_VALIDATE_URL) && mb_strlen($v) <= 300 ? $v : '';
    }

    /** Lien de vidéo accepté : YouTube, TikTok ou Facebook (https). Vide sinon. */
    private static function video($v): string
    {
        $v = trim((string) (is_scalar($v) ? $v : ''));
        if ($v === '' || mb_strlen($v) > 300) return '';
        if (!preg_match('#^https?://#i', $v)) $v = 'https://' . $v;
        $host = strtolower((string) parse_url($v, PHP_URL_HOST));
        foreach (['youtube.com', 'youtu.be', 'tiktok.com', 'facebook.com', 'fb.watch'] as $ok) {
            if ($host === $ok || str_ends_with($host, '.' . $ok)) return preg_replace('#^http://#i', 'https://', $v);
        }
        return '';
    }

    public static function newId(): string
    {
        return 's' . strtolower(\Illuminate\Support\Str::random(8));
    }

    // ─────────────────────────────────────────────────────────────── Affichage

    /** Texte d'un champ : celui du vendeur, sinon celui prévu (thème, ou commun aux sections ajoutées). */
    public static function txt(string $theme, array $sec, string $key): string
    {
        $v = $sec['d'][$key] ?? '';
        if (is_string($v) && $v !== '') return $v;
        return (string) (self::DEFAULTS[$theme][$sec['type']][$key] ?? self::DEFAULTS['custom'][$sec['type']][$key] ?? '');
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

    /** Toutes les photos d'une liste de sections (pour les miniatures de l'éditeur). */
    public static function images(array $home): array
    {
        $paths = [];
        array_walk_recursive($home, function ($v, $k) use (&$paths) { if ($k === 'img' && is_string($v)) $paths[$v] = true; });
        return collect(array_keys($paths))->mapWithKeys(fn ($p) => [$p => self::imgUrl($p)])->all();
    }

    /** Vidéo prête à afficher : lecteur YouTube intégré, sinon lien vers TikTok / Facebook. */
    public static function videoEmbed(string $url): ?array
    {
        $v = Storefront::video($url);
        if (!$v) return null;
        if ($v['kind'] === 'youtube') return ['kind' => 'youtube', 'src' => 'https://www.youtube-nocookie.com/embed/' . $v['id'] . '?rel=0&playsinline=1', 'thumb' => 'https://i.ytimg.com/vi/' . $v['id'] . '/hqdefault.jpg'];
        return ['kind' => 'link', 'url' => $url, 'site' => $v['site']];
    }

    /** La section est-elle affichée ? (pour les liens du menu de la page) */
    public static function shows(array $list, string $type): bool
    {
        foreach ($list as $s) if ($s['type'] === $type && $s['on']) return true;
        return false;
    }
}
