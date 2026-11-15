<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Nombre entier (quantité, nombre de colonnes…) */
class Number extends Setting
{
    public function __construct(string $id, string $label, mixed $default = null, ?string $info = null, public int $min = 0, public int $max = 1000000000)
    {
        parent::__construct($id, $label, $default, $info);
    }

    public function type(): string { return 'number'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'integer', 'min:' . $this->min, 'max:' . $this->max];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        return $value === null || $value === '' ? $this->default : (int) $value;
    }

    protected function extra(): array { return ['min' => $this->min, 'max' => $this->max]; }
}
