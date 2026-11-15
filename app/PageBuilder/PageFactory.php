<?php

namespace App\PageBuilder;

/**
 * Fabrique du JSON des pages : nouvelle section (depuis un préréglage), nouveau bloc, page de départ.
 * Les valeurs par défaut des schémas sont toujours appliquées.
 */
class PageFactory
{
    /** Section prête à insérer dans une page, avec ses blocs de préréglage */
    public static function section(string $type, int $preset = 0): array
    {
        $section = Registry::section($type) ?? throw new \InvalidArgumentException("Section inconnue : {$type}");
        $p = $section->presets()[$preset] ?? $section->presets()[0];

        $blocks = [];
        $order = [];
        foreach ($p['blocks'] ?? [] as $b) {
            $id = Schema::newId($b['type'], $order);
            $blocks[$id] = self::block($b['type'], $b['settings'] ?? []);
            $order[] = $id;
        }

        return [
            'type'        => $type,
            'settings'    => array_replace($section->defaults(), $p['settings'] ?? []),
            'blocks'      => $blocks,
            'block_order' => $order,
            'disabled'    => false,
        ];
    }

    public static function block(string $type, array $settings = []): array
    {
        $block = Registry::block($type) ?? throw new \InvalidArgumentException("Bloc inconnu : {$type}");
        return ['type' => $type, 'settings' => array_replace($block->defaults(), $settings), 'disabled' => false];
    }

    /** Page de départ : la section principale Produit seule */
    public static function blank(): array
    {
        return ['sections' => ['main' => self::section('main-product')], 'order' => ['main']];
    }

    /** Ajoute une section à une page (avant la position donnée, à la fin par défaut) ; renvoie [page, id] */
    public static function addSection(array $page, string $type, int $preset = 0, ?int $position = null): array
    {
        $id = Schema::newId($type, array_keys($page['sections'] ?? []));
        $page['sections'][$id] = self::section($type, $preset);
        $order = $page['order'] ?? [];
        array_splice($order, $position ?? count($order), 0, [$id]);
        $page['order'] = $order;
        return [$page, $id];
    }
}
