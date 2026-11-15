<?php

use App\PageBuilder\HtmlSanitizer;
use App\PageBuilder\PageFactory;
use App\PageBuilder\PageValidator;
use App\PageBuilder\Registry;
use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\Color;
use App\PageBuilder\Settings\Image;
use App\PageBuilder\Settings\ProductPicker;
use App\PageBuilder\Settings\Range;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Video;
use App\PageBuilder\ValidationContext;
use Database\Factories\ProductPageFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\Support\CampaignFixtures as F;

/** Erreurs de validation d'une page, par emplacement (tableau vide si la page passe) */
function pageErrors(array $page, ?ValidationContext $ctx = null): array
{
    $ctx ??= new ValidationContext(F::shop());
    try {
        (new PageValidator($ctx))->validate($page);
        return [];
    } catch (ValidationException $e) {
        return $e->errors();
    }
}

/** Un réglage seul accepte-t-il cette valeur ? */
function settingAccepts($setting, mixed $value, ValidationContext $ctx): bool
{
    return Validator::make(['v' => $value], ['v' => $setting->rules($ctx)])->passes();
}

test('le registre découvre les sections et les blocs, et range la bibliothèque par catégorie', function () {
    expect(Registry::section('main-product'))->toBeInstanceOf(SectionType::class)
        ->and(Registry::section('main-product')->isStatic())->toBeTrue()
        ->and(Registry::section('rich-text')->category())->toBe('contenu')
        ->and(array_keys(Registry::blocks()))->toContain('title', 'price', 'text')
        ->and(Registry::section('inconnue'))->toBeNull();

    // La section fixe n'est pas proposée dans « + Ajouter une section »
    $library = Registry::library();
    $types = collect($library)->flatMap(fn ($cat) => array_column($cat['sections'], 'type'))->all();
    expect($types)->toContain('rich-text')->not->toContain('main-product')
        ->and(collect($library)->firstWhere('key', 'contenu')['label'])->toBe('Contenu');

    // Le schéma envoyé à l'éditeur décrit chaque réglage, réglages communs compris
    $schema = Registry::section('rich-text')->schema();
    expect(array_column($schema['settings'], 'id'))->toContain('heading', 'content', 'color_scheme', 'padding_top_mobile', 'padding_bottom_desktop')
        ->and(collect($schema['settings'])->firstWhere('id', 'padding_top_mobile'))->toMatchArray(['type' => 'range', 'min' => 0, 'max' => 120, 'step' => 4, 'default' => 24]);
});

test('valeurs par défaut et préréglages', function () {
    $section = PageFactory::section('rich-text', 1);
    expect($section['settings']['color_scheme'])->toBe('nuit')
        ->and($section['settings']['padding_top_desktop'])->toBe(40)
        ->and($section['settings']['align'])->toBe('center');

    $main = PageFactory::section('main-product');
    expect($main['block_order'])->toHaveCount(11)
        ->and(array_column($main['blocks'], 'type'))->toContain('title', 'price', 'order_form', 'delivery')
        ->and($main['blocks'][$main['block_order'][2]]['settings']['show_compare'])->toBeTrue();

    [$page, $id] = PageFactory::addSection(PageFactory::blank(), 'rich-text', 0, 0);
    expect($page['order'])->toBe([$id, 'main'])
        ->and(pageErrors($page))->toBe([]);
});

test('une page correcte passe et ressort complétée', function () {
    $out = (new PageValidator(new ValidationContext(F::shop())))->validate(ProductPageFactory::sampleData());

    expect($out['order'])->toBe(['main'])
        ->and($out['sections']['main']['settings'])->toMatchArray(['gallery_layout' => 'thumbnails', 'color_scheme' => 'clair', 'padding_top_mobile' => 24])
        ->and($out['sections']['main']['blocks']['price']['settings'])->toBe(['show_compare' => true, 'show_saving' => true])
        ->and($out['sections']['main']['disabled'])->toBeFalse();
});

test('réglages inconnus retirés, valeurs vides remplacées par les valeurs par défaut, curseur ramené au pas', function () {
    $page = ProductPageFactory::sampleData();
    $page['sections']['main']['settings'] = ['pirate' => 'x', 'gallery_layout' => '', 'padding_top_mobile' => 27];
    $page['sections']['main']['blocks']['title']['settings'] = ['size' => 'xlarge', 'onclick' => 'alert(1)'];

    $out = (new PageValidator(new ValidationContext(F::shop())))->validate($page);
    $settings = $out['sections']['main']['settings'];

    expect($settings)->not->toHaveKey('pirate')
        ->and($settings['gallery_layout'])->toBe('thumbnails')
        ->and($settings['padding_top_mobile'])->toBe(28)
        ->and($out['sections']['main']['blocks']['title']['settings'])->toBe(['size' => 'xlarge']);
});

