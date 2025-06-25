<?php

namespace Modules\ModuleBuilder\Console\Commands;

use Illuminate\Console\Command;
use Modules\ModuleBuilder\Models\ModuleProject;
use Modules\ModuleBuilder\Models\ModuleTable;
use Modules\ModuleBuilder\Models\ModuleField;
use Modules\ModuleBuilder\Services\ModuleGeneratorService;

class TestModuleBuild extends Command
{
    protected $signature = 'module-builder:test';
    protected $description = 'Test module generation with sample data';

    public function handle()
    {
        $this->info('Creating test module project...');
        
        // Delete existing ShopModule if it exists
        $existingProject = ModuleProject::where('name', 'ShopModule')->first();
        if ($existingProject) {
            // Clean up files
            $modulePath = base_path("Modules/ShopModule");
            if (is_dir($modulePath)) {
                \Illuminate\Support\Facades\File::deleteDirectory($modulePath);
            }
            
            // Force delete all related records
            \DB::table('module_fields')->whereIn('table_id', function($query) use ($existingProject) {
                $query->select('id')->from('module_tables')->where('project_id', $existingProject->id);
            })->delete();
            
            \DB::table('module_relationships')->whereIn('from_table_id', function($query) use ($existingProject) {
                $query->select('id')->from('module_tables')->where('project_id', $existingProject->id);
            })->delete();
            
            \DB::table('module_permissions')->where('project_id', $existingProject->id)->delete();
            \DB::table('module_components')->where('project_id', $existingProject->id)->delete();
            \DB::table('module_tables')->where('project_id', $existingProject->id)->delete();
            \DB::table('module_projects')->where('id', $existingProject->id)->delete();
            
            $this->info('Force deleted existing ShopModule project and all related records');
        }
        
        // Create test project
        $project = ModuleProject::create([
            'name' => 'ShopModule',
            'slug' => 'shop-module',
            'description' => 'A e-commerce shop module',
            'namespace' => '',
            'version' => '1.0.0',
            'has_api' => true,
            'api_prefix' => 'shop',
            'enabled' => true,
            'status' => 'draft'
        ]);

        $this->info("Created project: {$project->name}");

        // Create Products table
        $table = ModuleTable::create([
            'project_id' => $project->id,
            'name' => 'products',
            'model_name' => 'Product',
            'display_name' => 'Products',
            'has_timestamps' => true,
            'has_soft_deletes' => true
        ]);

        $this->info("Created table: {$table->name}");

        // Create fields
        $fields = [
            ['name' => 'name', 'type' => 'string', 'length' => 255, 'nullable' => false, 'display_name' => 'Product Name'],
            ['name' => 'slug', 'type' => 'string', 'length' => 255, 'nullable' => false, 'display_name' => 'Slug', 'unique' => true],
            ['name' => 'description', 'type' => 'text', 'nullable' => true, 'display_name' => 'Description'],
            ['name' => 'price', 'type' => 'decimal', 'nullable' => false, 'display_name' => 'Price'],
            ['name' => 'sku', 'type' => 'string', 'length' => 100, 'nullable' => true, 'display_name' => 'SKU', 'unique' => true],
            ['name' => 'enabled', 'type' => 'boolean', 'nullable' => false, 'default_value' => 'true', 'display_name' => 'Enabled'],
            ['name' => 'category_id', 'type' => 'integer', 'nullable' => true, 'display_name' => 'Category ID'],
        ];

        foreach ($fields as $fieldData) {
            $field = ModuleField::create(array_merge($fieldData, [
                'table_id' => $table->id,
                'table_column' => true,
                'table_searchable' => in_array($fieldData['name'], ['name', 'slug', 'sku']),
                'table_sortable' => in_array($fieldData['name'], ['name', 'price']),
                'form_component' => null // Let service auto-detect
            ]));
            
            $this->info("Created field: {$field->name} ({$field->type})");
        }

        // Generate the module
        $this->info('Building module...');
        $generator = new ModuleGeneratorService($project);
        $result = $generator->generateModule();

        if ($result['success']) {
            $this->info('✅ Module generated successfully!');
            $this->info("Generated files:");
            foreach ($result['files'] as $file) {
                $this->line("  - {$file}");
            }
            
            // Test export functionality
            $this->info('Testing export functionality...');
            $zipPath = $generator->exportModule();
            if (file_exists($zipPath)) {
                $size = round(filesize($zipPath) / 1024, 2);
                $this->info("✅ Export successful: {$zipPath} ({$size} KB)");
            } else {
                $this->error("❌ Export failed: File not created");
            }
        } else {
            $this->error("❌ Error generating module: {$result['message']}");
        }

        return 0;
    }
}
