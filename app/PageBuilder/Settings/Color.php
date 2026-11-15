<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Couleur libre (#RRGGBB) */
class Color extends Setting
{
    public function type(): string { return 'color'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        return $value === null || $value === '' ? $this->default : strtoupper($value);
    }
}
