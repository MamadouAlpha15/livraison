<?php

namespace App\Support;

use App\Models\Shop;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Modification de la vitrine directement sur la vitrine en ligne (/s/{slug}?edition=1).
 * Reprend ce que faisait la page « Boutique » (ex-GrowController) : en-tête (nom, slogan, couleur, logo, photo de
 * partage), preuves de confiance et mode WhatsApp, thème, lien d'aperçu 48 h ; et y ajoute un brouillon :
 * shops.storefront_draft = ['config' => vitrine, 'accent' => couleur, 'extra' => ['name' => …, 'image' => logo]].
 * Les visiteurs ne voient les changements qu'à la publication (Growth::publish). Même structure de données
 * qu'avant (shops.storefront['home'] par thème, normalisé par HomeSections) : rien à migrer.
 */
class StorefrontEditor
{
    /** Logos importés pendant la session : seul un logo importé par ce vendeur peut être enregistré. */
    public const LOGO_SESSION = 'storefront_logo_uploads';

    /** Thèmes dont les sections se modifient sur la vitrine. */
    public const THEMES = ['express', 'signature'];

    /** Seul le propriétaire de la boutique modifie sa vitrine (jamais un employé, un client ni un visiteur). */
    public static function isOwner(Shop $shop, ?User $user): bool
    {
        return $user !== null && (int) $shop->user_id === (int) $user->id;
    }

    // ─────────────────────────────────────────────────────────── Version de travail (brouillon)

    /** Version en cours de travail : le brouillon s'il existe, sinon la version publiée. */
    public static function working(Shop $shop): array
    {
        [$config, $accent] = Growth::working($shop);
        $extra = is_array($shop->storefront_draft['extra'] ?? null) ? $shop->storefront_draft['extra'] : [];
        return [
            'config' => $config,
            'accent' => $accent,
            'name'   => is_string($extra['name'] ?? null) && $extra['name'] !== '' ? $extra['name'] : (string) $shop->name,
            'image'  => array_key_exists('image', $extra) ? $extra['image'] : $shop->image,
        ];
    }

    public static function hasDraft(Shop $shop): bool
    {
        return is_array($shop->storefront_draft) && isset($shop->storefront_draft['config']);
    }

    /** Le nom et le logo d'une version de travail, posés sur la boutique EN MÉMOIRE (aperçu, jamais enregistré). */
    public static function dress(Shop $shop, array $state): Shop
    {
        $shop->name = $state['name'];
        $shop->image = $state['image'];
        $shop->accent_color = $state['accent'];
        return $shop;
    }

