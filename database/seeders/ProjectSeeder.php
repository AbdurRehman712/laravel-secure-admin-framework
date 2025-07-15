<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use Modules\Core\app\Models\Admin;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user
        $admin = Admin::first();
        
        if (!$admin) {
            $this->command->error('No admin users found. Please run the admin seeder first.');
            return;
        }

        // Create sample projects
        $projects = [
            [
                'name' => 'E-commerce Platform',
                'slug' => 'ecommerce-platform',
                'description' => 'A comprehensive e-commerce platform with product catalog, shopping cart, payment processing, and order management.',
                'status' => 'planning',
                'created_by' => $admin->id,
                'settings' => [
                    'framework' => 'Laravel 11',
                    'frontend' => 'Livewire 3 + Alpine.js',
                    'styling' => 'Tailwind CSS',
                    'database' => 'MySQL',
                ],
                'ai_context' => [
                    'project_type' => 'E-commerce',
                    'target_audience' => 'Small to medium businesses',
                    'key_features' => [
                        'Product catalog',
                        'Shopping cart',
                        'User authentication',
                        'Payment processing',
                        'Order management',
                        'Admin dashboard',
                    ],
                ],
            ],
            [
                'name' => 'Task Management System',
                'slug' => 'task-management-system',
                'description' => 'A collaborative task management system with project organization, team collaboration, and progress tracking.',
                'status' => 'development',
                'created_by' => $admin->id,
                'settings' => [
                    'framework' => 'Laravel 11',
                    'frontend' => 'Livewire 3 + Alpine.js',
                    'styling' => 'Tailwind CSS',
                    'database' => 'PostgreSQL',
                ],
                'ai_context' => [
                    'project_type' => 'Project Management',
                    'target_audience' => 'Development teams',
                    'key_features' => [
                        'Task creation and assignment',
                        'Project organization',
                        'Team collaboration',
                        'Progress tracking',
                        'Time tracking',
                        'Reporting',
                    ],
                ],
            ],
            [
                'name' => 'Learning Management System',
                'slug' => 'learning-management-system',
                'description' => 'An online learning platform with course creation, student enrollment, progress tracking, and assessment tools.',
                'status' => 'planning',
                'created_by' => $admin->id,
                'settings' => [
                    'framework' => 'Laravel 11',
                    'frontend' => 'Livewire 3 + Alpine.js',
                    'styling' => 'Tailwind CSS',
                    'database' => 'MySQL',
                ],
                'ai_context' => [
                    'project_type' => 'Education',
                    'target_audience' => 'Educational institutions',
                    'key_features' => [
                        'Course creation',
                        'Student enrollment',
                        'Video lessons',
                        'Quizzes and assessments',
                        'Progress tracking',
                        'Certificates',
                    ],
                ],
            ],
        ];

        foreach ($projects as $projectData) {
            $project = Project::create($projectData);
            
            // Add the creator as a team member with project_owner role
            $project->addTeamMember($admin, 'product_owner');
            
            $this->command->info("Created project: {$project->name}");
        }

        $this->command->info('Project seeder completed successfully!');
    }
}
