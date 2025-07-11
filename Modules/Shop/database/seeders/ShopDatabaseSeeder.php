<?php

namespace Modules\Shop\database\seeders;

use Illuminate\Database\Seeder;

class ShopDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(OrderSeeder::class);
    }
}