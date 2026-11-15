<?php

namespace App\PageBuilder;

use App\PageBuilder\Settings\Setting;
use Illuminate\Support\Str;

/** Outils communs du moteur : valeurs par défaut, identifiants de sections et de blocs. */
class Schema
{
    /** Identifiant d'une section ou d'un bloc dans le JSON de la page */
    public const ID = '/^[a-z0-9_-]{1,40}$/';

    /** @param Setting[] $settings */
    public static function defaults(array $settings): array
    {
        $out = [];
        foreach ($settings as $s) {
            if ($s->storesValue()) {
                $out[$s->id] = $s->default;
            }
        }
        return $out;
    }

    /** Nouvel identifiant, différent de ceux déjà pris */
    public static function newId(string $prefix, array $taken = []): string
    {
        do {
            $id = Str::limit(Str::slug($prefix, '_'), 20, '') . '_' . Str::lower(Str::random(6));
        } while (in_array($id, $taken, true));
        return $id;
    }
}
