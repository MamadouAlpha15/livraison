<?php

namespace App\Support;

use App\Models\Product;

/**
 * Mise en page et réglages de la page produit, produit par produit (éditeur produit : onglets Sections,
 * Couleurs, Formulaire, Offres). Rangés dans products.landing['design'] ; le formulaire de commande peut
 * aussi être réglé pour toute la boutique (shops.storefront['form']).
 *
 *   buy     Bloc d'achat (à côté des photos), dans l'ordre : [{type, on, sp: [haut, bas]}]
 *   page    Sections sous le bloc d'achat (format App\Support\HomeSections, liste « product ») ; absent =
 *           la mise en page de la boutique (Boutique → Sections → Page produit)
 *   sticky  Barre « Commander » flottante sur mobile
 *   rating  {mention} : mot devant la note (« Excellent »…). La note et le nombre d'avis restent les vrais.
 *   trust   {text, cash, om, momo} : « Paiement en confiance »
 *   band    {text, bg, ink} : bandeau au nom de la boutique
 *   colors  {accent, bg, foot_bg, foot_ink, font_title, font_body} : vide = couleurs et polices du thème
 *   offer   {show, title, subtitle, image, hide_strike, unit, frame, preselect} : bloc des packs
 *   form    réglages propres au produit (quand form_own) ; sinon ceux de la boutique
 *
 * Tout passe par normalize() : types connus, couleurs #RRGGBB, textes bornés.
 */
class ProductDesign
{
    /** Bloc d'achat : [libellé, peut être masqué ici, onglet qui le règle]. */
    public const BUY = [
        'rating'   => ['Bandeau note', true, null],
        'title'    => ['Titre du produit', false, 'infos'],
        'hook'     => ['Accroche', true, null],
        'price'    => ['Prix', false, 'infos'],
        'benefits' => ['Liste de bénéfices', true, null],
        'delivery' => ['Suivi de livraison', true, null],
        'offer'    => ['Offre', false, 'offres'],
        'qty'      => ['Sélecteur de quantité', true, null],
        'addcart'  => ['Bouton Ajouter au panier', true, null],
        'form'     => ['Formulaire de commande', false, 'formulaire'],
        'trust'    => ['Paiement en confiance', true, null],
    ];

    public const FONTS = ['' => 'Celle du thème', 'Inter' => 'Inter', 'Poppins' => 'Poppins', 'Montserrat' => 'Montserrat', 'DM Sans' => 'DM Sans', 'Plus Jakarta Sans' => 'Plus Jakarta Sans', 'Playfair Display' => 'Playfair Display', 'Lora' => 'Lora', 'Fraunces' => 'Fraunces'];
    public const ANIMS = ['none' => 'Aucune', 'shake' => 'Tremblement', 'bounce' => 'Rebond', 'slide' => 'Glissement'];
    public const INTENSITIES = ['soft' => 'Doux', 'medium' => 'Moyen', 'strong' => 'Fort'];
    public const SPACE_MAX = 120;

    // ─────────────────────────────────────────────────────────────── Valeurs par défaut

    public static function defaults(): array
    {
        return [
            'buy'       => array_map(fn ($t) => ['type' => $t, 'on' => true, 'sp' => [0, 0]], array_keys(self::BUY)),
            'page'      => null,
            'sticky'    => true,
            'rating'    => ['mention' => ''],
            'trust'     => ['text' => 'Commandez en toute confiance — paiement à la réception', 'cash' => true, 'om' => false, 'momo' => false],
            'band'      => ['text' => '', 'bg' => null, 'ink' => null],
            'colors'    => ['accent' => null, 'bg' => null, 'foot_bg' => null, 'foot_ink' => null, 'font_title' => '', 'font_body' => ''],
            'offer'     => ['show' => true, 'title' => 'Pack et économie', 'subtitle' => 'Plus vous achetez, plus vous économisez', 'image' => true, 'hide_strike' => false, 'unit' => false, 'frame' => null, 'preselect' => 1],
            'form_own'  => false,
            'form'      => self::formDefaults(),
        ];
    }

    public static function formDefaults(): array
    {
        return [
            'integrated' => true, 'anim' => 'none', 'intensity' => 'medium',
            'bg' => null, 'border' => null, 'title_c' => null, 'label_c' => null,
            'title' => 'Finaliser la commande', 'button' => 'Confirmer la commande', 'note' => '💵 Paiement à la livraison, en espèces, au livreur',
            'address' => true, 'address_req' => false,
        ];
    }

    // ─────────────────────────────────────────────────────────────── Nettoyage

