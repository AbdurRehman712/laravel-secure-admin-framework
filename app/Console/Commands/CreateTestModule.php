<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\ModuleBuilder\Models\ModuleProject;
use Modules\ModuleBuilder\Models\ModuleTable;
use Modules\ModuleBuilder\Models\ModuleField;
use Modules\ModuleBuilder\Services\ModuleGeneratorService;

class CreateTestModule extends Command
{
    protected $signature = 'test:create-module {name=TestModule}';
    protected $description = 'Create a test module to verify module builder functionality';

    public function handle()
    {
        $moduleName = $this->argument('name');
        
        $this->info("Creating test module: {$moduleName}");
        
        // Create module project
        $project = ModuleProject::create([
            'name' => $moduleName,
            'slug' => strtolower($moduleName),
            'description' => 'A test module created to verify module builder functionality',
            'version' => '1.0.0',
            'author' => 'Module Builder Test',
            'namespace' => "Modules\\{$moduleName}",
            'enabled' => true,
            'providers' => ["Modules\\{$moduleName}\\app\\Providers\\{$moduleName}ServiceProvider"],
            'type' => 'module',
            'priority' => 0
        ]);
        
        $this->info("✅ Module project created with ID: {$project->id}");
        
        // Create a test table
        $table = ModuleTable::create([
            'project_id' => $project->id,
            'name' => 'test_items',
            'display_name' => 'Test Items',
            'description' => 'A test table for items'
        ]);
        
        $this->info("✅ Table 'test_items' created");
        
        // Add some test fields
        $fields = [
            [
                'name' => 'title',
                'type' => 'string',
                'length' => 255,
                'nullable' => false,
                'description' => 'Item title'
            ],
            [
                'name' => 'description',
                'type' => 'text',
                'nullable' => true,
                'description' => 'Item description'
            ],
            [
                'name' => 'price',
                'type' => 'decimal',
                'precision' => 8,
                'scale' => 2,
                'nullable' => true,
                'description' => 'Item price'
            ],
            [
                'name' => 'is_active',
                'type' => 'boolean',
                'default' => true,
                'nullable' => false,
                'description' => 'Is item active'
            ]
        ];
        
        foreach ($fields as $fieldData) {
            ModuleField::create([
                'table_id' => $table->id,
                'name' => $fieldData['name'],
                'type' => $fieldData['type'],
                'length' => $fieldData['length'] ?? null,
                'precision' => $fieldData['precision'] ?? null,
                'scale' => $fieldData['scale'] ?? null,
                'nullable' => $fieldData['nullable'] ?? false,
                'default' => $fieldData['default'] ?? null,
                'description' => $fieldData['description'] ?? null
            ]);
        }
        
        $this->info("✅ Added " . count($fields) . " fields to the table");
        
        // Generate the module
        try {
            $generator = new ModuleGeneratorService($project);
            $result = $generator->generateModule();
            
            if ($result['success']) {
                $project->update(['status' => 'built']);
                $this->info("✅ Module generated successfully!");
                $this->info("📁 Module location: Modules/{$moduleName}");
                
                // Show generated files
                $modulePath = base_path("Modules/{$moduleName}");
                if (is_dir($modulePath)) {
                    $this->info("\n📄 Generated files:");
                    $this->showDirectoryContents($modulePath, 0, 2);
                }
            } else {
                $this->error("❌ Module generation failed: " . ($result['message'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Module generation failed: " . $e->getMessage());
            return 1;
        }
        
        $this->info("\n🎉 Test module creation completed successfully!");
        $this->info("You can now:");
        $this->info("1. Visit /admin/module-projects to see the module in the admin panel");
        $this->info("2. Check the generated files in Modules/{$moduleName}");
        $this->info("3. Run migrations: php artisan migrate");
        
        return 0;
    }
    
    private function showDirectoryContents($path, $level = 0, $maxLevel = 2)
    {
        if ($level > $maxLevel) return;
        
        $indent = str_repeat('  ', $level);
        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($itemPath)) {
                $this->line($indent . "📁 {$item}/");
                $this->showDirectoryContents($itemPath, $level + 1, $maxLevel);
            } else {
                $this->line($indent . "📄 {$item}");
            }
        }
    }
}
