<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/**
 * Réglage d'une section ou d'un bloc (texte, image, couleur…). Chaque type sait :
 *   - décrire son champ pour l'éditeur (toArray) ;
 *   - donner ses règles de validation Laravel (rules) ;
 *   - remettre la valeur en forme et la nettoyer avant l'enregistrement (normalize).
 */
abstract class Setting
{
    public function __construct(
        public string $id,
        public string $label,
        public mixed $default = null,
        public ?string $info = null,
    ) {}

    /** Nom du type, utilisé par l'éditeur pour choisir le champ */
    abstract public function type(): string;

    /** Règles Laravel de la valeur (la valeur est toujours facultative : vide = valeur par défaut) */
    public function rules(ValidationContext $ctx): array
    {
        return ['nullable'];
    }

    /** Valeur enregistrée : mise en forme et nettoyage, ou valeur par défaut si vide */
    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        return $value ?? $this->default;
    }

    /** Ce réglage stocke-t-il une valeur ? (un intertitre de l'éditeur, non) */
    public function storesValue(): bool
    {
        return true;
    }

    /** Description du champ pour l'éditeur */
    public function toArray(): array
    {
        return array_filter([
            'id'      => $this->id,
            'type'    => $this->type(),
            'label'   => $this->label,
            'default' => $this->default,
            'info'    => $this->info,
        ] + $this->extra(), fn ($v) => $v !== null);
    }

    /** Propriétés propres au type (min, max, options…) */
    protected function extra(): array
    {
        return [];
    }
}
