<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Number;
use App\PageBuilder\Settings\Text;

/** Stock réel bas : « Plus que 4 en stock ». Caché au-dessus du seuil ou si le stock n'est pas suivi. */
class Stock extends BlockType
{
    public function type(): string { return 'stock'; }

    public function name(): string { return 'Stock restant'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'package'; }

    public function settings(): array
    {
        return [
            new Number('threshold', 'Afficher quand il en reste', 10, 'Le nombre affiché est toujours le stock réel.', 1, 100),
            new Text('text', 'Texte', 'Plus que {n} en stock', '{n} est remplacé par le stock réel.', 80),
        ];
    }
}
