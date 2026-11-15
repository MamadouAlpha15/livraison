<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Header;
use App\PageBuilder\Settings\Number;
use App\PageBuilder\Settings\ProductPicker;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;

/**
 * Offres groupées (jusqu'à 3 paliers : quantité, remise, cadeau). Les prix sont toujours recalculés
 * sur le serveur (App\PageBuilder\Commerce\Checkout) à partir du prix réel du produit.
 */
class Offers extends BlockType
{
    public const TIERS = 3;

    public const DISCOUNTS = ['none' => 'Sans remise', 'percent' => 'Remise en %', 'amount' => 'Remise en montant sur le lot'];

    public function type(): string { return 'offers'; }

    public function name(): string { return 'Offres groupées'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'gift'; }

    public function settings(): array
    {
        $defaults = [
            1 => ['qty' => 1, 'discount' => 'none', 'value' => 0, 'label' => '1 article', 'badge' => null],
            2 => ['qty' => 2, 'discount' => 'percent', 'value' => 10, 'label' => '2 articles', 'badge' => null],
            3 => ['qty' => 3, 'discount' => 'percent', 'value' => 15, 'label' => '3 articles', 'badge' => 'Meilleure économie'],
        ];

        $settings = [new Text('heading', 'Titre', 'Choisis ton offre', null, 80)];
        foreach ($defaults as $i => $d) {
            $settings[] = new Header("Offre {$i}");
            if ($i > 1) {
                $settings[] = new Checkbox("tier{$i}_enabled", "Proposer l'offre {$i}", true);
            }
            $settings[] = new Number("tier{$i}_qty", 'Quantité', $d['qty'], null, 1, 20);
            $settings[] = new Text("tier{$i}_label", 'Nom de l\'offre', $d['label'], null, 60);
            $settings[] = new Select("tier{$i}_discount", 'Remise', $d['discount'], null, self::DISCOUNTS);
            $settings[] = new Number("tier{$i}_value", 'Valeur de la remise', $d['value'], 'En % (90 au plus) ou en montant retiré du prix du lot.', 0, 100000000);
            $settings[] = new Text("tier{$i}_badge", 'Étiquette', $d['badge'], 'Ex. « Meilleure économie ». Vide : pas d\'étiquette.', 30);
            $settings[] = new ProductPicker("tier{$i}_gift", 'Cadeau offert', null, 'Un produit de ta boutique, offert avec cette offre tant qu\'il est en stock.');
        }
        $settings[] = new Header('Affichage');
        $settings[] = new Select('selected', 'Offre choisie à l\'ouverture', '1', null, ['1' => 'Offre 1', '2' => 'Offre 2', '3' => 'Offre 3']);

        return $settings;
    }
}
