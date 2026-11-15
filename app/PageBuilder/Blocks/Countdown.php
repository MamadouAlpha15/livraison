<?php

namespace App\PageBuilder\Blocks;

use App\Models\Product;
use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\DateTime;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;
use Illuminate\Support\Carbon;

/**
 * Compte à rebours vers une vraie échéance : la fin de la vente flash du produit, ou une date fixe.
 * Jamais de minuteur qui repart à zéro : une fois l'échéance passée, le bloc disparaît.
 */
class Countdown extends BlockType
{
    public function type(): string { return 'countdown'; }

    public function name(): string { return 'Compte à rebours'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'clock'; }

    public function settings(): array
    {
        return [
            new Select('source', 'Échéance', 'flash', null, ['flash' => 'Fin de la vente flash du produit', 'date' => 'Date fixe']),
            new DateTime('ends_at', 'Date et heure de fin', null, 'Pour « Date fixe ».'),
            new Text('label', 'Texte', 'L\'offre se termine dans', null, 80),
        ];
    }

    /** Échéance réelle, ou null si aucune échéance à venir */
    public static function deadline(array $settings, Product $product): ?Carbon
    {
        if ($settings['source'] === 'flash') {
            return $product->is_flash_active ? $product->flash_ends_at : null;
        }
        if (!$settings['ends_at']) {
            return null;
        }
        $end = Carbon::createFromFormat('Y-m-d\TH:i', $settings['ends_at']);
        return $end && $end->isFuture() ? $end : null;
    }
}
