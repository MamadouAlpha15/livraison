<?php

namespace App\PageBuilder\Sections;

use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;
use App\PageBuilder\Settings\Video;

/** Vidéo : MP4 envoyé (20 Mo et 60 secondes au plus) ou lien YouTube / TikTok */
class VideoSection extends SectionType
{
    public function type(): string { return 'video'; }

    public function name(): string { return 'Vidéo'; }

    public function category(): string { return 'medias'; }

    public function icon(): string { return 'camera'; }

    public function description(): ?string
    {
        return 'Ton produit en action : une courte vidéo ou un lien YouTube / TikTok.';
    }

    public function settings(): array
    {
        return [
            new Text('heading', 'Titre', 'Regarde-le en action', null, 100),
            new Video('video', 'Vidéo', null, 'Envoie un MP4 (20 Mo et 60 secondes au plus) ou colle un lien YouTube ou TikTok.'),
            new Text('caption', 'Légende', null, null, 160),
            new Select('format', 'Format', 'portrait', 'Pour un MP4 : vertical pour une vidéo de téléphone.', ['portrait' => 'Vertical (9:16)', 'landscape' => 'Horizontal (16:9)', 'square' => 'Carré']),
            new Checkbox('autoplay', 'MP4 : lecture automatique, sans le son, en boucle', true),
        ];
    }
}
