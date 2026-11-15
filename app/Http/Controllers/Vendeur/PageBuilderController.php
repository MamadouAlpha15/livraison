<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\PageTemplate;
use App\Models\Product;
use App\Models\ProductPage;
use App\PageBuilder\Ai\PageWriter;
use App\PageBuilder\ColorSchemes;
use App\PageBuilder\Media\Mp4;
use App\PageBuilder\PageFactory;
use App\PageBuilder\PageValidator;
use App\PageBuilder\Registry;
use App\PageBuilder\RenderContext;
use App\PageBuilder\Renderer;
use App\PageBuilder\Templates\ShopioTemplates;
use App\PageBuilder\ValidationContext;
use App\Services\ImageOptimizer;
use App\Support\Ai\AiUnavailable;
use App\Support\IconLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Éditeur de page produit (sections et blocs) : l'éditeur, l'aperçu en direct, l'enregistrement
 * automatique du brouillon, la publication et l'envoi d'images.
 * Chaque enregistrement passe par PageValidator ; le brouillon n'est jamais visible des clients.
 */
class PageBuilderController extends Controller
{
    /** Taille maximale du JSON d'une page envoyé par l'éditeur */
    private const MAX_PAYLOAD = 300_000;

    private const VIDEO_MAX_KB = 20 * 1024;

    private const VIDEO_MAX_SECONDS = 60;

    private const TEMPLATES_PER_SHOP = 20;

