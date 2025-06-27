<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;

echo "🧹 Cleaning up UnifiedModuleBuilder permissions...\n";

$permissions = Permission::where('name', 'like', '%unified_module_builder%')->get();

echo "Found " . $permissions->count() . " permissions to delete:\n";

foreach ($permissions as $perm) {
    echo "- {$perm->name}\n";
    $perm->delete();
}

echo "\n✅ Cleanup complete!\n";
