<?php

namespace App\PageBuilder\Sections;

use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Select;

/**
 * Section principale Produit : galerie à gauche, blocs d'achat à droite. Toujours présente, une seule fois,
 * avec son formulaire de commande. Les prix et les offres sont recalculés sur le serveur (Commerce\Checkout).
 */
class MainProduct extends SectionType
{
    public function type(): string { return 'main-product'; }

    public function name(): string { return 'Produit'; }

    public function category(): string { return 'produit'; }

    public function isStatic(): bool { return true; }

    public function icon(): string { return 'bag'; }

    public function description(): ?string
    {
        return 'Les photos du produit et tout ce qu\'il faut pour commander.';
    }

    public function settings(): array
    {
        return [
            new Select('gallery_layout', 'Disposition des photos', 'thumbnails', null, [
                'thumbnails' => 'Grande photo et miniatures',
                'stacked'    => 'Photos les unes sous les autres',
                'slider'     => 'Carrousel',
            ]),
            new Select('gallery_side', 'Photos à', 'left', null, ['left' => 'Gauche', 'right' => 'Droite']),
            new Checkbox('sticky_info', 'Garder les infos visibles en faisant défiler', true, 'Sur ordinateur seulement.'),
        ];
    }

    public function blocks(): array
    {
        return [
            'title', 'rating', 'price', 'text', 'countdown', 'stock', 'sold_count', 'variants', 'offers', 'quantity',
            'upsells', 'custom_field', 'order_form', 'badges', 'delivery', 'collapsible',
        ];
    }

    public function requiredBlocks(): array
    {
        return ['order_form'];
    }

    public function presets(): array
    {
        return [[
            'name'     => 'Produit',
            'settings' => [],
            'blocks'   => [
                ['type' => 'title'],
                ['type' => 'rating'],
                ['type' => 'price'],
                ['type' => 'countdown'],
                ['type' => 'stock'],
                ['type' => 'variants'],
                ['type' => 'quantity'],
                ['type' => 'order_form'],
                ['type' => 'badges'],
                ['type' => 'delivery'],
                ['type' => 'collapsible'],
            ],
        ]];
    }
}
