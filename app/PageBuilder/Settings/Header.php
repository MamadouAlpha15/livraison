<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Intertitre dans le panneau de réglages de l'éditeur (ne stocke rien) */
class Header extends Setting
{
    public function __construct(string $label, ?string $info = null)
    {
        parent::__construct('', $label, null, $info);
    }

    public function type(): string { return 'header'; }

    public function storesValue(): bool { return false; }
}
