<?php

namespace App\PageBuilder\Sections;

use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Range;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;

/** Avis réels des clients livrés (aucun avis inventé ni modifiable). Cachée tant qu'il n'y a pas d'avis. */
class Reviews extends SectionType
{
    public function type(): string { return 'reviews'; }

    public function name(): string { return 'Avis clients'; }

    public function category(): string { return 'preuve'; }

    public function icon(): string { return 'star'; }

    public function maxPerPage(): ?int { return 1; }

    public function description(): ?string
    {
        return 'Les vrais avis laissés par tes clients après livraison.';
    }

    public function settings(): array
    {
        return [
            new Text('heading', 'Titre', 'Ce que disent nos clients', null, 80),
            new Range('limit', 'Nombre d\'avis affichés', 6, null, 2, 12, 1),
            new Select('layout', 'Présentation', 'grid', null, ['grid' => 'Cartes', 'list' => 'Liste']),
            new Checkbox('show_summary', 'Afficher la note moyenne', true),
            new Checkbox('only_commented', 'Seulement les avis avec un commentaire', true),
        ];
    }
}
