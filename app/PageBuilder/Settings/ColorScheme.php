<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;
use App\PageBuilder\ColorSchemes;
use Illuminate\Validation\Rule;

/** Jeu de couleurs de la section, tiré des couleurs de la boutique (clair, doux, accent, nuit) */
class ColorScheme extends Setting
{
    public function __construct(string $id = 'color_scheme', string $label = 'Jeu de couleurs', mixed $default = 'clair', ?string $info = null)
    {
        parent::__construct($id, $label, $default, $info);
    }

    public function type(): string { return 'color_scheme'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', Rule::in(array_keys(ColorSchemes::LABELS))];
    }

    protected function extra(): array
    {
        return ['options' => collect(ColorSchemes::LABELS)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all()];
    }
}
