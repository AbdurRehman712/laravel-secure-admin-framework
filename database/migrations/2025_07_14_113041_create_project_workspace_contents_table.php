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
        Schema::create('project_workspace_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->enum('role', [
                'product_owner',
                'designer',
                'database_admin',
                'frontend_developer',
                'backend_developer',
                'devops'
            ]);
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
                'docker_config'
            ]);
            $table->string('title');
            $table->json('content');
            $table->json('ai_prompt_used')->nullable();
            $table->json('parsed_data')->nullable();
            $table->enum('status', ['draft', 'review', 'approved', 'implemented'])
                  ->default('draft');
            $table->integer('version')->default(1);
            $table->foreignId('parent_content_id')->nullable()
                  ->constrained('project_workspace_contents')->onDelete('cascade');
            $table->timestamps();

            $table->index(['project_id', 'role']);
            $table->index(['project_id', 'content_type']);
            $table->index(['status', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_workspace_contents');
    }
};
