<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Plusieurs produits en ligne de la boutique (produits similaires, ventes additionnelles…) */
class ProductList extends Setting
{
    public function __construct(string $id, string $label, mixed $default = [], ?string $info = null, public int $max = 8)
    {
        parent::__construct($id, $label, $default, $info);
    }

    public function type(): string { return 'product_list'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'array', 'max:' . $this->max, function ($attribute, $value, $fail) use ($ctx) {
            foreach ((array) $value as $id) {
                if (!is_numeric($id) || !$ctx->ownsProduct((int) $id)) {
                    $fail('Un des produits choisis n\'est pas un produit en ligne de ta boutique.');
                    return;
                }
            }
        }];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        return array_values(array_unique(array_map('intval', (array) ($value ?? $this->default))));
    }

    protected function extra(): array { return ['max' => $this->max]; }
}
