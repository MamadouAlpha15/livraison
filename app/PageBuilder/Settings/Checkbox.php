<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Case à cocher (afficher / masquer…) */
class Checkbox extends Setting
{
    public function __construct(string $id, string $label, mixed $default = false, ?string $info = null)
    {
        parent::__construct($id, $label, (bool) $default, $info);
    }

    public function type(): string { return 'checkbox'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'boolean'];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        return $value === null ? $this->default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