    /**
     * Version de travail construite à partir de l'éditeur (sections des thèmes, réglages de la page, en-tête),
     * vérifiée et normalisée. Ce qui n'est pas envoyé reste tel quel.
     * @throws ValidationException messages prêts à montrer au vendeur
     */
    public static function build(Shop $shop, array $input, Request $request): array
    {
        $state = self::working($shop);
        $config = $state['config'];

        // Sections de l'accueil, une liste par thème (même normalisation que l'ancienne page Sections)
        foreach (self::THEMES as $theme) {
            if (isset($input['home'][$theme]) && is_array($input['home'][$theme])) {
                $config['home'][$theme] = HomeSections::normalize($theme, array_slice($input['home'][$theme], 0, count(HomeSections::BUILTIN['signature']) + HomeSections::CUSTOM_MAX));
            }
        }

        // Réglages de la page : preuves de confiance et mode WhatsApp
        if (isset($input['settings']) && is_array($input['settings'])) {
            $on = fn ($k) => filter_var($input['settings'][$k] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($on('wa_mode') && !$shop->phone) {
                throw ValidationException::withMessages(['wa_mode' => 'Ajoutez d’abord le numéro WhatsApp de la boutique dans « Ma boutique ».']);
            }
            foreach (['wa_mode', 'proof_delivered', 'proof_response'] as $k) $config['settings'][$k] = $on($k);
        }

        // Couleurs de la page, par thème (vide = couleurs du thème)
        if (isset($input['colors']) && is_array($input['colors'])) {
            foreach (array_keys(PageModels::MODELS) as $theme) {
                if (!isset($input['colors'][$theme]) || !is_array($input['colors'][$theme])) continue;
                foreach (array_keys(Storefront::COLOR_KEYS) as $k) {
                    $v = $input['colors'][$theme][$k] ?? null;
                    if ($v !== null && $v !== '' && !Storefront::isHex($v)) {
                        throw ValidationException::withMessages(['colors' => 'Couleur invalide : choisissez-la dans la palette.']);
                    }
                    $config['colors'][$theme][$k] = $v ? strtoupper($v) : null;
                }
            }
        }

        // En-tête : nom, slogan, couleur, logo, photo de partage
        if (isset($input['identity']) && is_array($input['identity'])) {
            $state = self::identity($shop, $state, $config, $input['identity'], $request);
            $config = $state['config'];
        }

        $state['config'] = Storefront::normalize($config);
        return $state;
    }

    /** En-tête (reprend l'onglet « Couverture et logo » : mêmes règles, appliquées au brouillon). */
    private static function identity(Shop $shop, array $state, array $config, array $in, Request $request): array
    {
        // 30 caractères pour un nouveau nom ; un nom existant plus long reste accepté tel quel (jamais tronqué)
        $nameMax = max(30, mb_strlen((string) $shop->name), mb_strlen($state['name']));
        $data = Validator::make($in, [
            'name'      => ['required', 'string', 'min:2', 'max:' . $nameMax, fn ($a, $v, $fail) => mb_strlen(trim((string) $v)) > 30 && !in_array(trim((string) $v), [$shop->name, $state['name']], true) ? $fail('Le nom ne peut pas dépasser 30 caractères.') : null],
            'slogan'    => ['nullable', 'string', 'max:60'],
            'color'     => ['required', Rule::in(array_keys(Storefront::IDENTITY_COLORS + [Growth::accent($shop) => '', strtoupper($state['accent']) => '']))],
            'share_src' => ['required', Rule::in(array_keys(Storefront::COVER_SOURCES))],
            'share_img' => ['nullable', 'string', 'regex:' . Storefront::COVER_PATH],
            'logo'      => ['required', 'string', 'max:200'],
        ], [
            'name.required' => 'Le nom de la boutique est obligatoire.',
            'name.min'      => 'Le nom est trop court (2 caractères minimum).',
            'name.max'      => 'Le nom ne peut pas dépasser 30 caractères.',
            'slogan.max'    => 'Le slogan ne peut pas dépasser 60 caractères.',
            'color.in'      => 'Couleur inconnue.',
            'share_img.regex' => 'Importez de nouveau votre photo de partage.',
        ])->validate();
        if ($data['share_src'] === 'photo' && empty($data['share_img'])) {
            throw ValidationException::withMessages(['share_img' => 'Importez une photo pour « Ma photo », ou choisissez une autre image de partage.']);
        }

        // Logo : l'actuel, les initiales, ou un logo importé par ce vendeur (session) ou déjà dans le brouillon
        $logo = $data['logo'];
        $image = $state['image'];
        if ($logo === 'initials') {
            $logoSrc = 'initials';
        } elseif ($logo === 'current') {
            $logoSrc = $image ? 'current' : 'initials';
        } else {
            $allowed = array_merge($request->session()->get(self::LOGO_SESSION, []), array_filter([$state['image'], $shop->image]));
            if (!in_array($logo, $allowed, true)) {
                throw ValidationException::withMessages(['logo' => 'Importez de nouveau votre logo.']);
            }
            $image = $logo;
            $logoSrc = 'current';
        }

        // Photo de partage = première diapo de la couverture (les autres réglages de la couverture sont gardés)
        $slides = $config['cover']['slides'];
        $slides[0] = ['src' => $data['share_src'], 'img' => $data['share_src'] === 'photo' ? $data['share_img'] : null, 'pid' => null];
        $config['cover'] = array_merge($config['cover'], ['on' => true, 'slides' => $slides, 'slogan' => trim((string) ($data['slogan'] ?? ''))]);
        $config['logo'] = array_merge($config['logo'], ['src' => $logoSrc]);

        return array_merge($state, ['config' => $config, 'accent' => strtoupper($data['color']), 'name' => trim($data['name']), 'image' => $image]);
    }

    /** Enregistre le brouillon (rien n'est montré aux visiteurs). */
    public static function saveDraft(Shop $shop, array $state): void
    {
        $shop->update(['storefront_draft' => [
            'config' => $state['config'], 'accent' => $state['accent'],
            'extra' => ['name' => $state['name'], 'image' => $state['image']],
            'at' => now()->toIso8601String(),
        ]]);
    }

    /** Publie une version de travail : les visiteurs la voient tout de suite. */
    public static function publish(Shop $shop, array $state): void
    {
        $old = $shop->image;
        Growth::publish($shop, $state['config'], $state['accent'], ['name' => $state['name'], 'image' => $state['image']]);
        // Ancien logo remplacé : son fichier n'est plus utilisé
        if ($old && $old !== $state['image']) ImageOptimizer::delete($old);
    }

    /** Abandonne le brouillon : retour à la version en ligne (un logo importé pour le brouillon est supprimé). */
    public static function discardDraft(Shop $shop): void
    {
        $draftImage = $shop->storefront_draft['extra']['image'] ?? null;
        if ($draftImage && $draftImage !== $shop->image) ImageOptimizer::delete($draftImage);
        $shop->update(['storefront_draft' => null, 'storefront_publish_at' => null]);
    }

    /** Recopie un changement publié tout de suite (thème, « Ma vitrine ») dans le brouillon en attente. */
    public static function syncDraft(Shop $shop, callable $change): void
    {
        $draft = $shop->storefront_draft;
        if (!is_array($draft) || !isset($draft['config'])) return;
        $draft['config'] = Storefront::normalize($change(Storefront::normalize($draft['config'])));
        $shop->update(['storefront_draft' => $draft]);
    }

    // ─────────────────────────────────────────────────────────── Photos

    /** Photo de partage : WebP en 800 et 1600 px. La largeur d'origine sert à prévenir d'un flou. */
    public static function uploadCover(Request $request): array
    {
        $request->validate(['file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']], [
            'file.required' => 'Choisissez une image.',
            'file.image'    => 'Ce fichier n’est pas une image.',
            'file.mimes'    => 'Formats acceptés : JPG, PNG ou WebP.',
            'file.max'      => 'Image trop lourde : 5 Mo maximum.',
        ]);
        $width = @getimagesize($request->file('file')->getRealPath())[0] ?? null;
        $path = ImageOptimizer::store($request->file('file'), 'covers', true);
        return ['path' => $path, 'url' => ImageOptimizer::url(str_replace('/medium/', '/large/', $path), 'medium'), 'width' => $width];
    }

