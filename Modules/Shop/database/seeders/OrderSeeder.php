<?php

namespace Modules\Shop\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Shop\app\Models\Order;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Create records with proper relationships
        Order::factory()
            ->count(10)
            ->create();
    }
}