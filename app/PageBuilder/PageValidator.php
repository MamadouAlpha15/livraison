<?php

namespace App\PageBuilder;

use App\PageBuilder\Settings\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Valide et remet en forme le JSON complet d'une page avant tout enregistrement :
 *   - structure (sections, ordre, blocs, ordre des blocs, identifiants) ;
 *   - types connus, blocs autorisés par la section, limites (blocs par section, sections par page, section fixe) ;
 *   - chaque réglage selon son schéma (règles Laravel), puis nettoyage (texte riche) et valeurs par défaut.
 * Les réglages inconnus sont retirés. Les erreurs indiquent leur emplacement : « sections.<id>.settings.<réglage> ».
 */
class PageValidator
{
    public const MAX_SECTIONS = 40;

    /** Longueurs maximales du référencement (ce que Google affiche) */
    public const SEO = ['title' => 70, 'description' => 160];

    private array $errors = [];

    public function __construct(private ValidationContext $ctx) {}

    /** @throws ValidationException */
    public function validate(array $page): array
    {
        $this->errors = [];
        $sections = $page['sections'] ?? null;
        $order = $page['order'] ?? null;

        if (!is_array($sections) || !is_array($order)) {
            throw ValidationException::withMessages(['page' => 'Page illisible : sections ou ordre manquants.']);
        }
        if (count($sections) > self::MAX_SECTIONS) {
            $this->fail('page', 'Une page ne peut pas avoir plus de ' . self::MAX_SECTIONS . ' sections.');
        }
        if (array_values(array_unique($order)) !== array_values($order) || count(array_diff($order, array_keys($sections))) || count(array_diff(array_keys($sections), $order))) {
            $this->fail('order', 'L\'ordre des sections ne correspond pas aux sections de la page.');
        }

        $out = ['sections' => [], 'order' => array_values(array_filter($order, fn ($id) => isset($sections[$id])))];
        $perType = [];
        foreach ($sections as $id => $section) {
            $path = "sections.{$id}";
            if (!is_string($id) || !preg_match(Schema::ID, $id)) {
                $this->fail($path, 'Identifiant de section invalide.');
                continue;
            }
            if (!is_array($section)) {
                $this->fail($path, 'Section illisible.');
                continue;
            }
            $type = Registry::section((string) ($section['type'] ?? ''));
            if (!$type) {
                $this->fail("{$path}.type", 'Type de section inconnu : ' . ($section['type'] ?? '—') . '.');
                continue;
            }
            $perType[$type->type()] = ($perType[$type->type()] ?? 0) + 1;
            $out['sections'][$id] = $this->section($type, $section, $path);
        }

        foreach (Registry::sections() as $t) {
            $count = $perType[$t->type()] ?? 0;
            if ($t->isStatic() && $count === 0) {
                $this->fail('page', 'La section « ' . $t->name() . ' » est obligatoire.');
            }
            if ($t->maxPerPage() !== null && $count > $t->maxPerPage()) {
                $this->fail('page', 'La section « ' . $t->name() . ' » ne peut apparaître ' . self::times($t->maxPerPage()) . '.');
            }
        }
        // Une section fixe ne peut pas être masquée
        foreach ($out['sections'] as $id => $s) {
            if (Registry::section($s['type'])->isStatic() && $s['disabled']) {
                $this->fail("sections.{$id}.disabled", 'La section « ' . Registry::section($s['type'])->name() . ' » ne peut pas être masquée.');
            }
        }

        // Référencement (facultatif) : titre et description pour Google et le partage des liens
        if (isset($page['seo'])) {
            $seo = is_array($page['seo']) ? array_intersect_key($page['seo'], self::SEO) : [];
            $v = Validator::make($seo, ['title' => ['nullable', 'string', 'max:' . self::SEO['title']], 'description' => ['nullable', 'string', 'max:' . self::SEO['description']]]);
            foreach ($v->errors()->messages() as $key => $messages) {
                $this->fail("seo.{$key}", ($key === 'title' ? 'Titre pour Google' : 'Description pour Google') . ' : ' . self::SEO[$key] . ' caractères au plus.');
            }
            $seo = array_map(fn ($s) => is_string($s) && trim(strip_tags($s)) !== '' ? trim(strip_tags($s)) : null, $seo);
            if (array_filter($seo)) {
                $out['seo'] = array_filter($seo);
            }
        }

        if ($this->errors) {
            throw ValidationException::withMessages($this->errors);
        }
        return $out;
    }

