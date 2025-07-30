<?php

namespace Modules\ERDDesigner\database\seeders;

use Illuminate\Database\Seeder;

class ERDDesignerDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ERDDesignerPermissionsSeeder::class,
            ERDDesignerDemoDataSeeder::class,
        ]);
    }
}
