<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erd_project_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('erd_projects')->onDelete('cascade');
            $table->string('version_number');
            $table->text('description')->nullable();
            $table->json('canvas_data')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->unique(['project_id', 'version_number']);
            $table->index('project_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erd_project_versions');
    }
};
