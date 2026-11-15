<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Texte sur une ligne (titre, libellé de bouton…) */
class Text extends Setting
{
    public function __construct(string $id, string $label, mixed $default = null, ?string $info = null, public int $max = 255, public ?string $placeholder = null)
    {
        parent::__construct($id, $label, $default, $info);
    }

    public function type(): string { return 'text'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'string', 'max:' . $this->max];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        // Absent : valeur par défaut. Vidé par le vendeur : reste vide (pas de titre, pas de bouton…)
        return $value === null ? $this->default : trim(strip_tags((string) $value));
    }

    protected function extra(): array { return ['max' => $this->max, 'placeholder' => $this->placeholder]; }
}
