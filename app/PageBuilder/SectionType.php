<?php

namespace App\PageBuilder;

use App\PageBuilder\Settings\ColorScheme;
use App\PageBuilder\Settings\Header;
use App\PageBuilder\Settings\Range;
use App\PageBuilder\Settings\Setting;

/**
 * Type de section (section principale Produit, témoignages, FAQ…). Décrit son nom, sa catégorie, ses
 * réglages, les blocs qu'elle accepte et ses préréglages. Chaque section reçoit en plus les réglages
 * communs : jeu de couleurs et marges haut / bas séparées pour mobile et ordinateur.
 * Rendu : resources/views/page-builder/sections/{type}.blade.php
 */
abstract class SectionType
{
    public const CATEGORIES = [
        'produit'    => 'Produit',
        'medias'     => 'Médias',
        'preuve'     => 'Preuve sociale',
        'conversion' => 'Conversion',
        'contenu'    => 'Contenu',
        'mise_en_page' => 'Mise en page',
    ];

    abstract public function type(): string;

    abstract public function name(): string;

    abstract public function category(): string;

    /** @return Setting[] réglages propres à la section */
    public function settings(): array
    {
        return [];
    }

    /** Types de blocs acceptés (vide : aucun bloc) */
    public function blocks(): array
    {
        return [];
    }

    /** Blocs qui doivent être présents et visibles (ex. le formulaire de commande de la section Produit) */
    public function requiredBlocks(): array
    {
        return [];
    }

    public function maxBlocks(): int
    {
        return 30;
    }

    /** Section fixe : toujours présente une seule fois, ni supprimable ni duplicable (section principale Produit) */
    public function isStatic(): bool
    {
        return false;
    }

    /** Nombre maximum de cette section sur une page (null : sans limite) */
    public function maxPerPage(): ?int
    {
        return $this->isStatic() ? 1 : null;
    }

    /**
     * Préréglages proposés dans « + Ajouter une section » : [['name' => …, 'settings' => […], 'blocks' => [['type' => …, 'settings' => […]], …]]
     * Sans préréglage : la section vide avec ses valeurs par défaut.
     */
    public function presets(): array
    {
        return [['name' => $this->name(), 'settings' => [], 'blocks' => []]];
    }

    public function description(): ?string
    {
        return null;
    }

    public function icon(): string
    {
        return 'grid';
    }

    /** Miniature dans la bibliothèque des sections (image dans public/images/page-builder/) */
    public function thumbnail(): string
    {
        return 'images/page-builder/' . $this->type() . '.svg';
    }

    public function view(): string
    {
        return 'page-builder.sections.' . $this->type();
    }

    /** Réglages propres + réglages communs (couleurs, marges) */
    public function allSettings(): array
    {
        return array_merge($this->settings(), [
            new Header('Couleurs et marges'),
            new ColorScheme(),
            new Range('padding_top_mobile', 'Marge en haut · mobile', 24, null, 0, 120, 4, 'px'),
            new Range('padding_bottom_mobile', 'Marge en bas · mobile', 24, null, 0, 120, 4, 'px'),
            new Range('padding_top_desktop', 'Marge en haut · ordinateur', 40, null, 0, 160, 4, 'px'),
            new Range('padding_bottom_desktop', 'Marge en bas · ordinateur', 40, null, 0, 160, 4, 'px'),
        ]);
    }

    public function defaults(): array
    {
        return Schema::defaults($this->allSettings());
    }

    public function schema(): array
    {
        return [
            'type'        => $this->type(),
            'name'        => $this->name(),
            'category'    => $this->category(),
            'description' => $this->description(),
            'icon'        => $this->icon(),
            'thumbnail'   => asset($this->thumbnail()),
            'static'      => $this->isStatic(),
            'max_per_page' => $this->maxPerPage(),
            'blocks'      => $this->blocks(),
            'max_blocks'  => $this->maxBlocks(),
            'required_blocks' => $this->requiredBlocks(),
            'presets'     => $this->presets(), // complets : l'éditeur crée la section sans appel au serveur
            'settings'    => array_map(fn (Setting $s) => $s->toArray(), $this->allSettings()),
        ];
    }
}
