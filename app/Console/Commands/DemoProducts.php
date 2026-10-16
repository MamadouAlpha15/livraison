<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoProducts extends Command
{
    /**
     * Crée des boutiques et des produits FICTIFS pour tester le site en local
     * (accueil, catalogue, ventes flash, page produit…). Les vendeurs de démo ont
     * une adresse en @demo.shopio.test : c'est ce qui permet de tout retrouver et
     * de tout supprimer avec --supprimer. Refusé en production.
     */
    protected $signature = 'shopio:demo {--supprimer : Supprime les boutiques et produits de démonstration}';

    protected $description = 'Crée (ou supprime) des boutiques et produits fictifs pour le développement local';

    private const EMAIL_DOMAIN = '@demo.shopio.test';

    /** Boutiques fictives : couleur des visuels + produits [nom, catégorie, prix, prix barré, stock, flash, description]. */
    private const SHOPS = [
        [
            'slug' => 'kaloum-tech', 'name' => 'Kaloum Tech', 'type' => 'Électronique',
            'address' => 'Kaloum, Conakry', 'color' => [31, 124, 246],
            'description' => 'Téléphones, accessoires et petit électronique, garantis 6 mois.',
            'products' => [
                ['Samsung Galaxy A15 128 Go', 'Électronique & Téléphones', 1850000, 2100000, 14, false, 'Écran 6,5 pouces, double SIM, batterie 5000 mAh. Livré avec chargeur.'],
                ['Tecno Spark 20 Pro', 'Électronique & Téléphones', 1650000, null, 9, true, 'Écran 120 Hz, appareil photo 108 MP, 256 Go de stockage.'],
                ['iPhone 13 reconditionné 128 Go', 'Électronique & Téléphones', 4200000, 4800000, 3, false, 'Reconditionné grade A, batterie à 88 % minimum, garantie 6 mois.'],
                ['Écouteurs sans fil Oraimo FreePods', 'Électronique & Téléphones', 245000, 300000, 40, true, 'Bluetooth 5.3, 30 h d\'autonomie avec le boîtier, résistants à la sueur.'],
                ['Power bank 20 000 mAh', 'Électronique & Téléphones', 185000, null, 25, false, 'Deux ports USB et un USB-C, charge rapide 22,5 W. Idéal pendant les coupures.'],
                ['Chargeur rapide USB-C 33 W', 'Électronique & Téléphones', 95000, null, 60, false, 'Compatible Samsung, Tecno, Infinix et iPhone (câble fourni).'],
                ['Montre connectée Haylou', 'Bijoux & Montres', 390000, 450000, 2, false, 'Suivi du sommeil et des pas, notifications, autonomie de 10 jours.'],
                ['Enceinte Bluetooth JBL Go 3', 'Électronique & Téléphones', 420000, null, 11, false, 'Étanche IP67, son puissant, 5 h d\'autonomie.'],
            ],
        ],
        [
            'slug' => 'maison-nene', 'name' => 'Maison Néné', 'type' => 'Mode',
            'address' => 'Madina, Conakry', 'color' => [214, 92, 64],
            'description' => 'Pagnes, tenues sur mesure et accessoires de mode.',
            'products' => [
                ['Robe en wax Ankara', 'Vêtements & Mode', 350000, 420000, 8, true, 'Coupe évasée, tissu wax 100 % coton. Tailles S à XXL.'],
                ['Boubou brodé homme', 'Vêtements & Mode', 650000, null, 5, false, 'Bazin riche brodé main, avec pantalon assorti.'],
                ['Pagne Vlisco 6 yards', 'Vêtements & Mode', 780000, 900000, 12, false, 'Véritable wax hollandais, motifs de la nouvelle collection.'],
                ['Chemise en bogolan', 'Vêtements & Mode', 280000, null, 15, false, 'Tissu teint à la main, coupe ajustée.'],
                ['Sandales en cuir artisanales', 'Chaussures & Accessoires', 220000, null, 18, false, 'Cuir véritable, fabriquées à Conakry. Pointures 37 à 45.'],
                ['Baskets Nike Air Force 1', 'Chaussures & Accessoires', 950000, 1100000, 4, true, 'Blanches, pointures 39 à 45.'],
                ['Sac à main en raphia', 'Chaussures & Accessoires', 310000, null, 7, false, 'Tressé main, doublure intérieure et fermeture éclair.'],
                ['Collier en perles du Fouta', 'Bijoux & Montres', 160000, null, 20, false, 'Perles traditionnelles, fermoir réglable.'],
                ['Bracelet en or 18 carats', 'Bijoux & Montres', 2400000, null, 2, false, 'Maille fine, 6 grammes, certificat fourni.'],
            ],
        ],
        [
            'slug' => 'beaute-djoliba', 'name' => 'Beauté Djoliba', 'type' => 'Beauté',
            'address' => 'Ratoma, Conakry', 'color' => [176, 82, 160],
            'description' => 'Soins naturels au karité, parfums et cosmétiques.',
            'products' => [
                ['Beurre de karité pur 500 g', 'Beauté & Cosmétiques', 65000, null, 50, false, 'Karité brut non raffiné, récolté en Haute-Guinée.'],
                ['Savon noir africain', 'Beauté & Cosmétiques', 25000, 35000, 80, true, 'Savon artisanal pour le visage et le corps.'],
                ['Huile de coco vierge 250 ml', 'Beauté & Cosmétiques', 55000, null, 34, false, 'Pressée à froid, pour les cheveux et la peau.'],
                ['Parfum Oud Royal 100 ml', 'Beauté & Cosmétiques', 480000, 560000, 6, false, 'Notes boisées et ambrées, longue tenue.'],
                ['Coffret soins visage', 'Beauté & Cosmétiques', 290000, null, 9, false, 'Nettoyant, sérum et crème hydratante.'],
                ['Mèches brésiliennes 20 pouces', 'Beauté & Cosmétiques', 750000, 850000, 5, true, 'Cheveux naturels, 3 paquets.'],
                ['Henné naturel 200 g', 'Beauté & Cosmétiques', 30000, null, 3, false, 'Pour les mains, les pieds et les cheveux.'],
            ],
        ],
        [
            'slug' => 'marche-madina-express', 'name' => 'Marché Madina Express', 'type' => 'Alimentation',
            'address' => 'Marché Madina, Conakry', 'color' => [46, 150, 92],
            'description' => 'Épicerie, produits locaux et articles pour la maison, livrés le jour même.',
            'products' => [
                ['Riz parfumé 25 kg', 'Épicerie & Alimentation', 420000, null, 30, false, 'Riz long grain parfumé, sac de 25 kg.'],
                ['Huile végétale 5 L', 'Épicerie & Alimentation', 145000, 160000, 45, true, 'Huile raffinée pour la cuisine.'],
                ['Fonio précuit 1 kg', 'Épicerie & Alimentation', 35000, null, 60, false, 'Fonio du Fouta, lavé et précuit.'],
                ['Café Ziama moulu 500 g', 'Épicerie & Alimentation', 70000, null, 26, false, 'Café de Macenta, torréfaction artisanale.'],
                ['Jus de bissap 1 L', 'Boissons', 15000, null, 90, false, 'Bissap frais au gingembre, sans conservateur.'],
                ['Pack eau minérale 12 × 1,5 L', 'Boissons', 60000, null, 40, false, 'Eau minérale de source.'],
                ['Marmite en fonte 10 L', 'Maison & Décoration', 260000, 300000, 8, false, 'Fonte épaisse, idéale pour le riz et les sauces.'],
                ['Ventilateur rechargeable', 'Maison & Décoration', 380000, 450000, 4, true, 'Batterie de 8 h, lumière LED intégrée.'],
                ['Panier tressé décoratif', 'Maison & Décoration', 90000, null, 14, false, 'Fibres naturelles, fabriqué à la main.'],
            ],
        ],
        [
            'slug' => 'sport-plus-ratoma', 'name' => 'Sport Plus Ratoma', 'type' => 'Sport',
            'address' => 'Kipé, Ratoma', 'color' => [232, 150, 20],
            'description' => 'Maillots, équipement sportif et jouets pour enfants.',
            'products' => [
                ['Maillot Syli National 2026', 'Sport & Loisirs', 250000, 300000, 22, true, 'Maillot officiel domicile, tailles S à XXL.'],
                ['Ballon de football taille 5', 'Sport & Loisirs', 120000, null, 30, false, 'Cousu main, pour terrain synthétique et herbe.'],
                ['Haltères réglables 2 × 10 kg', 'Sport & Loisirs', 450000, null, 6, false, 'Disques en fonte et barres chromées.'],
                ['Tapis de yoga', 'Sport & Loisirs', 110000, 140000, 17, false, 'Antidérapant, 6 mm d\'épaisseur.'],
                ['Vélo enfant 16 pouces', 'Jouets & Enfants', 850000, null, 3, false, 'Avec roulettes amovibles, pour les 4 à 7 ans.'],
                ['Voiture télécommandée', 'Jouets & Enfants', 230000, 280000, 10, false, 'Batterie rechargeable, portée de 30 m.'],
                ['Puzzle 500 pièces carte d\'Afrique', 'Jouets & Enfants', 75000, null, 1, false, 'Jeu éducatif à partir de 8 ans.'],
            ],
        ],
    ];

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Commande réservée au développement : elle ne s\'exécute pas en production.');
            return self::FAILURE;
        }

        $removed = $this->removeDemo();

        if ($this->option('supprimer')) {
            $this->info("Données de démonstration supprimées ({$removed} produits).");
            return self::SUCCESS;
        }

        $disk = ImageOptimizer::activeDisk();
        $total = 0;

        foreach (self::SHOPS as $data) {
            $owner = User::create([
                'name'              => $data['name'],
                'email'             => $data['slug'] . self::EMAIL_DOMAIN,
                'password'          => bcrypt('demo1234'),
                'role'              => 'admin',
                'country'           => 'GN',
            ]);
            $owner->forceFill(['email_verified_at' => now()])->save();

            $shop = Shop::create([
                'user_id'         => $owner->id,
                'name'            => $data['name'],
                'type'            => $data['type'],
                'description'     => $data['description'],
                'address'         => $data['address'],
                'phone'           => '+2246' . random_int(10000000, 99999999),
                'email'           => $owner->email,
                'currency'        => 'GNF',
                'country'         => 'GN',
                'commission_rate' => 0.2,
                'is_approved'     => true,
                'image'           => $this->makeImage($disk, 'shops', $data['name'], $data['type'], $data['color']),
            ]);
            $owner->forceFill(['shop_id' => $shop->id])->save();

            foreach ($data['products'] as $i => [$name, $category, $price, $original, $stock, $flash, $description]) {
                Product::create([
                    'shop_id'             => $shop->id,
                    'name'                => $name,
                    'description'         => $description,
                    'category'            => $category,
                    'price'               => $price,
                    'original_price'      => $original,
                    'flash_price'         => $flash ? round($price * 0.8, -3) : null,
                    'flash_starts_at'     => $flash ? now()->subHour() : null,
                    'flash_ends_at'       => $flash ? now()->addHours(random_int(6, 72)) : null,
                    'stock'               => $stock,
                    'low_stock_threshold' => 3,
                    'unit'                => 'pièce',
                    'is_active'           => true,
                    'is_available'        => true,
                    'is_featured'         => $i < 2,
                    'image'               => $this->makeImage($disk, 'products', $name, $category, $data['color']),
                ]);
                $total++;
            }

            $this->line("  {$data['name']} : " . count($data['products']) . ' produits');
        }

        $this->info("{$total} produits fictifs créés dans " . count(self::SHOPS) . ' boutiques.');
        $this->line('Connexion vendeur de démo : <comment>kaloum-tech' . self::EMAIL_DOMAIN . '</comment> / <comment>demo1234</comment>');
        $this->line('Pour tout supprimer : <comment>php artisan shopio:demo --supprimer</comment>');

        return self::SUCCESS;
    }

    /** Supprime les vendeurs de démo, leurs boutiques, leurs produits et les images générées. */
    private function removeDemo(): int
    {
        $owners = User::where('email', 'like', '%' . self::EMAIL_DOMAIN)->get();
        $shops = Shop::whereIn('user_id', $owners->pluck('id'))->get();
        $products = Product::whereIn('shop_id', $shops->pluck('id'))->get();

        foreach ($products as $product) {
            ImageOptimizer::delete($product->image);
            $product->delete();
        }
        foreach ($shops as $shop) {
            ImageOptimizer::delete($shop->image);
            $shop->delete();
        }
        User::whereIn('id', $owners->pluck('id'))->delete();

        return $products->count();
    }

    /**
     * Génère un visuel carré (fond dégradé de la couleur de la boutique, nom du produit,
     * mention « démo ») en variantes thumb + medium, comme ImageOptimizer::store().
     */
    private function makeImage(string $disk, string $folder, string $title, string $label, array $rgb): string
    {
        $size = 800;
        $img = imagecreatetruecolor($size, $size);
        [$r, $g, $b] = $rgb;

        // Dégradé vertical clair → couleur légèrement plus soutenue
        for ($y = 0; $y < $size; $y++) {
            $t = $y / $size;
            $mix = fn ($c) => (int) (255 - (255 - $c) * (0.18 + 0.22 * $t));
            imageline($img, 0, $y, $size, $y, imagecolorallocate($img, $mix($r), $mix($g), $mix($b)));
        }
        // Grand cercle décoratif
        imagefilledellipse($img, (int) ($size * 0.72), (int) ($size * 0.3), 520, 520, imagecolorallocatealpha($img, $r, $g, $b, 100));

        $ink = imagecolorallocate($img, (int) ($r * 0.35), (int) ($g * 0.35), (int) ($b * 0.35));
        $accent = imagecolorallocate($img, $r, $g, $b);
        $bold = $this->font(true);
        $regular = $this->font(false);

        if ($bold && $regular) {
            imagettftext($img, 22, 0, 60, 120, $accent, $bold, mb_strtoupper($label));
            $y = 420;
            foreach ($this->wrap($title, 16) as $line) {
                imagettftext($img, 54, 0, 60, $y, $ink, $bold, $line);
                $y += 74;
            }
            imagettftext($img, 20, 0, 60, $size - 60, $ink, $regular, 'Shopio · image de démonstration');
        } else {
            imagestring($img, 5, 40, 380, $title, $ink);
        }

        $filename = Str::random(20);
        foreach (['thumb' => 300, 'medium' => 800] as $variant => $width) {
            $out = $width === $size ? $img : imagescale($img, $width, $width);
            ob_start();
            imagewebp($out, null, 80);
            Storage::disk($disk)->put("{$folder}/{$variant}/{$filename}.webp", ob_get_clean());
            if ($out !== $img) imagedestroy($out);
        }
        imagedestroy($img);

        return "{$folder}/medium/{$filename}.webp";
    }

    private function wrap(string $text, int $width): array
    {
        return array_slice(explode("\n", wordwrap($text, $width, "\n", true)), 0, 4);
    }

    private function font(bool $bold): ?string
    {
        $candidates = $bold
            ? ['C:/Windows/Fonts/arialbd.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', '/Library/Fonts/Arial Bold.ttf']
            : ['C:/Windows/Fonts/arial.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', '/Library/Fonts/Arial.ttf'];

        foreach ($candidates as $path) {
            if (is_file($path)) return $path;
        }

        return null;
    }
}
