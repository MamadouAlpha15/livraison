<?php

namespace Database\Factories;

use App\Models\PageTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PageTemplate> */
class PageTemplateFactory extends Factory
{
    protected $model = PageTemplate::class;

    public function definition(): array
    {
        return [
            'shop_id'  => null,
            'name'     => 'Modèle ' . fake()->unique()->word(),
            'category' => 'general',
            'data'     => ProductPageFactory::sampleData(),
        ];
    }
}
