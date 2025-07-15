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
        Schema::create('project_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('module_name');
            $table->string('module_slug');
            $table->text('description')->nullable();
            $table->foreignId('generated_by')->constrained('admins')->onDelete('cascade');
            $table->json('generation_data')->nullable();
            $table->json('file_structure')->nullable();
            $table->enum('status', ['generated', 'installed', 'active', 'disabled', 'error'])
                  ->default('generated');
            $table->integer('version')->default(1);
            $table->boolean('ai_generated')->default(false);
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['module_name', 'project_id']);
            $table->unique(['project_id', 'module_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_modules');
    }
};
