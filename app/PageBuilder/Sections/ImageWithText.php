<?php

namespace App\PageBuilder\Sections;

use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\Image;
use App\PageBuilder\Settings\RichText;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;
use App\PageBuilder\Settings\Url;

/** Image (ou avant / après) à côté d'un titre, d'un texte et d'un bouton facultatif */
class ImageWithText extends SectionType
{
    public function type(): string { return 'image-with-text'; }

    public function name(): string { return 'Image et texte'; }

    public function category(): string { return 'medias'; }

    public function icon(): string { return 'image'; }

    public function description(): ?string
    {
        return 'Montre ton produit en situation ou un avant / après, avec quelques lignes.';
    }

    public function settings(): array
    {
        return [
            new Select('mode', 'Images', 'single', null, ['single' => 'Une image', 'before_after' => 'Avant / après']),
            new Image('image', 'Image (ou « avant »)'),
            new Image('image2', 'Image « après »', null, 'Pour « Avant / après ».'),
            new Select('image_side', 'Image à', 'left', null, ['left' => 'Gauche', 'right' => 'Droite']),
            new Text('heading', 'Titre', 'Pensé pour ton quotidien', null, 100),
            new RichText('content', 'Texte', '<p>Explique en quelques lignes ce que ton produit change pour tes clients.</p>'),
            new Text('button_label', 'Texte du bouton', null, 'Vide : pas de bouton.', 40),
            new Url('button_link', 'Lien du bouton', null, 'Vide : le bouton mène au formulaire de commande.'),
        ];
    }

    public function presets(): array
    {
        return [
            ['name' => 'Image et texte', 'settings' => [], 'blocks' => []],
            ['name' => 'Avant / après', 'settings' => ['mode' => 'before_after', 'heading' => 'Le résultat parle de lui-même', 'content' => '<p>Montre une vraie photo avant et après utilisation.</p>'], 'blocks' => []],
        ];
    }
}
