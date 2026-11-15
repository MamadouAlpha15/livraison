<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;
use App\PageBuilder\Settings\Textarea;

/** Champ demandé au client dans le formulaire (prénom à graver, pointure…), enregistré avec la commande */
class CustomField extends BlockType
{
    public const FIELD_TYPES = ['text' => 'Texte court', 'textarea' => 'Texte long', 'select' => 'Choix dans une liste'];

    public function type(): string { return 'custom_field'; }

    public function name(): string { return 'Champ personnalisé'; }

    public function limit(): ?int { return 5; }

    public function icon(): string { return 'list'; }

    public function settings(): array
    {
        return [
            new Text('label', 'Question posée', 'Une précision pour ta commande ?', null, 80),
            new Select('field_type', 'Type de réponse', 'text', null, self::FIELD_TYPES),
            new Textarea('options', 'Choix proposés', null, 'Pour « Choix dans une liste » : un choix par ligne (20 au plus).', 1000),
            new Text('placeholder', 'Texte d\'exemple', null, null, 80),
            new Checkbox('required', 'Réponse obligatoire', false),
        ];
    }

    /** Choix d'un champ « liste » : lignes non vides, sans doublon, 20 au plus */
    public static function options(array $settings): array
    {
        $lines = preg_split('/\R/', (string) ($settings['options'] ?? '')) ?: [];
        return array_slice(array_values(array_unique(array_filter(array_map(fn ($l) => mb_substr(trim($l), 0, 80), $lines), 'strlen'))), 0, 20);
    }
}
