<?php

namespace App\Services;

use App\Support\ProductPhotos;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * « Importer depuis un lien » (Créer avec Shopio IA, étape 1) : lit la page d'un produit sur un autre site
 * (Shopify, YouCan, Alibaba, AliExpress, WooCommerce… tout site qui décrit ses produits) et en tire le nom,
 * la description, le prix affiché (à titre indicatif) et jusqu'à 4 photos, rangées comme des photos envoyées.
 *
 * Lecture : 1. Shopify (/products/<nom>.json) ; 2. données produit de la page (JSON-LD schema.org) ;
 * 3. balises de partage (Open Graph) ; 4. photos Alibaba / AliExpress (alicdn) écrites dans la page.
 * Sécurité : seulement http(s) public, ports 80/443, adresses privées refusées à chaque redirection
 * (l'adresse vérifiée est celle utilisée), 3 redirections, pages 4 Mo et photos 15 Mo au plus.
 * Rien n'est créé ici : le vendeur relit, fixe son prix en GNF, puis crée le produit.
 */
class ProductImporter
{
    public const MAX_PHOTOS = 4;
    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

    /**
     * @return array{name: string, description: string, photos: list<array{path: string, url: string}>, price: ?string, currency: ?string, site: string, url: string}
     * @throws \RuntimeException avec un message prêt à montrer au vendeur
     */
    public function import(string $url, int $maxPhotos = self::MAX_PHOTOS): array
    {
        return $this->finish($this->read($url), $url, $maxPhotos);
    }

    /**
     * Données lues par le bouton « Importer dans Shopio » dans le navigateur du vendeur (la page telle qu'il la voit,
     * pour les sites qui refusent la lecture par le serveur). Les photos sont téléchargées avec les mêmes contrôles.
     */
    public function importData(array $d, int $maxPhotos = self::MAX_PHOTOS): array
    {
        $url = (string) ($d['url'] ?? '');
        $out = ['name' => (string) ($d['name'] ?? ''), 'description' => (string) ($d['description'] ?? ''), 'images' => array_values(array_filter((array) ($d['images'] ?? []), 'is_string')),
            'price' => isset($d['price']) && $d['price'] !== '' ? (string) $d['price'] : null, 'currency' => isset($d['currency']) && $d['currency'] !== '' ? strtoupper((string) $d['currency']) : null,
            'site' => preg_replace('/^www\./', '', (string) parse_url($url, PHP_URL_HOST))];
        return $this->finish($this->clean($out, $url), $url, $maxPhotos);
    }

    /** Photos téléchargées et rangées, résultat prêt pour l'assistant. */
    private function finish(array $data, string $url, int $maxPhotos): array
    {
        $photos = [];
        foreach ($data['images'] as $img) {
            if (count($photos) >= $maxPhotos) break;
            try {
                $bytes = $this->get($img, 15 * 1048576, 'image/*')->body();
                $info = @getimagesizefromstring($bytes);
                if (!$info || $info[0] < 200 || $info[1] < 200) continue; // icônes et vignettes ignorées
                $photos[] = ProductPhotos::store($bytes, 'products');
            } catch (\Throwable $e) {
                continue;
            }
        }
        if (!$photos && $data['name'] === '') {
            // Grands sites (AliExpress, Alibaba, Jumia…) : page vide pour les robots, remplie ensuite par le navigateur
            throw new \RuntimeException(preg_match('/(^|\.)(aliexpress|alibaba|1688|jumia|temu|shein|amazon)\./i', $data['site'])
                ? 'Ce site ne montre pas ses produits à Shopio (il les affiche seulement dans votre navigateur). Utilisez le bouton « Importer dans Shopio » depuis la page du produit, ou ajoutez les photos vous-même.'
                : 'Aucun produit trouvé sur cette page. Vérifiez le lien (la page d’un seul produit), ou ajoutez les photos vous-même.');
        }
        return ['name' => $data['name'], 'description' => $data['description'], 'photos' => $photos, 'price' => $data['price'], 'currency' => $data['currency'], 'site' => $data['site'], 'url' => $url];
    }

    /** Nom, description, prix et adresses des photos (sans rien télécharger d'autre que la page). */
    public function read(string $url): array
    {
        $site = preg_replace('/^www\./', '', (string) parse_url($url, PHP_URL_HOST));
        // 1. La page du produit (une seule demande au site : les boutiques Shopify y mettent déjà leurs données)
        $page = null; $error = null;
        try {
            $page = $this->readPage($url, $site);
        } catch (\RuntimeException $e) {
            $error = $e;
        }
        if ($page && $page['name'] !== '' && count($page['images']) >= 2) return $page;

        // 2. Boutique Shopify (y compris avec son propre nom de domaine) : fiche produit en JSON, en complément
        if (preg_match('#/products/([A-Za-z0-9][A-Za-z0-9\-_.%]*)#', (string) parse_url($url, PHP_URL_PATH), $m)) {
            try {
                $p = json_decode($this->get($this->origin($url) . '/products/' . $m[1] . '.json', 4 * 1048576, 'application/json')->body(), true)['product'] ?? null;
                if (is_array($p) && !empty($p['title'])) {
                    return $this->clean(['name' => (string) $p['title'], 'description' => (string) ($p['body_html'] ?? ''), 'text' => Str::limit(trim(strip_tags(str_ireplace(['<br>', '</p>', '</li>'], "\n", (string) ($p['body_html'] ?? '')))), 25000, '')
                        . (isset($p['variants']) ? "\nVariantes : " . implode(', ', array_filter(array_column((array) $p['variants'], 'title'))) : ''),
                        'images' => array_column((array) ($p['images'] ?? []), 'src'), 'currency' => $page['currency'] ?? null,
                        'price' => isset($p['variants'][0]['price']) ? (string) $p['variants'][0]['price'] : null, 'site' => $site], $url);
                }
            } catch (\Throwable $e) {
                // pas une boutique Shopify, ou fiche refusée : on garde ce que la page a donné
            }
        }
        if ($page) return $page;
        throw $error;
    }

    /** Lecture de la page : données produit (JSON-LD), balises de partage, photos AliExpress / Alibaba. */
    private function readPage(string $url, string $site): array
    {
        $out = ['name' => '', 'description' => '', 'images' => [], 'price' => null, 'currency' => null, 'site' => $site];
        $res = $this->get($url, 6 * 1048576, 'text/html,application/xhtml+xml');
        $html = $res->body();
        if (stripos($html, 'captcha') !== false && !preg_match('/og:title|ld\+json/i', $html)) {
            throw new \RuntimeException('Ce site bloque la lecture automatique. Enregistrez les photos depuis le site, puis ajoutez-les ici.');
        }
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?>' . $html);
        libxml_clear_errors();
        $xp = new \DOMXPath($doc);

        // Texte visible de la page (caractéristiques, variantes, contenu du colis…), pour l'Importateur Produit Shopio
        $body = $xp->query('//body')->item(0);
        if ($body) {
            $clone = $body->cloneNode(true);
            foreach ((new \DOMXPath($doc))->query('.//script|.//style|.//noscript|.//svg|.//template|.//iframe', $clone) as $n) $n->parentNode?->removeChild($n);
            foreach ((new \DOMXPath($doc))->query('.//br|.//p|.//li|.//div|.//tr|.//h1|.//h2|.//h3|.//h4', $clone) as $n) $n->appendChild($doc->createTextNode("\n"));
            $out['text'] = Str::limit(trim(preg_replace(["/[ \t\x{A0}]+/u", "/\s*\n\s*/u"], [' ', "\n"], $clone->textContent)), 25000, '');
        }

        // Données produit schema.org (JSON-LD), souvent dans un « @graph »
        foreach ($xp->query('//script[@type="application/ld+json"]') as $node) {
            $json = json_decode(trim($node->textContent), true);
            $product = is_array($json) ? $this->findProduct($json) : null;
            if (!$product) continue;
            $out['name'] = $out['name'] ?: (string) ($product['name'] ?? '');
            $out['description'] = $out['description'] ?: (is_string($product['description'] ?? null) ? $product['description'] : '');
            foreach ((array) ($product['image'] ?? []) as $k => $img) {
                $out['images'][] = is_array($img) ? ($img['url'] ?? $img['contentUrl'] ?? '') : ($k === 'url' || is_int($k) ? $img : '');
            }
            $offer = $product['offers'] ?? null;
            if (is_array($offer) && array_is_list($offer)) $offer = $offer[0] ?? null;
            if (is_array($offer)) {
                $out['price'] ??= isset($offer['price']) ? (string) $offer['price'] : (isset($offer['lowPrice']) ? (string) $offer['lowPrice'] : null);
                $out['currency'] ??= $offer['priceCurrency'] ?? null;
            }
        }

        // Balises de partage (Open Graph, Twitter)
        $meta = function (string $name) use ($xp): array {
            $vals = [];
            foreach ($xp->query('//meta[@property="' . $name . '" or @name="' . $name . '"]/@content') as $a) $vals[] = trim($a->value);
            return array_values(array_filter($vals));
        };
        $out['name'] = $out['name'] ?: ($meta('og:title')[0] ?? '');
        $out['description'] = $out['description'] ?: ($meta('og:description')[0] ?? ($meta('description')[0] ?? ''));
        array_push($out['images'], ...$meta('og:image:secure_url'), ...$meta('og:image'), ...$meta('twitter:image'));
        $out['price'] ??= $meta('product:price:amount')[0] ?? ($meta('og:price:amount')[0] ?? null);
        $out['currency'] ??= $meta('product:price:currency')[0] ?? ($meta('og:price:currency')[0] ?? null);
        if ($out['name'] === '') $out['name'] = trim((string) ($xp->query('//title')->item(0)?->textContent ?? ''));

        // Alibaba / AliExpress : les photos du produit sont écrites dans les scripts de la page
        if (preg_match('/(^|\.)(alibaba|aliexpress|1688)\./i', $site)) {
            preg_match_all('#(?:https?:)?//[a-z0-9.-]*alicdn\.com/[^"\'\s\\\\]+?\.(?:jpe?g|png|webp)#i', $html, $mm);
            foreach ($mm[0] as $img) {
                if (!preg_match('#_(?:\d{2,3})x(?:\d{2,3})|/tps/|/imgextra/.*-\d{1,2}-\d{1,2}\.#', $img)) $out['images'][] = $img;
            }
        }
        return $this->clean($out, $url);
    }

    /** Nœud « Product » d'un JSON-LD (à la racine, dans une liste ou dans « @graph »). */
    private function findProduct(array $j): ?array
    {
        $type = $j['@type'] ?? null;
        if ($type === 'Product' || (is_array($type) && in_array('Product', $type, true)) || $type === 'ProductGroup') return $j;
        foreach (array_is_list($j) ? $j : ($j['@graph'] ?? []) as $item) {
            if (is_array($item) && ($found = $this->findProduct($item))) return $found;
        }
        return null;
    }

    /** Textes nettoyés, photos en adresses complètes sans doublon. */
    private function clean(array $out, string $base): array
    {
        $text = fn (string $v) => trim(preg_replace('/[ \t]+/u', ' ', preg_replace('/\s*\n\s*/u', "\n", html_entity_decode(strip_tags(str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</li>'], "\n", $v)), ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
        $out['name'] = Str::limit($text((string) $out['name']), 150, '');
        $out['description'] = Str::limit($text((string) $out['description']), 3000, '');
        $out['price'] = $out['price'] !== null && is_numeric(str_replace([',', ' '], ['.', ''], (string) $out['price'])) ? (string) $out['price'] : null;
        $out['currency'] = is_string($out['currency']) && preg_match('/^[A-Z]{3}$/', $out['currency']) ? $out['currency'] : null;
        $seen = [];
        foreach ($out['images'] as $img) {
            if (!is_string($img) || ($img = trim($img)) === '') continue;
            if (str_starts_with($img, '//')) $img = 'https:' . $img;
            elseif (!preg_match('#^https?://#i', $img)) $img = $this->origin($base) . '/' . ltrim($img, '/');
            $key = preg_replace('#\?.*$#', '', $img);
            if (!isset($seen[$key])) $seen[$key] = $img;
        }
        $out['images'] = array_slice(array_values($seen), 0, 12);
        $out['text'] = (string) ($out['text'] ?? '');
        return $out;
    }

    /**
     * Télécharge des photos (adresses données par la page ou choisies par le vendeur), avec les contrôles habituels.
     * @return list<array{path: string, url: string}>
     */
    public function downloadPhotos(array $urls, int $max = self::MAX_PHOTOS): array
    {
        $photos = [];
        foreach (array_slice(array_values(array_unique(array_filter($urls, 'is_string'))), 0, 12) as $img) {
            if (count($photos) >= $max) break;
            if (str_starts_with($img, '//')) $img = 'https:' . $img;
            try {
                $bytes = $this->get($img, 15 * 1048576, 'image/*')->body();
                $info = @getimagesizefromstring($bytes);
                if (!$info || $info[0] < 200 || $info[1] < 200) continue;
                $photos[] = ProductPhotos::store($bytes, 'products');
            } catch (\Throwable $e) {
                continue;
            }
        }
        return $photos;
    }

    private function origin(string $url): string
    {
        $p = parse_url($url);
        return ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');
    }

    /** GET sûr : adresse publique vérifiée (et utilisée), redirections vérifiées une à une, taille limitée. */
    protected function get(string $url, int $maxBytes, string $accept, int $hops = 0): Response
    {
        $p = parse_url($url);
        $scheme = strtolower($p['scheme'] ?? '');
        $host = strtolower(trim($p['host'] ?? '', '[]'));
        $port = $p['port'] ?? ($scheme === 'https' ? 443 : 80);
        if (!in_array($scheme, ['http', 'https'], true) || $host === '' || !in_array($port, [80, 443], true) || isset($p['user'])) {
            throw new \RuntimeException('Lien non accepté : collez l’adresse complète de la page du produit (https://…).');
        }
        $ips = $this->resolve($host);
        if (!$ips) throw new \RuntimeException('Site introuvable : vérifiez le lien.');
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \RuntimeException('Lien non accepté.');
            }
        }
        try {
            $res = Http::withHeaders(['User-Agent' => self::UA, 'Accept' => $accept, 'Accept-Language' => 'fr,en;q=0.8'])
                ->timeout(20)->connectTimeout(8)
                ->withOptions([
                    'allow_redirects' => false,
                    // L'adresse vérifiée ci-dessus est celle utilisée (pas de seconde résolution)
                    'curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:" . (str_contains($ips[0], ':') ? '[' . $ips[0] . ']' : $ips[0])]],
                    'on_headers' => function ($r) use ($maxBytes) {
                        if ((int) $r->getHeaderLine('Content-Length') > $maxBytes) throw new \RuntimeException('Fichier trop lourd.');
                    },
                ])
                ->get($url);
        } catch (ConnectionException $e) {
            throw new \RuntimeException('Le site ne répond pas : vérifiez le lien ou réessayez plus tard.');
        }
        if ($res->redirect()) {
            $to = (string) $res->header('Location');
            if ($hops >= 3 || $to === '') throw new \RuntimeException('Ce lien redirige trop de fois.');
            if (!preg_match('#^https?://#i', $to)) $to = $this->origin($url) . '/' . ltrim($to, '/');
            return $this->get($to, $maxBytes, $accept, $hops + 1);
        }
        if (in_array($res->status(), [401, 403, 429], true)) {
            throw new \RuntimeException('Ce site bloque la lecture automatique. Enregistrez les photos depuis le site, puis ajoutez-les ici.');
        }
        if (!$res->successful()) throw new \RuntimeException('Page introuvable (erreur ' . $res->status() . ') : vérifiez le lien.');
        if (strlen($res->body()) > $maxBytes) throw new \RuntimeException('Page trop lourde pour être lue.');
        return $res;
    }

    /** Adresses IP du site (remplaçable dans les tests). */
    protected function resolve(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) return [$host];
        $ips = [];
        foreach (@dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $r) $ips[] = $r['ip'] ?? $r['ipv6'] ?? null;
        $ips = array_values(array_filter($ips));
        usort($ips, fn ($a, $b) => str_contains($a, ':') <=> str_contains($b, ':')); // IPv4 d'abord
        return $ips ?: (gethostbynamel($host) ?: []);
    }
}
