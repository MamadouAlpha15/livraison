<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;
use App\Support\IconLibrary;
use Illuminate\Validation\Rule;

/** Icône de la bibliothèque Shopio */
class Icon extends Setting
{
    public function type(): string { return 'icon'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', Rule::in(IconLibrary::names())];
    }
}
