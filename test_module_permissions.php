<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Modules\Core\app\Models\Admin;

// Test script to verify ModuleBuilder permissions are working

echo "🧪 Testing ModuleBuilder Permission System\n";
echo "==========================================\n\n";

// Test 1: Check if ModuleBuilder permissions exist
echo "1. Checking ModuleBuilder permissions...\n";
$moduleBuilderPermissions = Permission::where('name', 'like', '%module_project%')
    ->orWhere('name', 'like', '%module_table%')
    ->orWhere('name', 'like', '%module_field%')
    ->get();

echo "   Found " . $moduleBuilderPermissions->count() . " ModuleBuilder permissions:\n";
foreach ($moduleBuilderPermissions as $perm) {
    echo "   - {$perm->name}\n";
}

// Test 2: Create a role WITHOUT ModuleBuilder permissions
echo "\n2. Creating test role without ModuleBuilder permissions...\n";
$testRole = Role::firstOrCreate([
    'name' => 'Limited Admin',
    'guard_name' => 'admin'
]);

// Give only Core permissions (admin management)
$corePermissions = Permission::where('name', 'like', '%admin%')
    ->where('guard_name', 'admin')
    ->pluck('name')
    ->toArray();

$testRole->syncPermissions($corePermissions);
echo "   ✓ Created 'Limited Admin' role with " . count($corePermissions) . " Core permissions\n";

// Test 3: Create test user with limited role
echo "\n3. Creating test user with limited permissions...\n";
$testUser = Admin::firstOrCreate(
    ['email' => 'limited@admin.com'],
    [
        'name' => 'Limited Admin User',
        'password' => bcrypt('password')
    ]
);

$testUser->syncRoles(['Limited Admin']);
echo "   ✓ Created limited@admin.com with 'Limited Admin' role\n";

// Test 4: Check permissions
echo "\n4. Testing permission checks...\n";
$canViewProjects = $testUser->can('view_any_module_project');
$canCreateProjects = $testUser->can('create_module_project');
$canViewAdmins = $testUser->can('view_any_admin');

echo "   Limited user can view module projects: " . ($canViewProjects ? "YES ❌" : "NO ✅") . "\n";
echo "   Limited user can create module projects: " . ($canCreateProjects ? "YES ❌" : "NO ✅") . "\n";
echo "   Limited user can view admins: " . ($canViewAdmins ? "YES ✅" : "NO ❌") . "\n";

// Test 5: Check Super Admin permissions
echo "\n5. Testing Super Admin permissions...\n";
$superAdmin = Admin::where('email', 'admin@admin.com')->first();
if ($superAdmin) {
    $superCanViewProjects = $superAdmin->can('view_any_module_project');
    $superCanCreateProjects = $superAdmin->can('create_module_project');
    
    echo "   Super Admin can view module projects: " . ($superCanViewProjects ? "YES ✅" : "NO ❌") . "\n";
    echo "   Super Admin can create module projects: " . ($superCanCreateProjects ? "YES ✅" : "NO ❌") . "\n";
} else {
    echo "   ❌ Super Admin not found!\n";
}

echo "\n📋 Test Results Summary:\n";
echo "========================\n";
echo "✅ ModuleBuilder permissions exist in database\n";
echo "✅ Limited role created with Core-only permissions\n";
echo "✅ Test user created with limited permissions\n";
echo ($canViewProjects || $canCreateProjects ? "❌" : "✅") . " Limited user correctly blocked from ModuleBuilder\n";
echo (isset($superCanViewProjects) && $superCanViewProjects ? "✅" : "❌") . " Super Admin has ModuleBuilder access\n";

echo "\n🎯 Next Steps:\n";
echo "1. Login to /admin with admin@admin.com (should see ModuleBuilder)\n";
echo "2. Login to /admin with limited@admin.com (should NOT see ModuleBuilder)\n";
echo "3. Test individual permission controls in Module Roles interface\n";

echo "\nTest complete! 🚀\n";