test('structure refusée : type inconnu, bloc non accepté, limite de bloc, ordre incohérent', function () {
    $page = ProductPageFactory::sampleData();
    $page['sections']['x'] = ['type' => 'carrousel-magique', 'settings' => []];
    $page['order'][] = 'x';
    expect(pageErrors($page))->toHaveKey('sections.x.type');

    $page = ProductPageFactory::sampleData();
    $page['sections']['main']['blocks']['bad'] = ['type' => 'video', 'settings' => []];
    $page['sections']['main']['block_order'][] = 'bad';
    expect(pageErrors($page))->toHaveKey('sections.main.blocks.bad.type');

    // Le titre ne peut apparaître qu'une fois
    $page = ProductPageFactory::sampleData();
    $page['sections']['main']['blocks']['title2'] = ['type' => 'title', 'settings' => []];
    $page['sections']['main']['block_order'][] = 'title2';
    expect(pageErrors($page)['sections.main.blocks.title2.type'][0])->toContain('qu\'une seule fois');

    // Un bloc absent de l'ordre, un identifiant invalide
    $page = ProductPageFactory::sampleData();
    $page['sections']['main']['block_order'] = ['title'];
    expect(pageErrors($page))->toHaveKey('sections.main.block_order');

    $page = ProductPageFactory::sampleData();
    $page['sections']['Main Section!'] = $page['sections']['main'];
    unset($page['sections']['main']);
    $page['order'] = ['Main Section!'];
    expect(pageErrors($page))->toHaveKey('sections.Main Section!');

    expect(pageErrors(['sections' => 'nope']))->toHaveKey('page')
        ->and(pageErrors(['sections' => ['main' => 'nope'], 'order' => ['main']]))->toHaveKey('sections.main');
});

test('trop de blocs ou de sections refusés', function () {
    $page = ProductPageFactory::sampleData();
    for ($i = 0; $i < 30; $i++) {
        $page['sections']['main']['blocks']["t{$i}"] = ['type' => 'text', 'settings' => []];
        $page['sections']['main']['block_order'][] = "t{$i}";
    }
    expect(pageErrors($page))->toHaveKey('sections.main.blocks');

    $page = ProductPageFactory::sampleData();
    for ($i = 0; $i < PageValidator::MAX_SECTIONS; $i++) {
        $page['sections']["r{$i}"] = PageFactory::section('rich-text');
        $page['order'][] = "r{$i}";
    }
    expect(pageErrors($page)['page'][0])->toContain('40 sections');
});

test('la section Produit est obligatoire, unique et toujours visible', function () {
    expect(pageErrors(['sections' => ['r' => PageFactory::section('rich-text')], 'order' => ['r']])['page'][0])
        ->toContain('« Produit » est obligatoire');

    $page = ProductPageFactory::sampleData();
    $page['sections']['main2'] = $page['sections']['main'];
    $page['order'][] = 'main2';
    expect(pageErrors($page)['page'][0])->toContain('qu\'une seule fois');

    $page = ProductPageFactory::sampleData();
    $page['sections']['main']['disabled'] = true;
    expect(pageErrors($page))->toHaveKey('sections.main.disabled');
});

test('valeurs refusées : curseur hors limites, choix hors liste, couleur invalide', function () {
    $page = ProductPageFactory::sampleData();
    $page['sections']['main']['settings'] = ['padding_top_mobile' => 500, 'gallery_layout' => 'mosaique', 'color_scheme' => 'fluo'];
    $errors = pageErrors($page);

    expect($errors)->toHaveKeys([
        'sections.main.settings.padding_top_mobile',
        'sections.main.settings.gallery_layout',
        'sections.main.settings.color_scheme',
    ])->and($errors['sections.main.settings.padding_top_mobile'][0])->toStartWith('Marge en haut · mobile');

    $ctx = new ValidationContext(F::shop());
    $color = new Color('c', 'Couleur', '#1F7CF6');
    $range = new Range('r', 'Taille', 10, null, 0, 50, 5);
    $select = new Select('s', 'Choix', 'a', null, ['a' => 'A', 'b' => 'B']);
    expect(settingAccepts($color, '#0b1e3d', $ctx))->toBeTrue()
        ->and(settingAccepts($color, 'red', $ctx))->toBeFalse()
        ->and(settingAccepts($color, '#FFF', $ctx))->toBeFalse()
        ->and(settingAccepts($range, 55, $ctx))->toBeFalse()
        ->and(settingAccepts($range, 'abc', $ctx))->toBeFalse()
        ->and(settingAccepts($select, 'c', $ctx))->toBeFalse()
        ->and($color->normalize('#0b1e3d', $ctx))->toBe('#0B1E3D');
});

