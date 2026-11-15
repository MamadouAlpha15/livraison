<?php

namespace App\PageBuilder\Templates;

use App\Models\PageTemplate;
use App\PageBuilder\PageFactory;
use App\PageBuilder\Schema;
use Illuminate\Support\Facades\Cache;

/**
 * Modèles de page fournis par Shopio (page_templates sans boutique). Définis ici, installés en base par
 * sync() : au premier passage dans l'éditeur, puis à chaque changement de VERSION (ou « page-templates:sync »).
 *
 * Règle commune : aucun texte qui affirme quelque chose de faux. Les modèles n'utilisent que des blocs qui
 * s'appuient sur des données réelles (description, avis, stock, vente flash, délais) ou des faits vrais sur
 * Shopio (paiement à la livraison, suivi du livreur). Une section sans contenu reste cachée aux clients.
 */
class ShopioTemplates
{
    /** À augmenter quand les définitions changent : les modèles en base sont alors mis à jour */
    public const VERSION = 1;

    private const CACHE_KEY = 'page-builder:shopio-templates';

    /** [clé => [name, category, description, page]] */
    public static function definitions(): array
    {
        $desc = ['collapsible', ['heading' => 'Description', 'source' => 'description', 'icon' => 'info']];
        $descOpen = ['collapsible', ['heading' => 'Description', 'source' => 'description', 'icon' => 'info', 'open' => true]];
        $form = ['order_form', []];
        $formWa = ['order_form', ['show_whatsapp' => true]];

        return [
            'classique' => ['Classique', 'general', 'La plupart des produits. Offres groupées, puis le formulaire juste en dessous.',
                self::page([['title', []], ['rating', []], ['price', []], ['stock', []], ['variants', []], ['offers', []], $formWa, ['badges', []], ['delivery', []], $desc],
                    [], [['steps'], ['reviews'], ['faq']])],
            'express' => ['Express', 'general', 'Pubs Facebook et TikTok, petits prix : le formulaire arrive tout de suite sous le prix.',
                self::page([['title', []], ['price', []], ['offers', []], ['variants', []], $form, ['badges', ['layout' => 'row']], $desc],
                    ['gallery_layout' => 'slider'], [['faq']])],
            'confiance' => ['Confiance', 'general', 'Produits chers : avis, engagements et réponses aux doutes avant la commande.',
                self::page([['title', []], ['rating', []], ['price', []], ['badges', ['layout' => 'list']], ['variants', []], ['offers', []], $formWa, ['delivery', []], $desc],
                    [], [['reviews'], ['steps'], ['faq']], [['announcement', 0, ['text' => 'Paiement à la livraison : tu vérifies ton colis avant de payer', 'icon' => 'shield']]])],
            'promo' => ['Promo', 'general', 'Ventes flash et prix barrés : le vrai compte à rebours et les lots mis en avant.',
                self::page([['countdown', ['source' => 'flash']], ['title', []], ['price', []], ['stock', []], ['offers', ['selected' => '2']], ['variants', []], $form, ['badges', []], ['delivery', []]],
                    [], [['faq']])],
            'histoire' => ['Histoire', 'general', 'Beauté, bien-être, produits à expliquer : la description en grand, puis les étapes et les avis.',
                self::page([['title', []], ['rating', []], ['price', []], $descOpen, ['variants', []], ['offers', []], $form, ['badges', []], ['delivery', []]],
                    [], [['steps'], ['reviews'], ['faq']])],
            'mode' => ['Mode', 'mode', 'Vêtements, chaussures, accessoires : photos les unes sous les autres, choix de la taille, autres articles.',
                self::page([['title', []], ['rating', []], ['price', []], ['variants', ['label' => 'Choisis ta taille']], ['stock', ['threshold' => 5]], ['quantity', ['max' => 5]], $formWa, ['badges', []], ['delivery', []], $desc],
                    ['gallery_layout' => 'stacked'], [['reviews'], ['product-list', 0, ['heading' => 'Complète ta tenue']], ['faq']])],
            'beaute' => ['Beauté et parfum', 'beaute', 'Soins, parfums, cosmétiques : lots, avant / après (avec tes vraies photos) et avis.',
                self::page([['title', []], ['rating', []], ['price', []], ['stock', []], ['offers', []], $form, ['badges', []], ['delivery', []], $descOpen],
                    [], [['image-with-text', 1], ['reviews'], ['faq']])],
            'electronique' => ['Électronique', 'electronique', 'Téléphones, écouteurs, accessoires : options, vidéo de démonstration, WhatsApp pour les questions.',
                self::page([['title', []], ['rating', []], ['price', []], ['variants', []], ['quantity', ['max' => 3]], $formWa, ['badges', ['layout' => 'list']], ['delivery', []], $desc],
                    [], [['video', 0, ['format' => 'landscape', 'autoplay' => false]], ['reviews'], ['steps'], ['faq']])],
            'unique' => ['Produit unique', 'unique', 'Une seule pièce ou un produit sans lot : simple et direct.',
                self::page([['title', []], ['price', []], ['stock', []], $form, ['badges', []], ['delivery', []], $descOpen],
                    [], [['steps'], ['faq']])],
        ];
    }

    /** Installe ou met à jour les modèles Shopio en base (sans réactiver ceux que l'équipe a désactivés) */
    public static function sync(): void
    {
        $position = 0;
        foreach (self::definitions() as $key => [$name, $category, $description, $page]) {
            $tpl = PageTemplate::firstOrNew(['key' => $key]);
            $tpl->fill(['shop_id' => null, 'name' => $name, 'category' => $category, 'description' => $description, 'data' => $page, 'position' => $position++]);
            $tpl->save();
        }
        Cache::forever(self::CACHE_KEY, self::VERSION);
    }

    /** Synchronise si les modèles en base datent d'une version précédente */
    public static function ensure(): void
    {
        if (Cache::get(self::CACHE_KEY) !== self::VERSION || !PageTemplate::whereNull('shop_id')->exists()) {
            self::sync();
        }
    }

    /**
     * Page : section Produit (blocs [[type, réglages]], réglages de la section), sections après [[type, préréglage, réglages]],
     * sections avant (bannière…).
     */
    private static function page(array $blocks, array $mainSettings, array $after, array $before = []): array
    {
        $main = PageFactory::section('main-product');
        $main['blocks'] = [];
        $main['block_order'] = [];
        foreach ($blocks as [$type, $settings]) {
            $id = Schema::newId($type, $main['block_order']);
            $main['blocks'][$id] = PageFactory::block($type, $settings);
            $main['block_order'][] = $id;
        }
        $main['settings'] = array_replace($main['settings'], $mainSettings);

        $page = ['sections' => [], 'order' => []];
        $add = function (array $s) use (&$page) {
            [$type, $preset, $settings] = $s + [1 => 0, 2 => []];
            $id = Schema::newId($type, $page['order']);
            $section = PageFactory::section($type, $preset);
            $section['settings'] = array_replace($section['settings'], $settings);
            $page['sections'][$id] = $section;
            $page['order'][] = $id;
        };
        foreach ($before as $s) {
            $add($s);
        }
        $page['sections']['main'] = $main;
        $page['order'][] = 'main';
        foreach ($after as $s) {
            $add($s);
        }
        return $page;
    }
}
