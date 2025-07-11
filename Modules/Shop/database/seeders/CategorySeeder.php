<?php

namespace Modules\Shop\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Shop\app\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Create records with proper relationships
        Category::factory()
            ->count(10)
            ->create();
    }
}