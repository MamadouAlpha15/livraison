<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Number;
use App\PageBuilder\Settings\Text;
use Illuminate\Support\Carbon;

/** Livraison estimée : dates calculées à partir des délais réels de la boutique */
class Delivery extends BlockType
{
    public function type(): string { return 'delivery'; }

    public function name(): string { return 'Livraison estimée'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'truck'; }

    public function settings(): array
    {
        return [
            new Text('label', 'Intitulé', 'Livraison estimée', null, 60),
            new Number('min_days', 'Délai le plus court (jours)', 1, 'Indique tes délais habituels à Conakry.', 0, 30),
            new Number('max_days', 'Délai le plus long (jours)', 3, null, 0, 60),
            new Checkbox('skip_sunday', 'Pas de livraison le dimanche', true),
        ];
    }

    /** [premier jour, dernier jour] de livraison, à partir d'aujourd'hui */
    public static function window(array $settings, ?Carbon $from = null): array
    {
        $min = max(0, (int) $settings['min_days']);
        $max = max($min, (int) $settings['max_days']);
        $add = function (int $days) use ($settings, $from) {
            $d = ($from ?? now())->copy()->startOfDay();
            for ($i = 0; $i < $days; $i++) {
                $d->addDay();
                if ($settings['skip_sunday'] && $d->isSunday()) {
                    $d->addDay();
                }
            }
            if ($settings['skip_sunday'] && $d->isSunday()) {
                $d->addDay();
            }
            return $d;
        };
        return [$add($min), $add($max)];
    }
}
