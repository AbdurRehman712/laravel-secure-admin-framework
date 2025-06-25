<?php

namespace Modules\ModuleBuilder\Console\Commands;

use Illuminate\Console\Command;
use Modules\ModuleBuilder\Models\ModuleProject;

class CreateTodoModuleCommand extends Command
{
    protected $signature = 'module:create-todo';
    protected $description = 'Create a sample Todo module for testing';

    public function handle()
    {
        $todo = ModuleProject::create([
            'name' => 'TodoModule',
            'namespace' => 'Modules\\TodoModule',
            'description' => 'A comprehensive todo management system with tasks, categories, and priority levels',
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

        $this->info("✅ Todo module created successfully!");
        $this->line("📁 Module ID: " . $todo->id);
        $this->line("📝 Name: " . $todo->name);
        $this->line("🔧 Status: " . $todo->status);
        $this->line("⚡ Enabled: " . ($todo->enabled ? 'Yes' : 'No'));
        
        return 0;
    }
}