    public function edit(Product $product)
    {
        $page = $this->page($product);
        $shop = $product->shop;

        $config = [
            'page'      => $page->draft,
            'revision'  => self::revision($page),
            'sections'  => collect(Registry::sections())->map->schema()->all(),
            'blocks'    => collect(Registry::blocks())->map->schema()->all(),
            'library'   => Registry::library(),
            'schemes'   => collect(ColorSchemes::for($shop))->map(fn ($c, $k) => ['label' => ColorSchemes::LABELS[$k], 'bg' => $c['bg'], 'text' => $c['text'], 'button' => $c['button']])->all(),
            'icons'     => collect(IconLibrary::names())->mapWithKeys(fn ($n) => [$n => IconLibrary::svg($n, '', 18)])->all(),
            'products'  => $shop->products()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'image'])
                ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'image' => ImageOptimizer::url($p->image, 'thumb')])->all(),
            // Préfixe des URL des médias (stockage local ou cloud) : ImageOptimizer renvoie le chemin tel quel hors /medium/
            'mediaBase' => substr((string) ImageOptimizer::url('_'), 0, -1),
            // Ce que Google affiche si le vendeur ne remplit rien (mêmes règles que Renderer::seo)
            'seoDefaults' => [
                'title'       => $product->name . ' · ' . $shop->name,
                'description' => ($plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->description)))) !== ''
                    ? Str::limit($plain, 155) : 'Commande ' . $product->name . ' chez ' . $shop->name . ' : paiement à la livraison.',
                'url'         => route('client.products.show', $product),
                'max'         => PageValidator::SEO,
            ],
            'published' => [
                'version' => $page->version,
                'at'      => $page->published_at?->translatedFormat('j F Y à H:i'),
                'pending' => $page->hasUnpublishedChanges(),
                'enabled' => $page->is_enabled,
            ],
            'urls' => [
                'save'    => route('products.builder.save', $product),
                'preview' => route('products.builder.preview', $product),
                'frame'   => route('products.builder.frame', $product),
                'publish' => route('products.builder.publish', $product),
                'upload'  => route('products.builder.upload', $product),
                'video'   => route('products.builder.video', $product),
                'versions' => route('products.builder.versions', $product),
                'restore'  => route('products.builder.restore', [$product, '__V__']),
                'templates' => route('products.builder.templates', $product),
                'templateStore' => route('products.builder.templates.store', $product),
                'templateApply' => route('products.builder.templates.apply', [$product, '__T__']),
                'templateDestroy' => route('products.builder.templates.destroy', [$product, '__T__']),
                'public'  => route('client.products.show', $product),
                'toggle'  => route('products.builder.toggle', $product),
                'ai'      => route('products.builder.ai', $product),
            ],
        ];

        return view('vendeur.page-builder.editor', compact('product', 'page', 'config'));
    }

    /** Aperçu du brouillon enregistré, chargé une fois dans le cadre de l'éditeur */
    public function frame(Product $product)
    {
        $page = $this->page($product);
        return response(Renderer::render($page->draft, new RenderContext($product->shop, $product, editing: true)))
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /** Aperçu de la page en cours d'édition (pas encore enregistrée) : HTML, ou erreurs 422 */
    public function preview(Request $request, Product $product)
    {
        $this->authorizePayload($request, $product);
        $clean = $this->validator($product)->validate((array) $request->input('page'));

        return response(Renderer::render($clean, new RenderContext($product->shop, $product, editing: true)));
    }

    /** Enregistrement automatique du brouillon. Refusé (409) si la page a changé depuis, dans un autre onglet. */
    public function save(Request $request, Product $product)
    {
        $this->authorizePayload($request, $product);
        $page = $this->page($product);

        if ((string) $request->input('revision') !== self::revision($page)) {
            return response()->json(['message' => 'La page a été modifiée ailleurs (autre onglet ou autre membre de l\'équipe). Recharge pour reprendre la dernière version.'], 409);
        }

        $clean = $this->validator($product)->validate((array) $request->input('page'));
        $page->update(['draft' => $clean, 'updated_by' => $request->user()->id]);
        $page->refresh();

        return response()->json([
            'revision' => self::revision($page),
            'page'     => $page->draft,
            'pending'  => $page->hasUnpublishedChanges(),
        ]);
    }

    /** Publie le brouillon : les clients voient cette version, l'ancienne est gardée dans l'historique. */
    public function publish(Request $request, Product $product)
    {
        Gate::authorize('edit-product-page', $product);
        $page = $this->page($product);
        $clean = $this->validator($product)->validate($page->draft);

        DB::transaction(function () use ($page, $clean, $request) {
            $page->forceFill([
                'draft'        => $clean,
                'published'    => $clean,
                'published_at' => now(),
                'version'      => $page->version + 1,
                'updated_by'   => $request->user()->id,
            ])->save();

            $page->versions()->create(['version' => $page->version, 'data' => $clean, 'published_by' => $request->user()->id]);

            $old = $page->versions()->pluck('id')->slice(ProductPage::VERSIONS_KEPT);
            if ($old->isNotEmpty()) {
                $page->versions()->whereIn('id', $old)->delete();
            }
        });
        $page->refresh();

        return response()->json([
            'message'  => 'Page publiée.',
            'revision' => self::revision($page),
            'version'  => $page->version,
            'at'       => $page->published_at->translatedFormat('j F Y à H:i'),
            'enabled'  => $page->is_enabled,
        ]);
    }

    /**
     * Active ou désactive la page construite de ce produit. Désactivée (par défaut), les clients voient la page
     * produit habituelle ; il faut avoir publié au moins une fois pour l'activer.
     */
    public function toggle(Request $request, Product $product)
    {
        $page = $this->page($product);
        $enabled = $request->validate(['enabled' => ['required', 'boolean']])['enabled'];
        if ($enabled && !$page->isPublished()) {
            throw ValidationException::withMessages(['enabled' => 'Publie d\'abord ta page : il n\'y a encore rien à montrer à tes clients.']);
        }
        $page->forceFill(['is_enabled' => (bool) $enabled])->save();

        return response()->json([
            'enabled' => $page->is_enabled,
            'message' => $page->is_enabled
                ? 'Page activée : tes clients voient maintenant cette page.'
                : 'Page désactivée : tes clients voient de nouveau la page produit habituelle.',
        ]);
    }

    /** Image d'une section ou d'un bloc : WebP en trois tailles, dans le dossier de la boutique */
    public function upload(Request $request, Product $product)
    {
        Gate::authorize('edit-product-page', $product);
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'image.image' => 'Ce fichier n\'est pas une image.',
            'image.mimes' => 'Formats acceptés : JPG, PNG ou WebP.',
            'image.max'   => 'L\'image dépasse 8 Mo.',
        ]);

        $path = ImageOptimizer::store($request->file('image'), 'page-builder/' . $product->shop_id, large: true);

        return response()->json(['path' => $path, 'url' => ImageOptimizer::url($path, 'medium')]);
    }

    /** Vidéo MP4 d'une section : 20 Mo et 60 secondes au plus, gardée telle quelle (pas de réencodage) */
    public function uploadVideo(Request $request, Product $product)
    {
        Gate::authorize('edit-product-page', $product);
        $request->validate([
            'video' => ['required', 'file', 'max:' . self::VIDEO_MAX_KB],
        ], [
            'video.max' => 'La vidéo dépasse 20 Mo : raccourcis-la ou colle plutôt un lien YouTube ou TikTok.',
        ]);

        // Le format est vérifié dans le fichier lui-même (en-tête MP4), pas dans le type annoncé par le navigateur
        $duration = Mp4::duration($request->file('video')->getRealPath());
        if ($duration === null) {
            throw ValidationException::withMessages(['video' => 'Envoie une vidéo au format MP4 (celle-ci est illisible ou dans un autre format).']);
        }
        if ($duration > self::VIDEO_MAX_SECONDS + 0.5) {
            throw ValidationException::withMessages(['video' => 'La vidéo dure ' . (int) round($duration) . ' secondes : 60 secondes au plus.']);
        }

        $path = $request->file('video')->storeAs('page-builder/' . $product->shop_id . '/videos', Str::random(32) . '.mp4', ImageOptimizer::activeDisk());

        return response()->json(['path' => $path, 'url' => ImageOptimizer::url($path)]);
    }

    /** Shopio IA écrit les textes de la page à partir de la fiche produit, dans le brouillon (rien n'est publié) */
    public function aiWrite(Request $request, Product $product, PageWriter $writer)
    {
        $page = $this->page($product);
        $quota = 'page-builder:ai:' . $product->shop_id . ':' . now()->toDateString();
        if ((int) Cache::get($quota, 0) >= PageWriter::DAILY_LIMIT) {
            return response()->json(['message' => 'Shopio IA a déjà écrit ' . PageWriter::DAILY_LIMIT . ' pages pour ta boutique aujourd\'hui. Réessaie demain.'], 429);
        }

        try {
            $copy = $writer->write($product, $product->shop);
        } catch (AiUnavailable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
        Cache::put($quota, (int) Cache::get($quota, 0) + 1, now()->endOfDay());

        return $this->replaceDraft($request, $product, $page, PageWriter::apply($page->draft, $copy),
            'Shopio IA a écrit une proposition dans ton brouillon : relis-la, change ce que tu veux, puis publie.');
    }

    /** Publications gardées (les 10 dernières), de la plus récente à la plus ancienne */
    public function versions(Product $product)
    {
        $page = $this->page($product);
        return response()->json(['versions' => $page->versions()->with('publisher:id,name')->get()->map(fn ($v) => [
            'version' => $v->version,
            'at'      => $v->created_at?->translatedFormat('j F Y à H:i'),
            'by'      => $v->publisher?->name,
            'current' => $v->version === $page->version,
        ])]);
    }

    /** Remet une publication passée dans le brouillon (rien n'est publié tant que le vendeur ne publie pas) */
    public function restore(Request $request, Product $product, string $version)
    {
        $page = $this->page($product);
        $old = $page->versions()->where('version', (int) $version)->firstOrFail();
        return $this->replaceDraft($request, $product, $page, $old->data, "Version {$version} remise dans le brouillon. Publie pour la remettre en ligne.");
    }

    /** Modèles disponibles : ceux de Shopio, puis ceux de la boutique */
    public function templates(Product $product)
    {
        $this->page($product);
        ShopioTemplates::ensure();
        $map = fn (PageTemplate $t) => [
            'id'          => $t->id,
            'name'        => $t->name,
            'category'    => PageTemplate::CATEGORIES[$t->category] ?? $t->category,
            'description' => $t->description,
            'sections'    => collect($t->data['order'] ?? [])->map(fn ($id) => Registry::section((string) ($t->data['sections'][$id]['type'] ?? ''))?->name())->filter()->values(),
            'mine'        => !$t->isShopio(),
        ];
        $all = PageTemplate::availableTo($product->shop_id)->get();

        return response()->json([
            'shopio'     => $all->filter->isShopio()->map($map)->values(),
            'mine'       => $all->reject->isShopio()->map($map)->values(),
            'categories' => PageTemplate::CATEGORIES,
            'max'        => self::TEMPLATES_PER_SHOP,
        ]);
    }

    /** Applique un modèle : il remplace le brouillon (l'éditeur permet d'annuler) */
    public function applyTemplate(Request $request, Product $product, PageTemplate $template)
    {
        $page = $this->page($product);
        Gate::authorize('use-page-template', $template);
        $page->page_template_id = $template->id;
        return $this->replaceDraft($request, $product, $page, $template->data, "Modèle « {$template->name} » appliqué au brouillon.");
    }

    /** Enregistre la page en cours comme modèle de la boutique, pour d'autres produits */
    public function storeTemplate(Request $request, Product $product)
    {
        $this->authorizePayload($request, $product);
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:80'],
            'category' => ['required', Rule::in(array_keys(PageTemplate::CATEGORIES))],
        ], ['name.required' => 'Donne un nom à ton modèle.']);

        if (PageTemplate::where('shop_id', $product->shop_id)->count() >= self::TEMPLATES_PER_SHOP) {
            throw ValidationException::withMessages(['name' => 'Ta boutique a déjà ' . self::TEMPLATES_PER_SHOP . ' modèles : supprimes-en un d\'abord.']);
        }
        $clean = $this->validator($product)->validate((array) $request->input('page'));

        $tpl = PageTemplate::create([
            'shop_id' => $product->shop_id, 'name' => trim(strip_tags($data['name'])), 'category' => $data['category'],
            'description' => 'Enregistré depuis « ' . $product->name . ' »', 'data' => $clean, 'created_by' => $request->user()->id,
        ]);
        return response()->json(['id' => $tpl->id, 'message' => "Modèle « {$tpl->name} » enregistré."], 201);
    }

    public function destroyTemplate(Product $product, PageTemplate $template)
    {
        $this->page($product);
        Gate::authorize('manage-page-template', $template);
        $template->delete();
        return response()->json(['message' => 'Modèle supprimé.']);
    }

    /** Remplace le brouillon par d'autres données (version passée, modèle), validées pour cette boutique */
    private function replaceDraft(Request $request, Product $product, ProductPage $page, array $data, string $message)
    {
        $clean = $this->validator($product)->validate($data);
        $page->forceFill(['draft' => $clean, 'updated_by' => $request->user()->id])->save();
        $page->refresh();

        return response()->json([
            'message'  => $message,
            'page'     => $page->draft,
            'revision' => self::revision($page),
            'pending'  => $page->hasUnpublishedChanges(),
        ]);
    }

    /** La page du produit, créée au premier passage avec la section Produit */
    private function page(Product $product): ProductPage
    {
        Gate::authorize('edit-product-page', $product);

        $page = ProductPage::firstOrCreate(['product_id' => $product->id], ['shop_id' => $product->shop_id, 'draft' => PageFactory::blank()]);
        if ($page->draft === null) {
            $page->update(['draft' => $page->published ?? PageFactory::blank()]);
        }
        // Relue depuis la base : valeurs par défaut des colonnes (version 0) et JSON tel qu'enregistré
        return $page->wasRecentlyCreated || $page->wasChanged() ? $page->refresh() : $page;
    }

    private function validator(Product $product): PageValidator
    {
        return new PageValidator(new ValidationContext($product->shop, $product));
    }

    private function authorizePayload(Request $request, Product $product): void
    {
        Gate::authorize('edit-product-page', $product);
        abort_if(strlen($request->getContent()) > self::MAX_PAYLOAD, 413, 'Page trop lourde.');
        abort_unless(is_array($request->input('page')), 422, 'Page manquante.');
    }

    /**
     * Empreinte du brouillon enregistré : l'éditeur la renvoie pour détecter une modification faite ailleurs.
     * Calculée sur les clés triées : MySQL range lui-même les clés d'une colonne JSON.
     */
    public static function revision(ProductPage $page): string
    {
        $sort = function ($v) use (&$sort) {
            if (!is_array($v)) {
                return $v;
            }
            $v = array_map($sort, $v);
            if (!array_is_list($v)) {
                ksort($v);
            }
            return $v;
        };
        return md5(json_encode($sort($page->draft)) . '|' . $page->version);
    }
}
