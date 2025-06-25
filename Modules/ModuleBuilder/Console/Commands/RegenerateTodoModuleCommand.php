<?php

namespace Modules\ModuleBuilder\Console\Commands;

use Illuminate\Console\Command;
use Modules\ModuleBuilder\Models\ModuleProject;
use Modules\ModuleBuilder\Services\ModuleGeneratorService;

class RegenerateTodoModuleCommand extends Command
{
    protected $signature = 'module:regenerate-todo';
    protected $description = 'Regenerate TodoModule with proper Filament resources using Module Builder';

    public function handle()
    {
        $this->info('🚀 Regenerating TodoModule with Module Builder...');

        // Check if TodoModule project exists in database
        $todoProject = ModuleProject::where('name', 'TodoModule')->first();
        
        if (!$todoProject) {
            // Create TodoModule project record
            $todoProject = ModuleProject::create([
                'name' => 'TodoModule',
                'namespace' => 'Modules\\TodoModule',
                'description' => 'A comprehensive Todo management module with task tracking, priorities, and categories',
                'author_name' => 'Module Builder',
                'author_email' => 'builder@example.com',
                'version' => '1.0.0',
                'enabled' => true,
                'has_admin_panel' => true,
                'has_api' => false,
                'has_web_routes' => false,
                'has_frontend' => false,
                'has_permissions' => true,
                'has_middleware' => false,
                'has_commands' => false,
                'status' => 'draft'
            ]);
            
            $this->info('✅ Created TodoModule project record');
        } else {
            $this->info('📋 Found existing TodoModule project record');
        }

        // Generate the module using the enhanced generator
        try {
            $generator = new ModuleGeneratorService($todoProject);
            $result = $generator->generateModule();
            
            if ($result['success']) {
                $this->info('✅ TodoModule regenerated successfully!');
                $this->info('📁 Generated files:');
                foreach ($result['files'] as $file) {
                    $this->line("   - {$file}");
                }
                
                // Run migrations
                $this->info('🔄 Running migrations...');
                $this->call('migrate');
                
                // Clear caches
                $this->info('🧹 Clearing caches...');
                $this->call('cache:clear');
                $this->call('config:clear');
                $this->call('route:clear');
                
                $this->info('🎉 TodoModule is now ready and should appear in the sidebar!');
                $this->info('🌐 You can access it at: /admin/todos');
                
            } else {
                $this->error('❌ Failed to regenerate TodoModule: ' . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error regenerating TodoModule: ' . $e->getMessage());
        }
    }
}
