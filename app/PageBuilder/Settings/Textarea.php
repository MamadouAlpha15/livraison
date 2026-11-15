<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Texte sur plusieurs lignes, sans mise en forme */
class Textarea extends Text
{
    public function __construct(string $id, string $label, mixed $default = null, ?string $info = null, int $max = 1000, ?string $placeholder = null)
    {
        parent::__construct($id, $label, $default, $info, $max, $placeholder);
    }

    public function type(): string { return 'textarea'; }
}
