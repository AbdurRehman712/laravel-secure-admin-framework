<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_workspace_contents', function (Blueprint $table) {
            // Modify the content_type enum to include additional types
            $table->dropColumn('content_type');
        });

        Schema::table('project_workspace_contents', function (Blueprint $table) {
            $table->enum('content_type', [
                'user_stories',
                'acceptance_criteria',
                'wireframes',
                'design_system',
                'database_schema',
                'api_endpoints',
                'frontend_components',
                'backend_logic',
                'deployment_config',
                'docker_config',
                'project_planning',
                'project_requirements',
                'business_requirements',
                'technical_requirements',
                'user_story',
                'project_plan'
            ])->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_workspace_contents', function (Blueprint $table) {
            $table->dropColumn('content_type');
        });

        Schema::table('project_workspace_contents', function (Blueprint $table) {
            $table->enum('content_type', [
                'user_stories',
                'acceptance_criteria',
                'wireframes',
                'design_system',
                'database_schema',
                'api_endpoints',
                'frontend_components',
                'backend_logic',
                'deployment_config',
                'docker_config',
                'project_planning'
            ])->after('role');
        });
    }
};
