<?php

namespace App\PageBuilder;

use App\PageBuilder\Settings\Setting;

/**
 * Type de bloc (titre, prix, bouton d'achat…). Décrit son nom, ses réglages et combien de fois il peut
 * apparaître dans une même section. Rendu : resources/views/page-builder/blocks/{type}.blade.php
 */
abstract class BlockType
{
    abstract public function type(): string;

    abstract public function name(): string;

    /** @return Setting[] */
    public function settings(): array
    {
        return [];
    }

    /** Nombre maximum de ce bloc dans une section (null : sans limite autre que celle de la section) */
    public function limit(): ?int
    {
        return null;
    }

    /** Icône dans l'arbre de l'éditeur (IconLibrary) */
    public function icon(): string
    {
        return 'grid';
    }

    public function view(): string
    {
        return 'page-builder.blocks.' . $this->type();
    }

    /** Valeurs par défaut de tous les réglages */
    public function defaults(): array
    {
        return Schema::defaults($this->settings());
    }

    public function schema(): array
    {
        return [
            'type'     => $this->type(),
            'name'     => $this->name(),
            'icon'     => $this->icon(),
            'limit'    => $this->limit(),
            'settings' => array_map(fn (Setting $s) => $s->toArray(), $this->settings()),
        ];
    }
}
