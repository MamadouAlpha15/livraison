<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPage;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductPage> */
class ProductPageFactory extends Factory
{
    protected $model = ProductPage::class;

    /** Page minimale : la section principale « Produit » avec un titre, un prix et le formulaire de commande */
    public static function sampleData(): array
    {
        return [
            'sections' => [
                'main' => [
                    'type'        => 'main-product',
                    'settings'    => [],
                    'blocks'      => [
                        'title' => ['type' => 'title', 'settings' => []],
                        'price' => ['type' => 'price', 'settings' => []],
                        'order' => ['type' => 'order_form', 'settings' => []],
                    ],
                    'block_order' => ['title', 'price', 'order'],
                ],
            ],
            'order' => ['main'],
        ];
    }

    public function definition(): array
    {
        return [
            'product_id' => function () {
                $owner = User::factory()->create(['role' => 'admin']);
                $shop = Shop::forceCreate(['user_id' => $owner->id, 'name' => 'Boutique ' . $owner->id, 'email' => "pb{$owner->id}@test.invalid", 'is_approved' => true]);
                $owner->forceFill(['shop_id' => $shop->id])->save();
                return Product::forceCreate(['shop_id' => $shop->id, 'name' => 'Produit ' . $owner->id, 'price' => 100000, 'stock' => 10, 'is_active' => true])->id;
            },
            'shop_id' => fn (array $attributes) => Product::find($attributes['product_id'])->shop_id,
            'draft'   => self::sampleData(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'published'    => $attributes['draft'] ?? self::sampleData(),
            'published_at' => now(),
            'is_enabled'   => true,
            'version'      => 1,
        ]);
    }
}
