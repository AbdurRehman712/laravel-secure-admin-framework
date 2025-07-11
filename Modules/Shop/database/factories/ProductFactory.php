<?php

namespace Modules\Shop\database\factories;

use Modules\Shop\app\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->text(),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'sale_price' => $this->faker->randomFloat(2, 10, 1000),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'weight' => $this->faker->randomFloat(2, 1, 100),
            'dimensions' => $this->faker->words(2, true),
            'featured_image' => $this->faker->imageUrl(640, 480, 'business', true),
            'gallery' => $this->faker->imageUrl(640, 480, 'business', true),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'featured' => $this->faker->boolean(80),
            'meta_title' => $this->faker->words(2, true),
            'meta_description' => $this->faker->text(),
            'category_id' => \Modules\Shop\app\Models\Category::factory()
        ];
    }
}