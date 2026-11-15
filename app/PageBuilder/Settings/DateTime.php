<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Date et heure (fin d'une offre…), au format du champ « datetime-local » : 2026-11-30T23:59 */
class DateTime extends Setting
{
    public function type(): string { return 'datetime'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'string', 'date_format:Y-m-d\TH:i'];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        return $value === null || $value === '' ? $this->default : (string) $value;
    }
}
