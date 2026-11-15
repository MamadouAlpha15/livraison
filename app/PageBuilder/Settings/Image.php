<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Image envoyée par la boutique (WebP compressé dans son dossier : page-builder/{boutique}/…) */
class Image extends Setting
{
    public function type(): string { return 'image'; }

    public function rules(ValidationContext $ctx): array
    {
        $prefix = preg_quote($ctx->mediaPrefix(), '#');
        return ['nullable', 'string', 'max:255', 'regex:#^' . $prefix . '(thumb|medium|large)/[A-Za-z0-9]{20,40}\.webp$#'];
    }
}
