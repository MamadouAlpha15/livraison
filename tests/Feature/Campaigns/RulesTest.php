<?php

use App\Models\AdCampaign as C;
use App\Models\ProductVariant;
use App\Support\Campaigns\CampaignRules as R;
use Tests\Support\CampaignFixtures as F;

test('une campagne valide ne renvoie aucune erreur', function () {
    $shop = F::shop();
    expect(R::validate($shop, F::package('boost_produit'), [F::product($shop)->id], today(), 7))->toBe([]);
});

test('seule une boutique active peut commander', function () {
    $shop = F::shop(approved: false);
    expect(R::validate($shop, F::package('boutique_une'), [], today(), 7))
        ->toBe(['Ta boutique doit être active pour commander une campagne.']);
});

test('5 campagnes actives au maximum', function () {
    $shop = F::shop();
    foreach (range(1, 5) as $i) {
        F::campaign($shop, 'boutique_une', [], ['status' => C::STATUS_EN_COURS, 'starts_on' => today()->addDays(10 * $i)]);
    }
    expect(R::validate($shop, F::package('boost_produit'), [F::product($shop)->id], today(), 7))
        ->toContain("Tu as déjà 5 campagnes actives : attends la fin de l'une d'elles.");
});

test('une campagne à payer ou terminée ne compte pas dans la limite', function () {
    $shop = F::shop();
    foreach (range(1, 3) as $i) {
        F::campaign($shop, 'boutique_une', [], ['status' => C::STATUS_TERMINEE, 'starts_on' => today()->subDays(40 + $i * 8)]);
        F::campaign($shop, 'boutique_une', [], ['status' => C::STATUS_ANNULEE]);
    }
    expect(R::validate($shop, F::package('boost_produit'), [F::product($shop)->id], today(), 7))->toBe([]);
});

test('un produit ne peut pas être dans deux mises en avant en même temps', function () {
    $shop = F::shop();
    $product = F::product($shop, ['name' => 'Montre verte']);
    F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS, 'starts_on' => today()]);

    $errors = R::validate($shop, F::package('top_categorie'), [$product->id], today()->addDays(3), 7);
    expect($errors[0])->toStartWith('« Montre verte » est déjà mis en avant du');

    // Après la fin de la première, c'est possible
    expect(R::validate($shop, F::package('top_categorie'), [$product->id], today()->addDays(7), 7))->toBe([]);
});

test('produit en rupture, désactivé ou d\'une autre boutique refusé', function () {
    $shop = F::shop();
    $other = F::shop();
    $out = F::product($shop, ['name' => 'Épuisé', 'stock' => 0]);
    $off = F::product($shop, ['name' => 'Caché', 'is_active' => false]);
    $foreign = F::product($other);

    expect(R::validate($shop, F::package('top_categorie'), [$out->id, $off->id, $foreign->id], today(), 7))->toBe([
        '« Épuisé » est en rupture de stock ou désactivé.',
        '« Caché » est en rupture de stock ou désactivé.',
        "Ce produit n'appartient pas à ta boutique.",
    ]);
});

test('un produit sans stock mais avec une variante disponible peut être promu', function () {
    $shop = F::shop();
    $product = F::product($shop, ['stock' => 0]);
    ProductVariant::create(['product_id' => $product->id, 'name' => 'Taille M', 'stock' => 4, 'is_active' => true]);

    expect(R::isPromotable($product->fresh()))->toBeTrue();
});

test('nombre de produits selon la formule', function () {
    $shop = F::shop();
    $ids = collect(range(1, 4))->map(fn () => F::product($shop)->id)->all();

    expect(R::validate($shop, F::package('top_categorie'), $ids, today(), 7))->toContain('La formule « Top catégorie » promeut au plus 3 produits.')
        ->and(R::validate($shop, F::package('boost_produit'), [], today(), 7))->toContain('Choisis au moins un produit à promouvoir.')
        ->and(R::validate($shop, F::package('boutique_une'), [$ids[0]], today(), 7))->toContain('La formule « Boutique à la une » promeut toute la boutique, sans choix de produits.');
});

test('réseaux sociaux : début au plus tôt dans 2 jours', function () {
    $shop = F::shop();
    $package = F::package('social_starter');

    expect(R::validate($shop, $package, [], today()->addDay(), 7)[0])->toStartWith('Une campagne réseaux sociaux commence au plus tôt le')
        ->and(R::validate($shop, $package, [], today()->addDays(2), 7))->toBe([]);
});

test('mise en avant : pas de date de début passée', function () {
    $shop = F::shop();
    expect(R::validate($shop, F::package('boutique_une'), [], today()->subDay(), 7))->toBe(['La date de début ne peut pas être dans le passé.']);
});

test('formule complète : message et prochaine date libre', function () {
    $package = F::package('boutique_une');                       // 6 places au total
    foreach (range(1, 6) as $i) {
        F::campaign(F::shop(), 'boutique_une', [], ['status' => C::STATUS_PROGRAMMEE, 'starts_on' => today()->addDays($i - 1)]);
    }
    // Les 6 campagnes se chevauchent du jour 5 au jour 6 ; la première libère sa place après le jour 6.

    $errors = R::validate(F::shop(), $package, [], today()->addDays(2), 7);
    expect($errors)->toBe([
        'La formule « Boutique à la une » est complète sur ces dates. Prochaine date libre : ' . today()->addDays(7)->format('d/m/Y') . '.',
    ]);
});

test('Top catégorie : les places se comptent par catégorie', function () {
    foreach (range(1, 4) as $i) {
        $s = F::shop();
        F::campaign($s, 'top_categorie', [F::product($s, ['category' => 'Bijoux & Montres'])->id], ['status' => C::STATUS_EN_COURS]);
    }
    $shop = F::shop();
    $bijou = F::product($shop, ['category' => 'Bijoux & Montres']);
    $robe = F::product($shop, ['category' => 'Vêtements & Mode']);

    expect(R::validate($shop, F::package('top_categorie'), [$bijou->id], today(), 7)[0])->toContain('est complète sur ces dates')
        ->and(R::validate($shop, F::package('top_categorie'), [$robe->id], today(), 7))->toBe([]);
});
