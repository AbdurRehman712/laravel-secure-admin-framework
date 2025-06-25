<?php

require_once 'bootstrap/app.php';

$app = Illuminate\Foundation\Application::getInstance();
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\ModuleBuilder\Models\ModuleProject;

$todo = ModuleProject::create([
    'name' => 'TodoModule',
    'namespace' => 'Modules\\TodoModule',
    'description' => 'A comprehensive todo management system with tasks, categories, and user assignments',
    'author_name' => 'Module Builder Pro',
    'author_email' => 'admin@example.com',
    'version' => '1.0.0',
    'enabled' => true,
    'status' => 'draft',
    'has_admin_panel' => true,
    'has_api' => true,
    'has_web_routes' => false,
    'has_permissions' => true,
    'has_middleware' => false,
    'has_commands' => false,
    'has_events' => false,
    'has_jobs' => false,
    'has_mail' => false,
    'has_notifications' => false
]);

echo "Todo module created with ID: " . $todo->id . "\n";
echo "Name: " . $todo->name . "\n";
echo "Status: " . $todo->status . "\n";
echo "Enabled: " . ($todo->enabled ? 'Yes' : 'No') . "\n";
