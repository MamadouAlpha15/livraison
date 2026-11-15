<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;
use App\PageBuilder\HtmlSanitizer;

/** Texte mis en forme (gras, italique, liens, listes) : nettoyé par liste blanche, jamais de script */
class RichText extends Setting
{
    public function __construct(string $id, string $label, mixed $default = null, ?string $info = null, public int $max = 5000)
    {
        parent::__construct($id, $label, $default, $info);
    }

    public function type(): string { return 'richtext'; }

    public function rules(ValidationContext $ctx): array
    {
        return ['nullable', 'string', 'max:' . $this->max];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        if ($value === null) {
            return $this->default;
        }
        // Vidé par le vendeur (ou réduit à des balises vides par le nettoyage) : reste vide
        $clean = HtmlSanitizer::clean((string) $value);
        return trim(strip_tags($clean)) === '' ? '' : $clean;
    }

    protected function extra(): array { return ['max' => $this->max]; }
}
