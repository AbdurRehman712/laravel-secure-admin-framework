<?php

namespace Modules\Shop\database\factories;

use Modules\Shop\app\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'image' => $this->faker->imageUrl(640, 480, 'business', true),
            'active' => $this->faker->boolean(80),
            'sort_order' => $this->faker->numberBetween(1, 1000)
        ];
    }
}