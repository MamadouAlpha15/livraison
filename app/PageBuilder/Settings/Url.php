<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Lien : adresse web (https) ou page de Shopio (commence par /) */
class Url extends Setting
{
    public function type(): string { return 'url'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'string', 'max:500', 'regex:#^(https?://[^\s<>"]+|/[^\s<>"]*)$#'];
    }
}
