<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Un produit en ligne de la boutique */
class ProductPicker extends Setting
{
    public function type(): string { return 'product'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'integer', function ($attribute, $value, $fail) use ($ctx) {
            if (!$ctx->ownsProduct((int) $value)) {
                $fail('Ce produit n\'est pas un produit en ligne de ta boutique.');
            }
        }];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        return $value === null || $value === '' ? $this->default : (int) $value;
    }
}
