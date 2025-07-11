<?php

namespace Modules\Shop\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Shop\app\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create records with proper relationships
        Product::factory()
            ->count(10)
            ->create();
    }
}