    public static function normalize($in): array
    {
        $d = self::defaults();
        $in = is_array($in) ? $in : [];
        $bool = fn ($v, $def) => $v === null ? $def : filter_var($v, FILTER_VALIDATE_BOOLEAN);

        // Bloc d'achat : chaque élément une fois, ceux qui manquent remis à leur place par défaut
        $buy = []; $seen = [];
        foreach (is_array($in['buy'] ?? null) ? $in['buy'] : [] as $b) {
            $t = is_array($b) ? ($b['type'] ?? '') : '';
            if (!isset(self::BUY[$t]) || isset($seen[$t])) continue;
            $seen[$t] = true;
            $buy[] = ['type' => $t, 'on' => self::BUY[$t][1] ? $bool($b['on'] ?? null, true) : true, 'sp' => self::space($b['sp'] ?? null)];
        }
        foreach (array_keys(self::BUY) as $i => $t) {
            if (isset($seen[$t])) continue;
            array_splice($buy, min($i, count($buy)), 0, [['type' => $t, 'on' => true, 'sp' => [0, 0]]]);
        }

        $r = is_array($in['rating'] ?? null) ? $in['rating'] : [];
        $tr = is_array($in['trust'] ?? null) ? $in['trust'] : [];
        $bd = is_array($in['band'] ?? null) ? $in['band'] : [];
        $co = is_array($in['colors'] ?? null) ? $in['colors'] : [];
        $of = is_array($in['offer'] ?? null) ? $in['offer'] : [];

        return [
            'buy'      => $buy,
            'page'     => is_array($in['page'] ?? null) && $in['page'] ? HomeSections::normalize('product', $in['page']) : null,
            'sticky'   => $bool($in['sticky'] ?? null, true),
            'rating'   => ['mention' => self::line($r['mention'] ?? '', 20)],
            'trust'    => [
                'text' => self::line($tr['text'] ?? $d['trust']['text'], 120),
                'cash' => $bool($tr['cash'] ?? null, true), 'om' => $bool($tr['om'] ?? null, false), 'momo' => $bool($tr['momo'] ?? null, false),
            ],
            'band'     => ['text' => self::line($bd['text'] ?? '', 40), 'bg' => self::hex($bd['bg'] ?? null), 'ink' => self::hex($bd['ink'] ?? null)],
            'colors'   => [
                'accent' => self::hex($co['accent'] ?? null), 'bg' => self::hex($co['bg'] ?? null),
                'foot_bg' => self::hex($co['foot_bg'] ?? null), 'foot_ink' => self::hex($co['foot_ink'] ?? null),
                'font_title' => isset(self::FONTS[$co['font_title'] ?? '']) ? (string) ($co['font_title'] ?? '') : '',
                'font_body' => isset(self::FONTS[$co['font_body'] ?? '']) ? (string) ($co['font_body'] ?? '') : '',
            ],
            'offer'    => [
                'show' => $bool($of['show'] ?? null, true),
                'title' => self::line($of['title'] ?? $d['offer']['title'], 50), 'subtitle' => self::line($of['subtitle'] ?? $d['offer']['subtitle'], 80),
                'image' => $bool($of['image'] ?? null, true), 'hide_strike' => $bool($of['hide_strike'] ?? null, false),
                'unit' => $bool($of['unit'] ?? null, false), 'frame' => self::hex($of['frame'] ?? null),
                'preselect' => max(1, min(10, (int) ($of['preselect'] ?? 1))),
            ],
            'form_own' => $bool($in['form_own'] ?? null, false),
            'form'     => self::normalizeForm($in['form'] ?? null),
        ];
    }

    public static function normalizeForm($f): array
    {
        $d = self::formDefaults();
        $f = is_array($f) ? $f : [];
        $bool = fn ($k) => array_key_exists($k, $f) ? filter_var($f[$k], FILTER_VALIDATE_BOOLEAN) : $d[$k];
        return [
            'integrated' => $bool('integrated'),
            'anim' => isset(self::ANIMS[$f['anim'] ?? '']) ? $f['anim'] : 'none',
            'intensity' => isset(self::INTENSITIES[$f['intensity'] ?? '']) ? $f['intensity'] : 'medium',
            'bg' => self::hex($f['bg'] ?? null), 'border' => self::hex($f['border'] ?? null),
            'title_c' => self::hex($f['title_c'] ?? null), 'label_c' => self::hex($f['label_c'] ?? null),
            'title' => self::line($f['title'] ?? $d['title'], 60) ?: $d['title'],
            'button' => self::line($f['button'] ?? $d['button'], 40) ?: $d['button'],
            'note' => self::line($f['note'] ?? $d['note'], 100),
            'address' => $bool('address'), 'address_req' => $bool('address') && $bool('address_req'),
        ];
    }

    /** Réglages d'un produit, prêts pour la vue : la mise en page et le formulaire de la boutique servent de repli. */
    public static function for(Product $p, array $sf): array
    {
        $d = self::normalize(is_array($p->landing) ? ($p->landing['design'] ?? null) : null);
        $d['own_page'] = $d['page'] !== null;
        if (!$d['own_page']) $d['page'] = $sf['home']['product'] ?? HomeSections::defaults('product');
        if (!$d['form_own']) $d['form'] = self::normalizeForm($sf['form'] ?? null);
        return $d;
    }

    private static function space($v): array
    {
        $v = is_array($v) ? array_values($v) : [0, 0];
        $c = fn ($x) => max(0, min(self::SPACE_MAX, (int) $x));
        return [$c($v[0] ?? 0), $c($v[1] ?? 0)];
    }

    private static function hex($v): ?string
    {
        return is_string($v) && preg_match('/^#[0-9A-Fa-f]{6}$/', $v) ? strtoupper($v) : null;
    }

    private static function line($v, int $max): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags(mb_scrub((string) (is_scalar($v) ? $v : ''), 'UTF-8')))), 0, $max);
    }

    /** Encre lisible (noir ou blanc) sur une couleur. */
    public static function ink(string $hex): string
    {
        return Storefront::inkFor($hex);
    }
}