    /** Logo : gardé pour cette session, appliqué au brouillon puis à la publication. */
    public static function uploadLogo(Request $request): array
    {
        $request->validate(['file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']], [
            'file.required' => 'Choisissez une image.',
            'file.image'    => 'Ce fichier n’est pas une image.',
            'file.mimes'    => 'Formats acceptés : PNG, JPG ou WebP.',
            'file.max'      => 'Logo trop lourd : 2 Mo maximum.',
        ]);
        $path = ImageOptimizer::store($request->file('file'), 'shops');
        $list = array_slice(array_merge($request->session()->get(self::LOGO_SESSION, []), [$path]), -10);
        $request->session()->put(self::LOGO_SESSION, $list);
        return ['path' => $path, 'url' => ImageOptimizer::url($path, 'thumb')];
    }

    // ─────────────────────────────────────────────────────────── Thème et aperçu

    /** Adopter un thème (barre « Thèmes Shopio ») : publié tout de suite, brouillon compris. */
    public static function useModel(Shop $shop, string $model): void
    {
        $apply = function (array $c) use ($model) { $c['page_model'] = $model; return $c; };
        Growth::applyEverywhere($shop, $apply);
        self::syncDraft($shop->fresh(), $apply);
    }

    /** Lien d'aperçu partageable (48 h) : il montre le brouillon, avec un formulaire d'avis. */
    public static function createPreview(Shop $shop): string
    {
        $shop->update(['preview_token' => Str::lower(Str::random(24)), 'preview_expires_at' => now()->addHours(Growth::PREVIEW_HOURS), 'preview_feedback' => []]);
        return route('storefront.preview-link', $shop->preview_token);
    }

    public static function disablePreview(Shop $shop): void
    {
        $shop->update(['preview_token' => null, 'preview_expires_at' => null]);
    }

    // ─────────────────────────────────────────────────────────── Adresses

    /** La vitrine en mode édition, ouverte sur l'en-tête ou les réglages de la page si demandé. */
    public static function editUrl(Shop $shop, ?string $open = null): string
    {
        return Storefront::publicUrl($shop) . '?edition=1' . ($open ? '&ouvrir=' . $open : '');
    }
}
