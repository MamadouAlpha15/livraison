<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Curseur (taille, espacement…) entre un minimum et un maximum, par pas */
class Range extends Setting
{
    public function __construct(string $id, string $label, mixed $default = null, ?string $info = null, public int $min = 0, public int $max = 100, public int $step = 1, public ?string $unit = null)
    {
        parent::__construct($id, $label, $default, $info);
    }

    public function type(): string { return 'range'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'integer', 'min:' . $this->min, 'max:' . $this->max];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        if ($value === null || $value === '') {
            return $this->default;
        }
        // Ramené sur le pas le plus proche
        $v = (int) $value;
        return (int) (round(($v - $this->min) / $this->step) * $this->step + $this->min);
    }

    protected function extra(): array { return ['min' => $this->min, 'max' => $this->max, 'step' => $this->step, 'unit' => $this->unit]; }
}
