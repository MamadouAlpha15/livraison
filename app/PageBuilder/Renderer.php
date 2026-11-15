<?php

namespace App\PageBuilder;

use App\Models\ProductPage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Rendu HTML d'une page construite. Tolérant : un type de section ou de bloc inconnu est ignoré,
 * et un réglage absent reprend sa valeur par défaut (utile si un schéma évolue après l'enregistrement).
 * Le JSON reçu doit déjà être passé par PageValidator (texte riche nettoyé).
 */
class Renderer
{
    /** Remplacé par le vrai jeton de sécurité du formulaire à chaque affichage d'une page en cache */
    public const CSRF_PLACEHOLDER = '__PB_CSRF_TOKEN__';

    /** Durée de vie du cache (avis, commandes livrées et autres produits peuvent changer entre-temps) */
    public const CACHE_MINUTES = 10;

    public static function render(array $page, RenderContext $ctx): string
    {
        $sections = self::sections($page);
        return view('page-builder.page', [
            'sections' => $sections,
            'ctx'      => $ctx,
            'seo'      => $ctx->editing ? null : self::seo($page, $sections, $ctx),
        ])->render();
    }

    /**
     * Page publiée vue par un client. Pour un visiteur non connecté, le HTML est mis en cache : la clé change
     * à chaque publication, modification du produit, de ses options ou de la boutique (compteurs forget()),
     * à la fin d'une vente flash et chaque jour (dates de livraison). Un client connecté a une page à lui
     * (nom et téléphone pré-remplis).
     */
    public static function published(ProductPage $page, RenderContext $ctx): string
    {
        if ($ctx->user()) {
            return self::render($page->published, $ctx);
        }
        $p = $ctx->product;
        $key = 'page-builder:html:' . md5(implode('|', [
            $p->id, $page->version, $page->published_at?->getTimestamp(), self::generation('product', $p->id),
            self::generation('shop', $ctx->shop->id), (int) $p->is_flash_active, now()->toDateString(),
        ]));
        $html = Cache::remember($key, now()->addMinutes(self::CACHE_MINUTES), function () use ($page, $ctx) {
            $ctx->csrfPlaceholder = true;
            return self::render($page->published, $ctx);
        });
        return str_replace(self::CSRF_PLACEHOLDER, csrf_token(), $html);
    }

    /** Les pages en cache d'un produit ou d'une boutique sont à recalculer (appelé par les modèles) */
    public static function forget(string $scope, int $id): void
    {
        Cache::forever("page-builder:gen:{$scope}:{$id}", self::generation($scope, $id) + 1);
    }

    private static function generation(string $scope, int $id): int
    {
        return (int) Cache::get("page-builder:gen:{$scope}:{$id}", 0);
    }

    /** Sections visibles, dans l'ordre, prêtes pour les vues : [['id', 'type', 'settings', 'blocks' => [['id', 'type', 'settings'], …]], …] */
    public static function sections(array $page): array
    {
        $out = [];
        foreach ($page['order'] ?? [] as $id) {
            $section = $page['sections'][$id] ?? null;
            $type = is_array($section) ? Registry::section((string) ($section['type'] ?? '')) : null;
            if (!$type || !empty($section['disabled'])) {
                continue;
            }

            $blocks = [];
            foreach ($section['block_order'] ?? [] as $bid) {
                $block = $section['blocks'][$bid] ?? null;
                $btype = is_array($block) ? Registry::block((string) ($block['type'] ?? '')) : null;
                if ($btype && empty($block['disabled']) && in_array($btype->type(), $type->blocks(), true)) {
                    $blocks[] = ['id' => (string) $bid, 'type' => $btype, 'settings' => array_replace($btype->defaults(), (array) ($block['settings'] ?? []))];
                }
            }

            $out[] = [
                'id'       => (string) $id,
                'type'     => $type,
                'settings' => array_replace($type->defaults(), (array) ($section['settings'] ?? [])),
                'blocks'   => $blocks,
            ];
        }
        return $out;
    }

    /**
     * Référencement : titre et description (choisis par le vendeur, sinon tirés du produit), lien canonique,
     * image de partage et données structurées (Product, avec la note seulement s'il y a de vrais avis ; FAQPage).
     */
    public static function seo(array $page, array $sections, RenderContext $ctx): array
    {
        $p = $ctx->product;
        $url = route('client.products.show', $p);
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $p->description)));
        $title = $page['seo']['title'] ?? $p->name . ' · ' . $ctx->shop->name;
        $description = $page['seo']['description']
            ?? ($plain !== '' ? Str::limit($plain, 155) : 'Commande ' . $p->name . ' chez ' . $ctx->shop->name . ' : paiement à la livraison.');
        $image = $ctx->photos()[0]['medium'] ?? null;

        $product = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $p->name,
            'description' => $plain !== '' ? Str::limit($plain, 500) : $description,
            'image'       => array_values(array_filter(array_column($ctx->photos(), 'medium'))),
            'brand'       => ['@type' => 'Brand', 'name' => $ctx->shop->name],
            'offers'      => [
                '@type'         => 'Offer',
                'url'           => $url,
                'price'         => (int) round($ctx->price()),
                'priceCurrency' => 'GNF',
                'availability'  => $ctx->unavailable() ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
                'seller'        => ['@type' => 'Organization', 'name' => $ctx->shop->name],
            ],
        ];
        $reviews = $ctx->reviewStats();
        if ($reviews['count'] > 0) {
            $product['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $reviews['avg'], 'reviewCount' => $reviews['count'], 'bestRating' => 5, 'worstRating' => 1];
        }

        $ld = [$product];
        $questions = [];
        foreach ($sections as $s) {
            if ($s['type']->type() !== 'faq') {
                continue;
            }
            foreach ($s['blocks'] as $b) {
                $q = trim((string) $b['settings']['question']);
                $a = trim(strip_tags((string) $b['settings']['answer']));
                if ($q !== '' && $a !== '') {
                    $questions[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
                }
            }
        }
        if ($questions) {
            $ld[] = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $questions];
        }

        return compact('title', 'description', 'url', 'image', 'ld');
    }
}
