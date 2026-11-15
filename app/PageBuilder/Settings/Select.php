<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;
use Illuminate\Validation\Rule;

/** Choix dans une liste (valeur => libellé) */
class Select extends Setting
{
    public function __construct(string $id, string $label, mixed $default = null, ?string $info = null, public array $options = [])
    {
        parent::__construct($id, $label, $default, $info);
    }

    public function type(): string { return 'select'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', Rule::in(array_map('strval', array_keys($this->options)))];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        return $value === null || $value === '' ? $this->default : (string) $value;
    }

    protected function extra(): array
    {
        return ['options' => collect($this->options)->map(fn ($label, $value) => ['value' => (string) $value, 'label' => $label])->values()->all()];
    }
}
