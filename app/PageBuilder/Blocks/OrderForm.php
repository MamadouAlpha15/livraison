<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Header;
use App\PageBuilder\Settings\Number;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;
use App\Support\OrderPhoneVerification;

/** Formulaire de commande payé à la livraison (nom, téléphone, commune, quartier), récapitulatif et bouton */
class OrderForm extends BlockType
{
    public function type(): string { return 'order_form'; }

    public function name(): string { return 'Formulaire de commande'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'truck'; }

    public function settings(): array
    {
        return [
            new Text('heading', 'Titre', 'Commande maintenant', null, 80),
            new Text('subtitle', 'Sous-titre', 'Tu paies seulement à la réception du colis.', null, 140),
            new Text('button', 'Texte du bouton', 'Commander · Payer à la livraison', null, 60),
            new Checkbox('show_quartier', 'Demander le quartier', true),
            new Checkbox('show_offer_button', 'Bouton « Faire une offre au vendeur »', false),
            new Checkbox('show_whatsapp', 'Bouton « Une question ? WhatsApp »', false, 'Vers le téléphone de ta boutique.'),
            new Header('Moins de commandes refusées'),
            new Select('verify_phone', 'Confirmer le numéro par code SMS', 'never', 'Le client reçoit un code et le saisit avant que la commande parte. Chaque code envoyé est un SMS payé.', OrderPhoneVerification::MODES),
            new Number('verify_above', 'À partir de (GNF)', 500000, 'Pour « Au-dessus d\'un montant » : total des articles.', 0, 1000000000),
        ];
    }
}