test('produit et image : seulement ceux de la boutique du vendeur', function () {
    $shop = F::shop();
    $mine = F::product($shop);
    $offline = F::product($shop, ['is_active' => false]);
    $other = F::product(F::shop());
    $ctx = new ValidationContext($shop);

    $picker = new ProductPicker('p', 'Produit');
    expect(settingAccepts($picker, $mine->id, $ctx))->toBeTrue()
        ->and(settingAccepts($picker, $other->id, $ctx))->toBeFalse()
        ->and(settingAccepts($picker, $offline->id, $ctx))->toBeFalse();

    $image = new Image('i', 'Image');
    $file = 'medium/' . str_repeat('a1B2', 8) . '.webp';
    expect(settingAccepts($image, "page-builder/{$shop->id}/{$file}", $ctx))->toBeTrue()
        ->and(settingAccepts($image, "page-builder/{$other->shop_id}/{$file}", $ctx))->toBeFalse()
        ->and(settingAccepts($image, "page-builder/{$shop->id}/../secrets.webp", $ctx))->toBeFalse()
        ->and(settingAccepts($image, 'https://exemple.com/photo.webp', $ctx))->toBeFalse();
});

test('vidéo : MP4 de la boutique, YouTube ou TikTok seulement', function () {
    $shop = F::shop();
    $ctx = new ValidationContext($shop);
    $video = new Video('v', 'Vidéo');

    expect(settingAccepts($video, "page-builder/{$shop->id}/videos/" . str_repeat('x', 24) . '.mp4', $ctx))->toBeTrue()
        ->and(settingAccepts($video, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', $ctx))->toBeTrue()
        ->and(settingAccepts($video, 'https://youtu.be/dQw4w9WgXcQ', $ctx))->toBeTrue()
        ->and(settingAccepts($video, 'https://www.youtube.com/shorts/abcDEF123', $ctx))->toBeTrue()
        ->and(settingAccepts($video, 'https://www.tiktok.com/@shopio.gn/video/7301234567890123456', $ctx))->toBeTrue()
        ->and(settingAccepts($video, 'https://vimeo.com/123456', $ctx))->toBeFalse()
        ->and(settingAccepts($video, 'http://www.youtube.com/watch?v=dQw4w9WgXcQ', $ctx))->toBeFalse()
        ->and(settingAccepts($video, 'https://youtube.com.evil.com/watch?v=dQw4w9WgXcQ', $ctx))->toBeFalse()
        ->and(settingAccepts($video, 'page-builder/999999/videos/' . str_repeat('x', 24) . '.mp4', $ctx))->toBeFalse();
});

test('texte riche nettoyé : ni script, ni gestionnaire d\'évènement, ni lien javascript', function () {
    $dirty = '<p onclick="steal()">Bonjour <strong>Conakry</strong></p><script>alert(1)</script>'
        . '<img src=x onerror="alert(1)"><a href="javascript:alert(1)">piège</a> <a href="https://shopio.gn">ok</a>'
        . '<iframe src="https://evil.test"></iframe><h4 style="color:red">Titre</h4>';
    $clean = HtmlSanitizer::clean($dirty);

    expect($clean)->not->toContain('<script')->not->toContain('onclick')->not->toContain('onerror')
        ->not->toContain('javascript:')->not->toContain('<iframe')->not->toContain('<img')->not->toContain('style=')
        ->toContain('<strong>Conakry</strong>')->toContain('href="https://shopio.gn"')->toContain('<h4>Titre</h4>');

    // Même nettoyage quand la page entière passe par le validateur
    $page = ProductPageFactory::sampleData();
    $page['sections']['r'] = PageFactory::section('rich-text');
    $page['sections']['r']['settings']['content'] = $dirty;
    $page['sections']['r']['settings']['heading'] = '<b>Titre</b><script>x</script>';
    $page['order'][] = 'r';

    $out = (new PageValidator(new ValidationContext(F::shop())))->validate($page);
    expect($out['sections']['r']['settings']['content'])->toBe($clean)
        ->and($out['sections']['r']['settings']['heading'])->toBe('Titrex');
});
