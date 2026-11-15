<?php

namespace App\PageBuilder;

/**
 * Registre des types de sections et de blocs, découverts automatiquement dans app/PageBuilder/Sections
 * et app/PageBuilder/Blocks. Ajouter une section = ajouter une classe et sa vue Blade, rien d'autre.
 */
class Registry
{
    /** @var array<string, SectionType>|null */
    private static ?array $sections = null;

    /** @var array<string, BlockType>|null */
    private static ?array $blocks = null;

    /** @return array<string, SectionType> */
    public static function sections(): array
    {
        return self::$sections ??= self::discover('Sections', SectionType::class);
    }

    /** @return array<string, BlockType> */
    public static function blocks(): array
    {
        return self::$blocks ??= self::discover('Blocks', BlockType::class);
    }

    public static function section(string $type): ?SectionType
    {
        return self::sections()[$type] ?? null;
    }

    public static function block(string $type): ?BlockType
    {
        return self::blocks()[$type] ?? null;
    }

    /** Sections que le vendeur peut ajouter, rangées par catégorie (pour « + Ajouter une section ») */
    public static function library(): array
    {
        $out = [];
        foreach (SectionType::CATEGORIES as $key => $label) {
            $items = array_values(array_filter(self::sections(), fn (SectionType $s) => !$s->isStatic() && $s->category() === $key));
            if ($items) {
                $out[] = ['key' => $key, 'label' => $label, 'sections' => array_map(fn (SectionType $s) => $s->schema(), $items)];
            }
        }
        return $out;
    }

    /** Pour les tests : oublier les types déjà découverts */
    public static function flush(): void
    {
        self::$sections = self::$blocks = null;
    }

    private static function discover(string $folder, string $base): array
    {
        $found = [];
        foreach (glob(app_path('PageBuilder/' . $folder . '/*.php')) ?: [] as $file) {
            $class = 'App\\PageBuilder\\' . $folder . '\\' . basename($file, '.php');
            if (class_exists($class) && is_subclass_of($class, $base) && !(new \ReflectionClass($class))->isAbstract()) {
                $instance = new $class();
                $found[$instance->type()] = $instance;
            }
        }
        ksort($found);
        return $found;
    }
}