    private function section(SectionType $type, array $section, string $path): array
    {
        $blocks = is_array($section['blocks'] ?? null) ? $section['blocks'] : [];
        $order = is_array($section['block_order'] ?? null) ? $section['block_order'] : array_keys($blocks);

        if (count($blocks) > $type->maxBlocks()) {
            $this->fail("{$path}.blocks", 'Cette section accepte ' . $type->maxBlocks() . ' blocs au plus.');
        }
        if (array_values(array_unique($order)) !== array_values($order) || count(array_diff($order, array_keys($blocks))) || count(array_diff(array_keys($blocks), $order))) {
            $this->fail("{$path}.block_order", 'L\'ordre des blocs ne correspond pas aux blocs de la section.');
        }

        $outBlocks = [];
        $perBlock = [];
        foreach ($blocks as $bid => $block) {
            $bpath = "{$path}.blocks.{$bid}";
            if (!is_string($bid) || !preg_match(Schema::ID, $bid)) {
                $this->fail($bpath, 'Identifiant de bloc invalide.');
                continue;
            }
            if (!is_array($block)) {
                $this->fail($bpath, 'Bloc illisible.');
                continue;
            }
            $btype = Registry::block((string) ($block['type'] ?? ''));
            if (!$btype || !in_array($btype->type(), $type->blocks(), true)) {
                $this->fail("{$bpath}.type", 'Le bloc « ' . ($btype?->name() ?? ($block['type'] ?? '—')) . ' » n\'est pas accepté dans « ' . $type->name() . ' ».');
                continue;
            }
            $perBlock[$btype->type()] = ($perBlock[$btype->type()] ?? 0) + 1;
            if ($btype->limit() !== null && $perBlock[$btype->type()] > $btype->limit()) {
                $this->fail("{$bpath}.type", 'Le bloc « ' . $btype->name() . ' » ne peut apparaître ' . self::times($btype->limit()) . ' dans cette section.');
            }
            $outBlocks[$bid] = [
                'type'     => $btype->type(),
                'settings' => $this->settings($btype->settings(), is_array($block['settings'] ?? null) ? $block['settings'] : [], "{$bpath}.settings"),
                'disabled' => (bool) ($block['disabled'] ?? false),
            ];
        }

        foreach ($type->requiredBlocks() as $required) {
            $present = collect($outBlocks)->contains(fn ($b) => $b['type'] === $required && !$b['disabled']);
            if (!$present && Registry::block($required)) {
                $this->fail("{$path}.blocks", 'Le bloc « ' . Registry::block($required)->name() . ' » est obligatoire dans « ' . $type->name() . ' ».');
            }
        }

        return [
            'type'        => $type->type(),
            'settings'    => $this->settings($type->allSettings(), is_array($section['settings'] ?? null) ? $section['settings'] : [], "{$path}.settings"),
            'blocks'      => $outBlocks,
            'block_order' => array_values(array_filter($order, fn ($bid) => isset($outBlocks[$bid]))),
            'disabled'    => (bool) ($section['disabled'] ?? false),
        ];
    }

    /** @param Setting[] $schema */
    private function settings(array $schema, array $values, string $path): array
    {
        $rules = [];
        $known = [];
        foreach ($schema as $s) {
            if ($s->storesValue()) {
                $known[$s->id] = $s;
                $rules[$s->id] = $s->rules($this->ctx);
            }
        }
        $data = array_intersect_key($values, $known); // réglages inconnus retirés
        $failed = Validator::make($data, $rules)->errors()->messages();
        foreach ($failed as $key => $messages) {
            $this->fail("{$path}.{$key}", ($known[$key]->label ?? $key) . ' : ' . $messages[0]);
        }

        $out = [];
        foreach ($known as $id => $s) {
            // Une valeur refusée n'est pas mise en forme : la page entière sera rejetée de toute façon
            $out[$id] = isset($failed[$id]) ? $s->default : $s->normalize($data[$id] ?? null, $this->ctx);
        }
        return $out;
    }

    private static function times(int $n): string
    {
        return $n === 1 ? 'qu\'une seule fois' : "que {$n} fois";
    }

    private function fail(string $path, string $message): void
    {
        $this->errors[$path] ??= $message;
    }
}
